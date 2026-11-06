<?php

namespace Tests\Feature;

use App\Jobs\SendGroupWebinarEmail;
use App\Mail\UserNotificationMail;
use App\Models\AcademicYear;
use App\Models\Coupon;
use App\Models\GroupWebinarAdjustmentRequest;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Services\GroupWebinarEmailService;
use App\Services\GroupWebinarPricing;
use App\Services\RazorpayService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupWebinarBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        foreach ([
            'webinar_group_enabled' => '1',
            'webinar_group_allow_requests' => '1',
            'webinar_group_is_free' => '0',
            'webinar_group_price_per_person' => '100',
            'webinar_group_min_participants' => '2',
            'webinar_group_max_participants' => '5',
            'webinar_group_duration_minutes' => '60',
            'webinar_group_invitation_expiry_hours' => '72',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'webinar_group',
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'type' => 'text',
            ]);
        }
    }

    public function test_adjustment_request_migration_can_resume_when_its_table_exists(): void
    {
        Schema::drop('group_webinar_adjustment_requests');
        Schema::create('group_webinar_adjustment_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_webinar_request_id');
            $table->foreignId('participant_id')->nullable();
            $table->foreignId('payment_id')->nullable();
            $table->foreignId('requested_by')->nullable();
            $table->foreignId('processed_by')->nullable();
            $table->string('type', 30);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->string('provider_reference', 100)->nullable();
            $table->text('admin_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_11_02_130000_create_group_webinar_adjustment_requests.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('group_webinar_adjustment_requests'));
        $this->assertTrue(Schema::hasIndex(
            'group_webinar_adjustment_requests',
            ['provider_reference'],
            'unique'
        ));
        $this->assertTrue(Schema::hasIndex(
            'group_webinar_adjustment_requests',
            ['group_webinar_request_id', 'type', 'status']
        ));

        $foreignKeyColumns = collect(Schema::getForeignKeys('group_webinar_adjustment_requests'))
            ->pluck('columns')
            ->flatten()
            ->all();

        foreach (['group_webinar_request_id', 'participant_id', 'payment_id', 'requested_by', 'processed_by'] as $column) {
            $this->assertContains($column, $foreignKeyColumns);
        }
    }

    public function test_group_request_creates_one_record_per_participant_and_guest_invitation(): void
    {
        $organizer = User::factory()->create(['name' => 'Organizer', 'email' => 'organizer@example.com']);
        $response = $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['name' => 'Guest', 'email' => 'guest@example.com'],
            ['name' => '', 'email' => 'second@example.com'],
        ]));

        $response->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants')->firstOrFail();
        $this->assertSame(3, $groupRequest->participant_count);
        $this->assertSame(3, $groupRequest->participants->count());
        $this->assertSame(100.0, (float) $groupRequest->total_amount);
        $this->assertSame(3, $groupRequest->participants->where('payment_status', 'pending')->count());
        $this->assertSame(1, $organizer->notifications()->count());
        $this->assertDatabaseHas('group_webinar_participants', [
            'group_webinar_request_id' => $groupRequest->id,
            'email_normalized' => 'guest@example.com',
            'user_id' => null,
        ]);
        Queue::assertPushed(SendGroupWebinarEmail::class, 3);

        $participant = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $delivery = $participant->emailDeliveries()->firstOrFail();
        $this->assertSame('Your Group Webinar Booking Request – PhysioSource', $delivery->subject);
        $this->assertStringContainsString('booking request', strtolower($delivery->message));
        $this->assertStringContainsString('not confirmed until it has been officially approved', $delivery->message);
        $this->assertStringContainsString('Student Representative is responsible for making the combined group payment', $delivery->message);
        $this->assertStringNotContainsString('organizer@example.com', $delivery->message);
        $this->assertStringNotContainsString('second@example.com', $delivery->message);
        $organizerDelivery = $groupRequest->participants->firstWhere('role', 'organizer')->emailDeliveries()->firstOrFail();
        $this->assertSame('Your Group Webinar Booking Request – PhysioSource', $organizerDelivery->subject);
        $this->assertStringContainsString($groupRequest->reference, $organizerDelivery->message);
        $this->assertStringContainsString('one combined group payment of ₹100.00', $organizerDelivery->message);
        $this->assertStringNotContainsString('guest@example.com', $organizerDelivery->message);
        $this->assertStringNotContainsString('second@example.com', $organizerDelivery->message);
        $allRequestDeliveries = $groupRequest->participants->flatMap->emailDeliveries;
        $this->assertCount(3, $allRequestDeliveries);
        $this->assertSame(
            ['guest@example.com', 'organizer@example.com', 'second@example.com'],
            $allRequestDeliveries->pluck('recipient')->sort()->values()->all()
        );
        $this->assertSame(0, $allRequestDeliveries
            ->where('notification_type', 'group_booking_confirmation')->count());
        $this->assertNotSame($participant->invitation_token_hash, $delivery->action_url);
        $token = basename(parse_url($delivery->action_url, PHP_URL_PATH));
        app(GroupWebinarEmailService::class)->sendBookingRequestConfirmation($participant, $token, false, true);
        Queue::assertPushed(SendGroupWebinarEmail::class, 3);
        Mail::fake();
        $emailJob = new SendGroupWebinarEmail($delivery->id);
        $emailJob->handle();
        $emailJob->handle();
        Mail::assertSent(UserNotificationMail::class, fn ($mail) => $mail->hasTo('guest@example.com'));
        $this->assertNotNull($delivery->fresh()->sent_at);
        $this->app['auth']->guard()->logout();
        $this->get($delivery->action_url)
            ->assertOk()
            ->assertSee('guest@example.com')
            ->assertSee('Included in Student Representative payment')
            ->assertSee('The group total is ₹100.00')
            ->assertSee($groupRequest->reference)
            ->assertDontSee('organizer@example.com')
            ->assertDontSee('second@example.com')
            ->assertDontSee('group_webinar_participants/'.$participant->id);
    }

    public function test_retried_booking_submission_reuses_request_without_duplicate_email_jobs(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $payload = $this->requestData([
            ['email' => 'guest@example.com'],
        ]);

        $this->actingAs($organizer)->post(route('group-webinars.store'), $payload)->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $organizerDelivery = $groupRequest->participants->firstWhere('role', 'organizer')
            ->emailDeliveries->firstWhere('notification_type', 'group_payment_link');
        $invitationToken = basename(parse_url($organizerDelivery->action_url, PHP_URL_PATH));

        $this->post(route('group-webinars.store'), $payload)
            ->assertRedirect(route('group-webinars.invitation', $invitationToken));

        $this->assertDatabaseCount('group_webinar_requests', 1);
        $this->assertSame(1, $organizer->notifications()->count());
        $this->assertSame(2, $groupRequest->participants()->count());
        $this->assertSame(2, $groupRequest->participants()->withCount('emailDeliveries')->get()->sum('email_deliveries_count'));
        Queue::assertPushed(SendGroupWebinarEmail::class, 2);
    }

    public function test_booking_email_queue_failure_is_reported_without_claiming_delivery(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(GroupWebinarEmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyInApp')->once();
            $mock->shouldReceive('sendBookingRequestConfirmation')
                ->twice()
                ->andThrow(new \RuntimeException('Queue unavailable'));
        });

        $this->actingAs($organizer)
            ->post(route('group-webinars.store'), $this->requestData([
                ['email' => 'guest@example.com'],
            ]))
            ->assertRedirect()
            ->assertSessionHas('webinar_error')
            ->assertSessionMissing('success');

        $this->assertDatabaseCount('group_webinar_requests', 1);
        Queue::assertNothingPushed();
    }

    public function test_free_group_booking_sends_individual_request_emails_without_payment_amounts(): void
    {
        Setting::where('key', 'webinar_group_is_free')->update(['value' => '1']);
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->get(route('group-webinars.create'))
            ->assertOk()
            ->assertSee('Submit Free Booking')
            ->assertSee('This Group Webinar is free; no payment is required.')
            ->assertDontSee('Continue to Group Payment');

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $this->assertTrue($groupRequest->is_free);
        $this->assertSame(0.0, (float) $groupRequest->total_amount);
        $this->assertSame(2, $groupRequest->participants->count());

        foreach ($groupRequest->participants as $participant) {
            $delivery = $participant->emailDeliveries->sole();
            $this->assertSame('Your Group Webinar Booking Request – PhysioSource', $delivery->subject);
            $this->assertStringContainsString('This session is free; no payment is required.', $delivery->message);
            $this->assertStringNotContainsString('₹', $delivery->message);
            $this->assertStringNotContainsString('payable', strtolower($delivery->message));
        }

        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $invitationToken = basename(parse_url($guest->emailDeliveries->sole()->action_url, PHP_URL_PATH));
        $this->get(route('group-webinars.invitation', $invitationToken))
            ->assertOk()
            ->assertSee('No payment is required for this free webinar.')
            ->assertDontSee('group payment has been verified');

        $this->assertSame(
            GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
            $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com')->payment_status
        );
        $this->assertSame(0, $groupRequest->participants->flatMap->emailDeliveries
            ->where('notification_type', 'group_payment_link')->count());
        Queue::assertPushed(SendGroupWebinarEmail::class, 2);
    }

    public function test_group_booking_form_requires_and_links_the_terms_checkbox(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);

        $this->actingAs($organizer)
            ->get(route('group-webinars.create'))
            ->assertOk()
            ->assertSee('name="terms_accepted"', false)
            ->assertSee('Group Webinar Terms and Refund Policy', false)
            ->assertSee('href="#webinarPolicyModal"', false)
            ->assertSee('id="webinarPolicyModal"', false)
            ->assertDontSee('(optional)');
    }

    public function test_group_booking_requires_three_complete_preferred_slots(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $data = [
            'submission_key' => (string) Str::uuid(),
            'organizer_whatsapp' => '+919876543210',
            'terms_accepted' => '1',
        ];
        $this->actingAs($organizer)->post(route('group-webinars.store'), $data)
            ->assertSessionHasErrors('preferred_slots');

        $data['preferred_slots'] = [
            ['date' => now()->addDay()->format('Y-m-d'), 'time' => '18:00'],
            ['date' => now()->addDays(2)->format('Y-m-d'), 'time' => '19:00'],
            ['date' => now()->addDays(3)->format('Y-m-d'), 'time' => '20:00'],
        ];
        $this->actingAs($organizer)->post(route('group-webinars.store'), $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_duplicate_emails_and_capacity_are_validated_including_the_organizer(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);

        $duplicateResponse = $this->actingAs($organizer)->from(route('group-webinars.create'))
            ->post(route('group-webinars.store'), $this->requestData([
                ['email' => 'Organizer@Example.com'],
            ]));
        $duplicateResponse->assertSessionHasErrors('participants');
        $this->assertDatabaseCount('group_webinar_requests', 0);

        $duplicateParticipantsResponse = $this->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
            ['email' => 'GUEST@example.com'],
        ]));
        $duplicateParticipantsResponse->assertSessionHasErrors('participants.1.email');
        $this->assertDatabaseCount('group_webinar_requests', 0);

        $this->setSetting('webinar_group_max_participants', '2');
        $capacityResponse = $this->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'first@example.com'],
            ['email' => 'second@example.com'],
        ]));
        $capacityResponse->assertSessionHasErrors('participants');
        $this->assertDatabaseCount('group_webinar_requests', 0);
    }

    public function test_policy_pricing_covers_individual_and_large_group_bookings(): void
    {
        $this->assertSame(100, GroupWebinarPricing::totalFor(1));
        $this->assertSame(100, GroupWebinarPricing::totalFor(10));
        $this->assertSame(110, GroupWebinarPricing::totalFor(11));
        $this->assertSame(150, GroupWebinarPricing::totalFor(15));
        $this->assertSame(100.0, array_sum(GroupWebinarPricing::sharesFor(3)));
        $this->assertSame(150.0, array_sum(GroupWebinarPricing::sharesFor(15)));
    }

    public function test_organizer_pays_once_for_the_full_group_and_participants_are_confirmed_after_payment(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->withArgs(fn ($payment) => (float) $payment->amount === 100.0)
                ->andReturn(['id' => 'order_group_organizer', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifyGroupWebinarParticipantSignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_group_organizer',
                'order_id' => 'order_group_organizer',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
            ['email' => 'second@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $organizerParticipant = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($organizerParticipant->emailDeliveries->firstOrFail()->action_url, PHP_URL_PATH));

        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])
            ->assertRedirect();

        $payment = $organizerParticipant->payments()->firstOrFail();
        $this->assertSame(100.0, (float) $payment->amount);
        $this->assertSame(0.0, (float) $guest->amount);
        $this->assertSame('pending', $guest->fresh()->payment_status);

        $this->post(route('group-webinars.payment.verify', [
            'token' => $token,
            'payment' => $payment->public_reference,
        ]), [
            'razorpay_payment_id' => 'pay_group_organizer',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $organizerParticipant->fresh()->payment_status);
        $this->assertSame('paid', $guest->fresh()->payment_status);
        $confirmations = $groupRequest->participants()
            ->with(['emailDeliveries' => fn ($query) => $query->where('notification_type', 'group_booking_confirmation')])
            ->get()
            ->map(fn (GroupWebinarParticipant $participant) => $participant->emailDeliveries->sole());
        $this->assertCount(3, $confirmations);
        $this->assertSame(
            ['guest@example.com', 'organizer@example.com', 'second@example.com'],
            $confirmations->pluck('recipient')->sort()->values()->all()
        );
        foreach ($confirmations as $delivery) {
            $this->assertStringContainsString('combined payment of ₹100.00 has been verified', $delivery->message);
            $this->assertStringNotContainsString('guest@example.com', $delivery->message);
            $this->assertStringNotContainsString('second@example.com', $delivery->message);
        }
        Queue::assertPushed(SendGroupWebinarEmail::class, 6);
    }

    public function test_an_individual_can_book_and_acceptance_is_saved_on_the_booking(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([]))
            ->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants')->firstOrFail();
        $this->assertSame(1, $groupRequest->participant_count);
        $this->assertSame(100.0, (float) $groupRequest->total_amount);
        $this->assertSame('+919876543210', $groupRequest->organizer_whatsapp);
        $this->assertSame(config('webinar.group_policy_version'), $groupRequest->policy_version);
        $this->assertNotNull($groupRequest->terms_accepted_at);
        $this->assertNotNull($groupRequest->participants->sole()->terms_accepted_at);
    }

    public function test_only_organizer_phone_and_policy_are_required_for_a_booking(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $data = [
            'submission_key' => (string) Str::uuid(),
            'organizer_whatsapp' => '+919876543210',
            'terms_accepted' => '1',
            'preferred_slots' => $this->futureSlots(),
        ];

        $this->actingAs($organizer)->post(route('group-webinars.store'), $data)
            ->assertRedirect();

        $groupRequest = GroupWebinarRequest::firstOrFail();
        $this->assertSame('', $groupRequest->topic);
        $this->assertStringContainsString('Preferred date and time slots:', $groupRequest->description);
        $this->assertCount(3, $groupRequest->preferred_slots);
        $this->assertSame(1, $groupRequest->participant_count);
    }

    public function test_long_group_description_is_accepted_without_truncation(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $description = str_repeat('Long clinical discussion. ', 400);

        $this->actingAs($organizer)->post(route('group-webinars.store'), [
            'submission_key' => (string) Str::uuid(),
            'organizer_whatsapp' => '+919876543210',
            'description' => $description,
            'terms_accepted' => '1',
            'preferred_slots' => $this->futureSlots(),
        ])->assertRedirect();

        $storedDescription = GroupWebinarRequest::firstOrFail()->description;
        $this->assertNotNull($storedDescription);
        $this->assertSame(rtrim($description), substr($storedDescription, -strlen(rtrim($description))));
    }

    public function test_invalid_organizer_email_is_rejected(): void
    {
        $organizer = User::factory()->create(['email' => 'not-an-email']);

        $this->actingAs($organizer)->post(route('group-webinars.store'), [
            'submission_key' => (string) Str::uuid(),
            'organizer_whatsapp' => '+919876543210',
            'terms_accepted' => '1',
            'preferred_slots' => $this->futureSlots(),
        ])->assertSessionHasErrors('organizer_email');

        $this->assertDatabaseCount('group_webinar_requests', 0);
    }

    public function test_booking_cannot_be_submitted_without_server_side_policy_acceptance(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $data = $this->requestData([]);
        unset($data['terms_accepted']);

        $this->actingAs($organizer)->post(route('group-webinars.store'), $data)
            ->assertSessionHasErrors('terms_accepted');

        $this->assertDatabaseCount('group_webinar_requests', 0);
    }

    public function test_organizer_policy_acceptance_is_saved_and_used_for_combined_payment(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->andReturn(['id' => 'order_guest_consent', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $representative = GroupWebinarParticipant::where('role', 'organizer')->firstOrFail();
        $guest = GroupWebinarParticipant::where('email_normalized', 'guest@example.com')->firstOrFail();
        $this->assertSame(100.0, (float) $representative->amount);
        $this->assertSame(0.0, (float) $guest->amount);
        $token = basename(parse_url($representative->emailDeliveries()->firstOrFail()->action_url, PHP_URL_PATH));
        $this->assertNotNull($representative->terms_accepted_at);

        $paymentResponse = $this->post(route('group-webinars.payment.start', $token));
        $paymentResponse->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse(session()->has('webinar_error'));
        $this->assertSame(1, $representative->payments()->count());
        $this->assertSame(100.0, (float) $representative->payments()->firstOrFail()->amount);
        $this->assertNotNull($representative->fresh()->terms_accepted_at);
        $this->assertSame(config('webinar.group_policy_version'), $representative->fresh()->policy_version);
    }

    public function test_my_webinars_is_private_and_group_meeting_access_requires_payment_and_confirmation(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $groupRequest = GroupWebinarRequest::with('participants')->firstOrFail();
        $organizerParticipant = $groupRequest->participants->sole();
        $this->get(route('webinars.my'))
            ->assertOk()
            ->assertSee('Knee rehabilitation')
            ->assertSee('₹100.00');

        $this->actingAs($otherUser)->get(route('webinars.my'))
            ->assertOk()
            ->assertDontSee('Knee rehabilitation');
        $this->get(route('group-webinars.join', $groupRequest))->assertNotFound();

        $groupRequest->update([
            'status' => GroupWebinarRequest::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDay(),
            'meeting_link' => 'https://meet.example.com/private-room',
        ]);
        $this->actingAs($organizer)->get(route('group-webinars.join', $groupRequest))->assertForbidden();

        $organizerParticipant->update(['payment_status' => GroupWebinarParticipant::PAYMENT_PAID]);
        $this->get(route('group-webinars.join', $groupRequest))
            ->assertRedirect('https://meet.example.com/private-room');
    }

    public function test_organizer_can_request_a_policy_limited_dropout_refund_before_deadline(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $groupRequest = GroupWebinarRequest::create([
            'organizer_id' => $organizer->id,
            'reference' => 'GW-REFUND-TEST',
            'organizer_name' => $organizer->name,
            'organizer_email' => $organizer->email,
            'topic' => 'Refund eligible group',
            'description' => 'A group webinar eligible for an individual dropout refund.',
            'participant_count' => 11,
            'minimum_participants' => 1,
            'maximum_participants' => 50,
            'duration_minutes' => 60,
            'price_per_person' => 10,
            'total_amount' => 110,
            'currency' => 'INR',
            'is_free' => false,
            'status' => GroupWebinarRequest::STATUS_CONFIRMED,
            'scheduled_at' => now('Asia/Kolkata')->addDays(2),
            'policy_version' => '1.0',
            'terms_accepted_at' => now(),
        ]);
        $participant = $groupRequest->participants()->create([
            'user_id' => $organizer->id,
            'name' => $organizer->name,
            'email' => $organizer->email,
            'email_normalized' => mb_strtolower($organizer->email),
            'role' => 'organizer',
            'status' => GroupWebinarParticipant::STATUS_PAID,
            'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
            'amount' => 10,
            'amount_due' => 10,
            'policy_version' => '1.0',
            'terms_accepted_at' => now(),
        ]);
        $payment = $participant->payments()->create([
            'public_reference' => (string) Str::uuid(),
            'original_amount' => 10,
            'discount_amount' => 0,
            'amount' => 10,
            'currency' => 'INR',
            'status' => GroupWebinarParticipantPayment::STATUS_PAID,
            'razorpay_order_id' => 'order_refund_test',
            'razorpay_payment_id' => 'pay_refund_test',
            'paid_at' => now(),
        ]);

        $this->actingAs($organizer)->post(route('group-webinars.adjustments.store', $groupRequest), [
            'type' => 'dropout_refund',
            'participant_id' => $participant->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('group_webinar_adjustment_requests', [
            'group_webinar_request_id' => $groupRequest->id,
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'amount' => 10,
            'status' => 'pending',
        ]);
    }

    public function test_admin_refund_approval_uses_razorpay_once_and_records_the_provider_reference(): void
    {
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('refundGroupWebinarParticipantPayment')
                ->once()
                ->withArgs(fn ($payment, $amount, $receipt) => $payment instanceof GroupWebinarParticipantPayment
                    && (float) $amount === 10.0
                    && $receipt === 'GW-REFUND-1')
                ->andReturn(['id' => 'rfnd_test_1', 'status' => 'processed']);
        });

        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $groupRequest = $this->createAdminRequest('GW-REFUND-ADMIN', 'confirmed', [
            ['email' => 'organizer@example.com', 'payment_status' => 'paid', 'amount_due' => 10, 'payment_amount' => 10],
        ], now()->addDay());
        $groupRequest->update(['organizer_id' => $organizer->id]);
        $participant = $groupRequest->participants()->firstOrFail();
        $participant->update(['user_id' => $organizer->id]);
        $groupRequest->update(['participant_count' => 11]);
        $payment = $participant->payments()->firstOrFail();
        $adjustment = GroupWebinarAdjustmentRequest::create([
            'group_webinar_request_id' => $groupRequest->id,
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'requested_by' => $organizer->id,
            'type' => GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND,
            'amount' => 10,
            'status' => GroupWebinarAdjustmentRequest::STATUS_PENDING,
        ]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.group-webinars.adjustments.update', $adjustment), ['decision' => 'approve'])
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));

        $this->assertDatabaseHas('group_webinar_adjustment_requests', [
            'id' => $adjustment->id,
            'status' => GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
            'provider_reference' => 'rfnd_test_1',
        ]);
        $this->patch(route('admin.group-webinars.adjustments.update', $adjustment), ['decision' => 'approve'])
            ->assertStatus(409);
    }

    public function test_admin_can_convert_an_unscheduled_paid_booking_to_a_future_session_credit(): void
    {
        $organizer = User::factory()->create(['email' => 'credit-organizer@example.com']);
        $groupRequest = $this->createAdminRequest('GW-CREDIT-ADMIN', 'pending', [
            ['email' => $organizer->email, 'payment_status' => 'paid', 'amount_due' => 50, 'payment_amount' => 50],
        ]);
        $groupRequest->update([
            'organizer_id' => $organizer->id,
            'admin_response' => 'The proposed schedule did not work; please suggest alternatives.',
        ]);
        $participant = $groupRequest->participants()->firstOrFail();
        $participant->update(['user_id' => $organizer->id]);
        $adjustment = GroupWebinarAdjustmentRequest::create([
            'group_webinar_request_id' => $groupRequest->id,
            'participant_id' => $participant->id,
            'requested_by' => $organizer->id,
            'type' => GroupWebinarAdjustmentRequest::TYPE_SESSION_CREDIT,
            'amount' => 50,
            'status' => GroupWebinarAdjustmentRequest::STATUS_PENDING,
        ]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.group-webinars.adjustments.update', $adjustment), ['decision' => 'approve'])
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));

        $this->assertSame(GroupWebinarRequest::STATUS_PENDING, $groupRequest->fresh()->status);
        $this->assertNull($groupRequest->fresh()->scheduled_at);
        $this->assertSame(GroupWebinarParticipant::PAYMENT_PAID, $participant->fresh()->payment_status);
        $this->assertDatabaseHas('group_webinar_adjustment_requests', [
            'id' => $adjustment->id,
            'status' => GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
            'amount' => 50,
            'provider_reference' => 'SESSION-CREDIT-'.$adjustment->id,
        ]);
    }

    public function test_coupon_is_applied_to_the_combined_group_total_and_one_razorpay_order(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'GUEST20',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->withArgs(fn ($payment) => (float) $payment->amount === 80.0)
                ->andReturn(['id' => 'order_guest_1', 'amount' => 8000, 'currency' => 'INR', 'status' => 'created']);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $response = $this->post(route('group-webinars.payment.start', $token), ['coupon_code' => $coupon->code, 'terms_accepted' => '1']);

        $payment = $representative->payments()->firstOrFail();
        $this->assertSame(80.0, (float) $payment->amount);
        $this->assertSame(20.0, (float) $payment->discount_amount);
        $this->assertSame('order_guest_1', $payment->razorpay_order_id);
        $this->assertSame(1, $representative->payments()->count());
        $this->assertSame('pending', $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com')->payment_status);
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'redeemable_type' => $payment::class,
            'redeemable_id' => $payment->id,
            'email' => 'organizer@example.com',
            'status' => 'reserved',
        ]);
        $response->assertRedirect(route('group-webinars.payment', ['token' => $token, 'payment' => $payment->public_reference]));
    }

    public function test_fixed_coupon_reduces_the_group_webinar_order_and_payment_page_amount(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'LESS20',
            'discount_type' => Coupon::TYPE_FIXED,
            'discount_value' => 20,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'is_active' => true,
        ]);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->withArgs(fn ($payment) => (float) $payment->amount === 80.0)
                ->andReturn(['id' => 'order_guest_fixed', 'amount' => 8000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('keyId')->once()->andReturn('rzp_test_public');
            $mock->shouldReceive('amountToSubunits')->once()->with(80)->andReturn(8000);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['coupon_code' => $coupon->code, 'terms_accepted' => '1'])
            ->assertRedirect();

        $payment = $representative->payments()->firstOrFail();
        $this->assertSame(100.0, (float) $payment->original_amount);
        $this->assertSame(20.0, (float) $payment->discount_amount);
        $this->assertSame(80.0, (float) $payment->amount);
        $this->assertSame('order_guest_fixed', $payment->razorpay_order_id);
        $representative->update(['invitation_expires_at' => now()->subMinute()]);
        $this->get(route('group-webinars.payment', ['token' => $token, 'payment' => $payment->public_reference]))
            ->assertOk()
            ->assertSee('LESS20')
            ->assertSee('₹20.00 fixed discount')
            ->assertSee('₹80.00 INR')
            ->assertSee('amount: 8000', false);
    }

    public function test_group_coupon_preview_returns_combined_amounts_without_reserving_coupon(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'PREVIEW20',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'is_active' => true,
        ]);

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));

        $this->postJson(route('group-webinars.coupon.quote', $token), ['code' => $coupon->code])
            ->assertOk()
            ->assertJson([
                'valid' => true,
                'code' => $coupon->code,
                'original_amount' => 100,
                'discount_description' => '20% discount',
                'discount_amount' => 20,
                'final_amount' => 80,
            ]);

        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    public function test_verified_organizer_payment_confirms_all_group_participants(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->andReturn(['id' => 'order_guest_2', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifyGroupWebinarParticipantSignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_guest_2',
                'order_id' => 'order_guest_2',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $payment = $representative->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.verify', ['token' => $token, 'payment' => $payment->public_reference]), [
            'razorpay_payment_id' => 'pay_guest_2',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $representative->fresh()->payment_status);
        $this->assertSame('paid', $guest->fresh()->payment_status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('pay_guest_2', $payment->fresh()->razorpay_payment_id);
    }

    public function test_authorized_group_payment_is_captured_before_marking_participant_paid(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->andReturn(['id' => 'order_guest_authorized', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifyGroupWebinarParticipantSignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_guest_authorized',
                'order_id' => 'order_guest_authorized',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'authorized',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->twice();
            $mock->shouldReceive('capturePayment')->once()->andReturn([
                'id' => 'pay_guest_authorized',
                'order_id' => 'order_guest_authorized',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $payment = $representative->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.verify', [
            'token' => $token,
            'payment' => $payment->public_reference,
        ]), [
            'razorpay_payment_id' => 'pay_guest_authorized',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $representative->fresh()->payment_status);
        $this->assertSame('paid', $guest->fresh()->payment_status);
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_provider_verified_failed_payment_remains_unpaid_and_can_be_retried(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->andReturn(
                    ['id' => 'order_guest_failed', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created'],
                    ['id' => 'order_guest_retry', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']
                );
            $mock->shouldReceive('fetchPayment')->once()->with('pay_guest_failed')->andReturn([
                'id' => 'pay_guest_failed',
                'order_id' => 'order_guest_failed',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'failed',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $payment = $representative->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.failure', [
            'token' => $token,
            'payment' => $payment->public_reference,
        ]), ['razorpay_payment_id' => 'pay_guest_failed'])
            ->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('pay_guest_failed', $payment->fresh()->razorpay_payment_id);
        $this->assertSame('failed', $representative->fresh()->payment_status);
        $this->assertSame('pending', $guest->fresh()->payment_status);
        $this->assertSame(0, $groupRequest->participants()
            ->whereHas('emailDeliveries', fn ($query) => $query->where('notification_type', 'group_booking_confirmation'))
            ->count());
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $this->assertSame(2, $representative->payments()->count());
    }

    public function test_payment_captured_after_cancellation_does_not_confirm_or_restore_the_booking(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->andReturn(['id' => 'order_cancelled', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifyGroupWebinarParticipantSignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_cancelled',
                'order_id' => 'order_cancelled',
                'amount' => 10000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $payment = $representative->payments()->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin, 'admin')->patch(route('admin.group-webinars.update', $groupRequest), [
            'status' => 'cancelled',
            'admin_response' => 'Request cancelled before payment verification.',
        ])->assertRedirect();

        $this->post(route('group-webinars.payment.verify', ['token' => $token, 'payment' => $payment->public_reference]), [
            'razorpay_payment_id' => 'pay_cancelled',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('webinars.my'));

        $this->assertSame('cancelled', $groupRequest->fresh()->status);
        $this->assertSame(GroupWebinarParticipant::STATUS_CANCELLED, $representative->fresh()->status);
        $this->assertSame(GroupWebinarParticipant::STATUS_CANCELLED, $guest->fresh()->status);
        $this->assertSame(GroupWebinarParticipantPayment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(0, $groupRequest->participants()
            ->whereHas('emailDeliveries', fn ($query) => $query->where('notification_type', 'group_booking_confirmation'))
            ->count());
        $this->assertSame(1, $representative->emailDeliveries()
            ->where('notification_type', 'payment_after_cancellation')
            ->count());
    }

    public function test_dismissed_group_checkout_stays_unpaid_and_reuses_the_same_order_on_retry(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->andReturn(['id' => 'order_guest_cancelled', 'amount' => 10000, 'currency' => 'INR', 'status' => 'created']);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $payment = $representative->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.abandon', [
            'token' => $token,
            'payment' => $payment->public_reference,
        ]))->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame(GroupWebinarParticipantPayment::STATUS_ABANDONED, $payment->fresh()->status);
        $this->assertSame(GroupWebinarParticipant::PAYMENT_PENDING, $representative->fresh()->payment_status);
        $this->post(route('group-webinars.payment.start', $token), ['terms_accepted' => '1'])->assertRedirect();
        $this->assertSame(1, $representative->payments()->count());
        $this->assertSame('order_guest_cancelled', $payment->fresh()->razorpay_order_id);
        $this->assertSame(GroupWebinarParticipantPayment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_full_coupon_confirms_the_group_without_creating_a_razorpay_order(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'FULLGUEST',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 100,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('isConfigured');
            $mock->shouldNotReceive('createGroupWebinarParticipantOrder');
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));

        $this->post(route('group-webinars.payment.start', $token), ['coupon_code' => $coupon->code, 'terms_accepted' => '1'])
            ->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $representative->fresh()->payment_status);
        $this->assertSame(0, $representative->payments()->whereNotNull('razorpay_order_id')->count());
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'email' => 'organizer@example.com',
            'status' => 'redeemed',
        ]);
        $this->assertSame('paid', $guest->fresh()->payment_status);
        $confirmation = $guest->emailDeliveries()
            ->where('notification_type', 'group_booking_confirmation')
            ->firstOrFail();
        $this->assertStringContainsString('coupon covered the full group total', $confirmation->message);
        $this->assertStringNotContainsString('payment has been verified', $confirmation->message);
    }

    public function test_expired_invitation_can_be_resend_without_revealing_whether_details_match(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $representative = $groupRequest->participants->firstWhere('role', 'organizer');
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $oldToken = basename(parse_url($representative->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $representative->update(['invitation_expires_at' => now()->subMinute()]);

        $this->app['auth']->guard()->logout();
        $this->flushSession();
        $this->get(route('group-webinars.invitation', $oldToken))
            ->assertOk()
            ->assertSee('Invitation expired')
            ->assertDontSee('guest@example.com')
            ->assertDontSee($groupRequest->reference);

        $this->post(route('group-webinars.invitation.resend'), [
            'reference' => $groupRequest->reference,
            'email' => 'organizer@example.com',
        ])->assertSessionHas('success', 'If the reference and email match an active participant, a new invitation has been sent.');

        $this->get(route('group-webinars.invitation', $oldToken))->assertNotFound();
        $newDelivery = $representative->emailDeliveries()->latest()->firstOrFail();
        $newToken = basename(parse_url($newDelivery->action_url, PHP_URL_PATH));
        $this->assertNotSame($oldToken, $newToken);
        $this->get(route('group-webinars.invitation', $newToken))->assertOk()->assertSee('organizer@example.com');
    }

    public function test_admin_schedule_updates_notify_participants_without_duplicate_emails(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::firstOrFail();
        $groupRequest->participants()->update([
            'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
            'status' => GroupWebinarParticipant::STATUS_PAID,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $scheduledAt = now()->addDays(3)->format('Y-m-d\TH:i');
        $payload = [
            'status' => 'confirmed',
            'scheduled_at' => $scheduledAt,
            'meeting_link' => 'https://meet.example.com/group',
            'admin_response' => 'Please join five minutes early.',
        ];

        $this->actingAs($admin, 'admin')->patch(route('admin.group-webinars.update', $groupRequest), $payload)
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));

        $this->assertSame('confirmed', $groupRequest->fresh()->status);
        $this->assertSame(2, $groupRequest->participants()->whereHas('emailDeliveries', fn ($query) => $query->where('notification_type', 'schedule_update'))->count());
        $scheduleDeliveries = $groupRequest->participants()
            ->with(['emailDeliveries' => fn ($query) => $query->where('notification_type', 'schedule_update')])
            ->get()
            ->map(fn (GroupWebinarParticipant $participant) => $participant->emailDeliveries->sole());
        $this->assertCount(2, $scheduleDeliveries);
        $this->assertCount(2, $scheduleDeliveries->pluck('recipient')->unique());
        $this->assertCount(2, $scheduleDeliveries->pluck('action_url')->unique());
        foreach ($scheduleDeliveries as $delivery) {
            $this->assertStringContainsString($groupRequest->fresh()->scheduled_at->format('d M Y, h:i A'), $delivery->message);
            $this->assertStringContainsString('https://meet.example.com/group', $delivery->message);
            $this->assertStringContainsString('/group-webinars/invitations/', $delivery->action_url);
        }
        Queue::assertPushed(SendGroupWebinarEmail::class, 4);

        $this->patch(route('admin.group-webinars.update', $groupRequest), $payload)
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));
        Queue::assertPushed(SendGroupWebinarEmail::class, 4);

        $payload['status'] = 'pending';
        $this->patch(route('admin.group-webinars.update', $groupRequest), $payload)
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));
        $pendingScheduleDeliveries = $groupRequest->participants()
            ->with(['emailDeliveries' => fn ($query) => $query->where('notification_type', 'schedule_update')->latest('id')])
            ->get()
            ->map(fn (GroupWebinarParticipant $participant) => $participant->emailDeliveries->firstOrFail());
        foreach ($pendingScheduleDeliveries as $delivery) {
            $this->assertStringNotContainsString('https://meet.example.com/group', $delivery->message);
        }
        Queue::assertPushed(SendGroupWebinarEmail::class, 6);
    }

    public function test_failed_schedule_email_queue_preserves_existing_invitation_links(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect();
        $groupRequest = GroupWebinarRequest::firstOrFail();
        $participant = $groupRequest->participants()->where('role', 'participant')->firstOrFail();
        $originalHash = $participant->invitation_token_hash;
        $originalExpiry = $participant->invitation_expires_at;

        $this->mock(GroupWebinarEmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendRequestUpdate')
                ->twice()
                ->andThrow(new \RuntimeException('Queue unavailable'));
        });

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin, 'admin')->patch(route('admin.group-webinars.update', $groupRequest), [
            'status' => 'confirmed',
            'scheduled_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'meeting_link' => 'https://meet.example.com/group',
        ])->assertRedirect(route('admin.group-webinars.show', $groupRequest));

        $this->assertSame($originalHash, $participant->fresh()->invitation_token_hash);
        $this->assertEquals($originalExpiry, $participant->fresh()->invitation_expires_at);
    }

    public function test_admin_dashboard_summaries_revenue_filters_and_details_use_actual_payments(): void
    {
        $partial = $this->createAdminRequest('GW-PARTIAL', 'pending', [
            ['email' => 'paid@example.com', 'payment_status' => 'paid', 'amount_due' => 500, 'payment_amount' => 500],
            ['email' => 'unpaid@example.com', 'payment_status' => 'pending', 'amount_due' => 250],
        ]);
        $unpaid = $this->createAdminRequest('GW-PENDING', 'pending', [
            ['email' => 'pending@example.com', 'payment_status' => 'pending', 'amount_due' => 300],
        ]);
        $full = $this->createAdminRequest('GW-FULL', 'confirmed', [
            ['email' => 'full@example.com', 'payment_status' => 'paid', 'amount_due' => 250, 'payment_amount' => 250],
        ], now()->addDay());
        $cancelled = $this->createAdminRequest('GW-CANCELLED', 'cancelled', [
            ['email' => 'cancelled@example.com', 'payment_status' => 'pending', 'amount_due' => 700, 'status' => 'cancelled'],
        ]);

        $role = Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin, 'admin')->get(route('admin.group-webinars.index'))
            ->assertOk()
            ->assertSee('Group Webinar Requests')
            ->assertSee('Total Webinar Requests')
            ->assertSee('Total Participants')
            ->assertSee('Pending Payments')
            ->assertSee('Fully Paid Webinars')
            ->assertSee('Needs Scheduling')
            ->assertSee('₹750.00')
            ->assertSee('₹250.00')
            ->assertSee('GW-PARTIAL')
            ->assertSee('GW-FULL')
            ->assertSee('GW-CANCELLED');

        $this->get(route('admin.group-webinars.index', ['payment_status' => 'partial']))
            ->assertOk()
            ->assertSee('GW-PARTIAL')
            ->assertDontSee('GW-FULL')
            ->assertDontSee('GW-CANCELLED');

        $this->get(route('admin.group-webinars.index', [
            'status' => 'confirmed',
            'scheduling_status' => 'scheduled',
        ]))->assertOk()->assertSee('GW-FULL')->assertDontSee('GW-PARTIAL');

        $this->get(route('admin.group-webinars.index', [
            'payment_status' => 'pending',
            'scheduling_status' => 'not_scheduled',
        ]))->assertOk()->assertSee('GW-PENDING')->assertDontSee('GW-CANCELLED')->assertDontSee('GW-PARTIAL');

        $this->get(route('admin.group-webinars.show', $partial))
            ->assertOk()
            ->assertSee('Organizer Details')
            ->assertSee('Complete Participant List')
            ->assertSee('Guest')
            ->assertSee('Paid ₹500.00')
            ->assertSee('Razorpay Reference')
            ->assertSee('Payment Summary');

        $this->assertSame(1, $unpaid->participants()->count());
    }

    private function requestData(array $participants): array
    {
        $academicYear = AcademicYear::query()->firstOrCreate(
            ['slug' => 'year-one'],
            ['name' => 'Year One', 'status' => true, 'order' => 1]
        );
        $subject = Subject::query()->firstOrCreate(
            ['slug' => 'anatomy'],
            ['name' => 'Anatomy', 'status' => true, 'order' => 1]
        );

        return [
            'submission_key' => (string) Str::uuid(),
            'topic' => 'Knee rehabilitation',
            'description' => 'Discuss shared rehabilitation questions and clinical approach.',
            'preferred_schedule' => 'Weekday evenings, 6-8 PM IST',
            'preferred_slots' => collect([1, 2, 3])->map(fn (int $days): array => [
                'date' => now()->addDays($days)->format('Y-m-d'),
                'time' => '18:00',
            ])->all(),
            'academic_year_id' => $academicYear->id,
            'subject_id' => $subject->id,
            'organizer_whatsapp' => '+919876543210',
            'participants' => $participants,
            'terms_accepted' => '1',
        ];
    }

    private function setSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], [
            'value' => $value,
            'group' => 'webinar_group',
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'type' => 'text',
        ]);
    }

    private function futureSlots(): array
    {
        return collect([1, 2, 3])->map(fn (int $days): array => [
            'date' => now()->addDays($days)->format('Y-m-d'),
            'time' => '18:00',
        ])->all();
    }

    private function createAdminRequest(string $reference, string $status, array $participants, ?Carbon $scheduledAt = null): GroupWebinarRequest
    {
        $request = GroupWebinarRequest::create([
            'reference' => $reference,
            'organizer_name' => 'Organizer '.$reference,
            'organizer_email' => strtolower($reference).'@example.com',
            'topic' => 'Topic for '.$reference,
            'description' => 'Description for a group webinar request.',
            'participant_count' => count($participants),
            'minimum_participants' => 1,
            'maximum_participants' => 10,
            'duration_minutes' => 60,
            'price_per_person' => 500,
            'currency' => 'INR',
            'is_free' => false,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
        ]);

        foreach ($participants as $index => $attributes) {
            $participant = $request->participants()->create([
                'name' => $attributes['name'] ?? 'Participant '.($index + 1),
                'email' => $attributes['email'],
                'email_normalized' => mb_strtolower($attributes['email']),
                'role' => $index === 0 ? 'organizer' : 'participant',
                'status' => $attributes['status'] ?? ($attributes['payment_status'] === 'paid' ? 'paid' : 'payment_pending'),
                'payment_status' => $attributes['payment_status'],
                'amount' => 500,
                'amount_due' => $attributes['amount_due'],
                'invitation_token_hash' => hash('sha256', $reference.$index),
                'invitation_expires_at' => now()->addDay(),
            ]);

            if (isset($attributes['payment_amount'])) {
                $participant->payments()->create([
                    'public_reference' => (string) Str::uuid(),
                    'original_amount' => $attributes['payment_amount'],
                    'discount_amount' => 0,
                    'amount' => $attributes['payment_amount'],
                    'currency' => 'INR',
                    'status' => GroupWebinarParticipantPayment::STATUS_PAID,
                    'razorpay_order_id' => 'order_'.strtolower($reference).'_'.$index,
                    'razorpay_payment_id' => 'pay_'.strtolower($reference).'_'.$index,
                    'paid_at' => now(),
                ]);
            }
        }

        return $request;
    }
}
