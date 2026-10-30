<?php

namespace Tests\Feature;

use App\Jobs\SendGroupWebinarEmail;
use App\Mail\UserNotificationMail;
use App\Models\Coupon;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Models\Setting;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Mockery\MockInterface;
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
            'webinar_group_price_per_person' => '500',
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

    public function test_group_request_creates_one_record_per_participant_and_guest_invitation(): void
    {
        $organizer = User::factory()->create(['name' => 'Organizer', 'email' => 'organizer@example.com']);
        $response = $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['name' => 'Guest', 'email' => 'guest@example.com'],
            ['name' => '', 'email' => 'second@example.com'],
        ]));

        $response->assertRedirect(route('webinars.index'));
        $groupRequest = GroupWebinarRequest::with('participants')->firstOrFail();
        $this->assertSame(3, $groupRequest->participant_count);
        $this->assertSame(3, $groupRequest->participants->count());
        $this->assertSame(3, $groupRequest->participants->where('payment_status', 'pending')->count());
        $this->assertDatabaseHas('group_webinar_participants', [
            'group_webinar_request_id' => $groupRequest->id,
            'email_normalized' => 'guest@example.com',
            'user_id' => null,
        ]);
        Queue::assertPushed(SendGroupWebinarEmail::class, 3);

        $participant = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $delivery = $participant->emailDeliveries()->firstOrFail();
        $this->assertNotSame($participant->invitation_token_hash, $delivery->action_url);
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
            ->assertSee('₹500.00')
            ->assertSee($groupRequest->reference)
            ->assertDontSee('group_webinar_participants/'.$participant->id);
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

    public function test_each_participant_uses_an_independent_coupon_quote_and_razorpay_order(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'GUEST10',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->once()
                ->withArgs(fn ($payment) => (float) $payment->amount === 450.0)
                ->andReturn(['id' => 'order_guest_1', 'amount' => 45000, 'currency' => 'INR', 'status' => 'created']);
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $response = $this->post(route('group-webinars.payment.start', $token), ['coupon_code' => $coupon->code]);

        $payment = $guest->payments()->firstOrFail();
        $this->assertSame(450.0, (float) $payment->amount);
        $this->assertSame('order_guest_1', $payment->razorpay_order_id);
        $this->assertSame(1, $guest->payments()->count());
        $this->assertSame(0, $groupRequest->participants->firstWhere('role', 'organizer')->payments()->count());
        $this->assertSame('pending', $groupRequest->participants->firstWhere('role', 'organizer')->payment_status);
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'redeemable_type' => $payment::class,
            'redeemable_id' => $payment->id,
            'email' => 'guest@example.com',
            'status' => 'reserved',
        ]);
        $response->assertRedirect(route('group-webinars.payment', ['token' => $token, 'payment' => $payment->public_reference]));
    }

    public function test_guest_coupon_preview_returns_authoritative_amounts_without_reserving_coupon(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $coupon = Coupon::create([
            'code' => 'PREVIEW10',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'is_active' => true,
        ]);

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));

        $this->postJson(route('group-webinars.coupon.quote', $token), ['code' => $coupon->code])
            ->assertOk()
            ->assertJson([
                'valid' => true,
                'code' => $coupon->code,
                'original_amount' => 500,
                'discount_amount' => 50,
                'final_amount' => 450,
            ]);

        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    public function test_verified_payment_updates_only_the_invited_participant(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->andReturn(['id' => 'order_guest_2', 'amount' => 50000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifyGroupWebinarParticipantSignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_guest_2',
                'order_id' => 'order_guest_2',
                'amount' => 50000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));

        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token))->assertRedirect();
        $payment = $guest->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.verify', ['token' => $token, 'payment' => $payment->public_reference]), [
            'razorpay_payment_id' => 'pay_guest_2',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $guest->fresh()->payment_status);
        $this->assertSame('paid', $payment->fresh()->status);
        $organizerParticipant = $groupRequest->participants->firstWhere('role', 'organizer');
        $this->assertSame('pending', $organizerParticipant->fresh()->payment_status);
        $this->assertNull($organizerParticipant->payments()->first());
        $this->assertSame('pay_guest_2', $payment->fresh()->razorpay_payment_id);
    }

    public function test_provider_verified_failed_payment_remains_unpaid_and_can_be_retried(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createGroupWebinarParticipantOrder')
                ->andReturn(
                    ['id' => 'order_guest_failed', 'amount' => 50000, 'currency' => 'INR', 'status' => 'created'],
                    ['id' => 'order_guest_retry', 'amount' => 50000, 'currency' => 'INR', 'status' => 'created']
                );
            $mock->shouldReceive('fetchPayment')->once()->with('pay_guest_failed')->andReturn([
                'id' => 'pay_guest_failed',
                'order_id' => 'order_guest_failed',
                'amount' => 50000,
                'currency' => 'INR',
                'status' => 'failed',
            ]);
            $mock->shouldReceive('assertPaymentMatchesGroupWebinarParticipant')->once();
        });

        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $this->post(route('group-webinars.payment.start', $token))->assertRedirect();
        $payment = $guest->payments()->firstOrFail();

        $this->post(route('group-webinars.payment.failure', [
            'token' => $token,
            'payment' => $payment->public_reference,
        ]), ['razorpay_payment_id' => 'pay_guest_failed'])
            ->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('pay_guest_failed', $payment->fresh()->razorpay_payment_id);
        $this->assertSame('failed', $guest->fresh()->payment_status);
        $this->assertSame('pending', $groupRequest->participants->firstWhere('role', 'organizer')->fresh()->payment_status);
        $this->post(route('group-webinars.payment.start', $token))->assertRedirect();
        $this->assertSame(2, $guest->payments()->count());
    }

    public function test_full_coupon_confirms_only_that_guest_without_creating_a_razorpay_order(): void
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
        ]))->assertRedirect(route('webinars.index'));
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $token = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));

        $this->post(route('group-webinars.payment.start', $token), ['coupon_code' => $coupon->code])
            ->assertRedirect(route('group-webinars.invitation', $token));

        $this->assertSame('paid', $guest->fresh()->payment_status);
        $this->assertSame(0, $guest->payments()->whereNotNull('razorpay_order_id')->count());
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'email' => 'guest@example.com',
            'status' => 'redeemed',
        ]);
        $this->assertSame('pending', $groupRequest->participants->firstWhere('role', 'organizer')->fresh()->payment_status);
    }

    public function test_expired_invitation_can_be_resend_without_revealing_whether_details_match(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));
        $groupRequest = GroupWebinarRequest::with('participants.emailDeliveries')->firstOrFail();
        $guest = $groupRequest->participants->firstWhere('email_normalized', 'guest@example.com');
        $oldToken = basename(parse_url($guest->emailDeliveries->first()->action_url, PHP_URL_PATH));
        $guest->update(['invitation_expires_at' => now()->subMinute()]);

        $this->flushSession();
        $this->get(route('group-webinars.invitation', $oldToken))
            ->assertOk()
            ->assertSee('Invitation expired')
            ->assertDontSee('guest@example.com')
            ->assertDontSee($groupRequest->reference);

        $this->post(route('group-webinars.invitation.resend'), [
            'reference' => $groupRequest->reference,
            'email' => 'guest@example.com',
        ])->assertSessionHas('success', 'If the reference and email match an active participant, a new invitation has been sent.');

        $this->get(route('group-webinars.invitation', $oldToken))->assertNotFound();
        $newDelivery = $guest->emailDeliveries()->latest()->firstOrFail();
        $newToken = basename(parse_url($newDelivery->action_url, PHP_URL_PATH));
        $this->assertNotSame($oldToken, $newToken);
        $this->get(route('group-webinars.invitation', $newToken))->assertOk()->assertSee('guest@example.com');
    }

    public function test_admin_schedule_updates_notify_participants_without_duplicate_emails(): void
    {
        $organizer = User::factory()->create(['email' => 'organizer@example.com']);
        $this->actingAs($organizer)->post(route('group-webinars.store'), $this->requestData([
            ['email' => 'guest@example.com'],
        ]))->assertRedirect(route('webinars.index'));
        $groupRequest = GroupWebinarRequest::firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $scheduledAt = now()->addDays(3)->format('Y-m-d\TH:i');
        $payload = [
            'status' => 'confirmed',
            'scheduled_at' => $scheduledAt,
            'meeting_link' => 'https://meet.example.com/group',
            'admin_response' => 'Please join five minutes early.',
        ];

        $this->actingAs($admin)->patch(route('admin.group-webinars.update', $groupRequest), $payload)
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));

        $this->assertSame('confirmed', $groupRequest->fresh()->status);
        $this->assertSame(2, $groupRequest->participants()->whereHas('emailDeliveries', fn ($query) => $query->where('notification_type', 'schedule_update'))->count());
        Queue::assertPushed(SendGroupWebinarEmail::class, 4);

        $this->patch(route('admin.group-webinars.update', $groupRequest), $payload)
            ->assertRedirect(route('admin.group-webinars.show', $groupRequest));
        Queue::assertPushed(SendGroupWebinarEmail::class, 4);
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

        $this->actingAs($admin)->get(route('admin.group-webinars.index'))
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
        return [
            'topic' => 'Knee rehabilitation',
            'description' => 'Discuss shared rehabilitation questions and clinical approach.',
            'participants' => $participants,
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

    private function createAdminRequest(string $reference, string $status, array $participants, ?\Illuminate\Support\Carbon $scheduledAt = null): GroupWebinarRequest
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
                    'public_reference' => (string) \Illuminate\Support\Str::uuid(),
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
