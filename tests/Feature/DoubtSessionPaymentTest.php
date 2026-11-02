<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Coupon;
use App\Models\DoubtSessionBooking;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class DoubtSessionPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'doubt_session_enabled' => '1',
            'doubt_session_is_free' => '0',
            'doubt_session_price' => '1000',
            'doubt_session_duration_minutes' => '60',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'doubt_session',
                'label' => $key,
                'type' => 'text',
            ]);
        }
    }

    public function test_percentage_coupon_persists_discount_and_creates_checkout_for_final_amount(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon(Coupon::TYPE_PERCENTAGE, 20, 'SESSION20');
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->twice()->andReturn(true);
            $mock->shouldReceive('createOrder')->once()
                ->withArgs(fn (DoubtSessionBooking $booking): bool => (float) $booking->amount === 800.0)
                ->andReturn(['id' => 'order_session_20', 'amount' => 80000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('keyId')->once()->andReturn('rzp_test_public');
            $mock->shouldReceive('amountToSubunits')->once()
                ->withArgs(fn ($amount): bool => (float) $amount === 800.0)
                ->andReturn(80000);
        });

        $this->actingAs($user)
            ->post(route('doubt-sessions.store'), $this->bookingData([
                'coupon_code' => $coupon->code,
            ]))
            ->assertRedirect();

        $booking = DoubtSessionBooking::firstOrFail();
        $this->assertSame(1000.0, (float) $booking->original_amount);
        $this->assertSame(200.0, (float) $booking->discount_amount);
        $this->assertSame(800.0, (float) $booking->amount);
        $this->assertSame('SESSION20', $booking->coupon_code);
        $this->assertSame('order_session_20', $booking->razorpay_order_id);
        $this->assertSame(DoubtSessionBooking::PAYMENT_PENDING, $booking->payment_status);

        $this->get(route('doubt-sessions.payment', $booking))
            ->assertOk()
            ->assertSee('SESSION20')
            ->assertSee('20% discount')
            ->assertSee('₹800.00')
            ->assertSee('amount: 80000', false);
    }

    public function test_fixed_coupon_and_coupon_removal_use_the_original_booking_price(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon(Coupon::TYPE_FIXED, 200, 'LESS200');
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->twice()->andReturn(true);
            $mock->shouldReceive('createOrder')->twice()
                ->withArgs(fn (DoubtSessionBooking $booking): bool => in_array((float) $booking->amount, [800.0, 1000.0], true))
                ->andReturn(
                    ['id' => 'order_session_fixed', 'amount' => 80000, 'currency' => 'INR', 'status' => 'created'],
                    ['id' => 'order_session_full', 'amount' => 100000, 'currency' => 'INR', 'status' => 'created']
                );
        });

        $this->actingAs($user)->post(route('doubt-sessions.store'), $this->bookingData([
            'coupon_code' => $coupon->code,
        ]))->assertRedirect();
        $discounted = DoubtSessionBooking::query()->where('coupon_code', $coupon->code)->firstOrFail();
        $this->assertSame(200.0, (float) $discounted->discount_amount);
        $this->assertSame(800.0, (float) $discounted->amount);

        $this->post(route('doubt-sessions.store'), $this->bookingData())
            ->assertRedirect();
        $withoutCoupon = DoubtSessionBooking::query()->whereNull('coupon_code')->firstOrFail();
        $this->assertSame(0.0, (float) $withoutCoupon->discount_amount);
        $this->assertSame(1000.0, (float) $withoutCoupon->original_amount);
        $this->assertSame(1000.0, (float) $withoutCoupon->amount);
    }

    public function test_only_verified_captured_payment_marks_a_doubt_session_paid(): void
    {
        $user = User::factory()->create();
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createOrder')
                ->andReturn(['id' => 'order_session_paid', 'amount' => 100000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('verifySignature')->once();
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_session_paid',
                'order_id' => 'order_session_paid',
                'amount' => 100000,
                'currency' => 'INR',
                'status' => 'captured',
            ]);
            $mock->shouldReceive('assertPaymentMatchesBooking')->once();
        });

        $this->actingAs($user)->post(route('doubt-sessions.store'), $this->bookingData())->assertRedirect();
        $booking = DoubtSessionBooking::firstOrFail();

        $this->post(route('doubt-sessions.payment.verify', $booking), [
            'razorpay_payment_id' => 'pay_session_paid',
            'razorpay_order_id' => 'order_session_paid',
            'razorpay_signature' => 'verified-signature',
        ])->assertRedirect(route('doubt-sessions.success', $booking));

        $this->assertSame(DoubtSessionBooking::PAYMENT_PAID, $booking->fresh()->payment_status);
        $this->assertSame('pay_session_paid', $booking->fresh()->razorpay_payment_id);
    }

    public function test_provider_verified_failed_payment_remains_unpaid(): void
    {
        $user = User::factory()->create();
        $this->mock(RazorpayService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('createOrder')
                ->andReturn(['id' => 'order_session_failed', 'amount' => 100000, 'currency' => 'INR', 'status' => 'created']);
            $mock->shouldReceive('fetchPayment')->once()->andReturn([
                'id' => 'pay_session_failed',
                'order_id' => 'order_session_failed',
                'amount' => 100000,
                'currency' => 'INR',
                'status' => 'failed',
            ]);
            $mock->shouldReceive('assertPaymentMatchesBooking')->once();
        });

        $this->actingAs($user)->post(route('doubt-sessions.store'), $this->bookingData())->assertRedirect();
        $booking = DoubtSessionBooking::firstOrFail();

        $this->post(route('doubt-sessions.payment.failure', $booking), [
            'razorpay_payment_id' => 'pay_session_failed',
        ])->assertRedirect(route('doubt-sessions.payment', $booking));

        $this->assertSame(DoubtSessionBooking::PAYMENT_FAILED, $booking->fresh()->payment_status);
        $this->assertNotSame(DoubtSessionBooking::PAYMENT_PAID, $booking->fresh()->payment_status);
    }

    private function coupon(string $type, float $value, string $code): Coupon
    {
        return Coupon::create([
            'code' => $code,
            'discount_type' => $type,
            'discount_value' => $value,
            'applies_to' => Coupon::SERVICE_DOUBT_SESSION,
            'is_active' => true,
        ]);
    }

    private function bookingData(array $overrides = []): array
    {
        $year = AcademicYear::query()->firstOrCreate(
            ['slug' => 'test-year'],
            ['name' => 'Test Year', 'status' => true]
        );
        $subject = Subject::query()->firstOrCreate(
            ['slug' => 'test-subject'],
            ['name' => 'Test Subject', 'status' => true]
        );

        return array_merge([
            'phone' => '+911234567890',
            'academic_year_id' => $year->id,
            'subject_id' => $subject->id,
            'topic' => 'Knee rehabilitation',
            'doubt' => 'How should this clinical issue be assessed?',
        ], $overrides);
    }
}
