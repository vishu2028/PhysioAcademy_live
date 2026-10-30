<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_discount_and_maximum_discount_are_calculated(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon([
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'maximum_discount' => 150,
        ]);

        $quote = app(CouponService::class)->quote($coupon->code, $user->id, Coupon::SERVICE_DOUBT_SESSION, 1000);

        $this->assertSame(150.0, $quote['discount_amount']);
        $this->assertSame(850.0, $quote['final_amount']);
    }

    public function test_fixed_discount_is_capped_at_the_eligible_amount(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon([
            'discount_type' => Coupon::TYPE_FIXED,
            'discount_value' => 500,
        ]);

        $quote = app(CouponService::class)->quote($coupon->code, $user->id, Coupon::SERVICE_WEBINAR, 100);

        $this->assertSame(100.0, $quote['discount_amount']);
        $this->assertSame(0.0, $quote['final_amount']);
    }

    public function test_service_restrictions_and_minimum_amount_are_enforced(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon([
            'applies_to' => Coupon::SERVICE_DOUBT_SESSION,
            'minimum_amount' => 500,
        ]);
        $service = app(CouponService::class);

        try {
            $service->quote($coupon->code, $user->id, Coupon::SERVICE_WEBINAR, 1000);
            $this->fail('A service-specific coupon must not be used by a webinar.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('not applicable', $exception->errors()['coupon_code'][0]);
        }

        try {
            $service->quote($coupon->code, $user->id, Coupon::SERVICE_DOUBT_SESSION, 400);
            $this->fail('A coupon minimum amount must be enforced.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Minimum', $exception->errors()['coupon_code'][0]);
        }
    }

    public function test_inactive_expired_and_usage_limited_coupons_are_rejected(): void
    {
        $user = User::factory()->create();
        $inactive = $this->coupon(['is_active' => false]);
        $this->assertCouponRejected($inactive, $user, 'inactive');

        $expired = $this->coupon(['expires_at' => now()->subMinute()]);
        $this->assertCouponRejected($expired, $user, 'expired');

        $limited = $this->coupon(['usage_limit' => 1, 'per_user_limit' => 1]);
        CouponRedemption::create([
            'coupon_id' => $limited->id,
            'user_id' => $user->id,
            'redeemable_type' => 'App\\Models\\DoubtSessionBooking',
            'redeemable_id' => 42,
            'coupon_code' => $limited->code,
            'original_amount' => 100,
            'discount_amount' => 10,
            'final_amount' => 90,
            'status' => CouponRedemption::STATUS_REDEEMED,
            'redeemed_at' => now(),
        ]);
        $this->assertCouponRejected($limited, $user, 'usage limit');
    }

    public function test_active_reservations_count_toward_limits_and_expired_reservations_do_not(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $coupon = $this->coupon(['usage_limit' => 1]);
        $this->createRedemption($coupon, $firstUser, CouponRedemption::STATUS_RESERVED, now()->subMinute());

        $quote = app(CouponService::class)->quote($coupon->code, $secondUser->id, Coupon::SERVICE_WEBINAR, 100);
        $this->assertSame(10.0, $quote['discount_amount']);

        $this->createRedemption($coupon, $firstUser, CouponRedemption::STATUS_RESERVED, now()->addMinutes(10));

        $this->assertCouponRejected($coupon, $secondUser, 'usage limit');
    }

    public function test_future_start_and_per_user_limits_are_enforced(): void
    {
        $user = User::factory()->create();
        $future = $this->coupon(['starts_at' => now()->addDay()]);
        $this->assertCouponRejected($future, $user, 'not active yet');

        $perUser = $this->coupon(['per_user_limit' => 1]);
        $this->createRedemption($perUser, $user, CouponRedemption::STATUS_REDEEMED, null);
        $this->assertCouponRejected($perUser, $user, 'usage limit');
    }

    public function test_public_coupon_list_only_includes_currently_available_discounts(): void
    {
        $user = User::factory()->create();
        $best = $this->coupon([
            'code' => 'BEST20',
            'discount_value' => 20,
            'applies_to' => Coupon::SERVICE_WEBINAR,
            'minimum_amount' => 100,
            'maximum_discount' => 50,
        ]);
        $this->coupon(['is_active' => false]);
        $this->coupon(['starts_at' => now()->addDay()]);
        $this->coupon(['expires_at' => now()->subMinute()]);
        $this->coupon(['applies_to' => Coupon::SERVICE_DOUBT_SESSION]);
        $this->coupon(['minimum_amount' => 1000]);
        $exhausted = $this->coupon(['usage_limit' => 1]);
        $this->createRedemption($exhausted, $user, CouponRedemption::STATUS_REDEEMED, null);

        $coupons = app(CouponService::class)->publicCoupons([
            Coupon::SERVICE_WEBINAR => [500],
        ]);

        $this->assertSame([$best->id], $coupons->pluck('id')->all());
        $this->assertSame(['webinar'], $coupons->first()->public_services);
        $this->assertSame(50.0, $coupons->first()->public_discount_rank);
    }

    public function test_homepage_renders_only_eligible_coupons_before_request_a_topic(): void
    {
        Role::create(['name' => 'user', 'guard_name' => 'web']);

        foreach ([
            'doubt_session_enabled' => '1',
            'doubt_session_is_free' => '0',
            'doubt_session_price' => '500',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'doubt_session',
                'label' => $key,
                'type' => 'text',
            ]);
        }

        $coupon = $this->coupon([
            'code' => 'PUBLIC10',
            'applies_to' => Coupon::SERVICE_DOUBT_SESSION,
        ]);
        $response = $this->get(route('home'))->assertOk()
            ->assertSee('PUBLIC10')
            ->assertSee('10% OFF');
        $html = $response->getContent();
        $promoPosition = strpos($html, '<section class="section coupon-promo-section"');
        $topicPosition = strpos($html, '<section class="section request-topic-section" id="request-topic">');

        $this->assertNotFalse($promoPosition);
        $this->assertNotFalse($topicPosition);
        $this->assertLessThan($topicPosition, $promoPosition);

        $coupon->update(['is_active' => false]);
        $this->get(route('home'))->assertOk()
            ->assertDontSee('<section class="section coupon-promo-section"', false);
    }

    public function test_coupon_admin_saves_and_displays_validity_times_in_ist(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $form = $this->actingAs($admin)
            ->get(route('admin.coupons.create'))
            ->assertOk()
            ->assertSee('Starts at (IST)')
            ->assertSee('Expires at (IST)');

        $this->post(route('admin.coupons.store'), [
            'code' => 'ISTOFFER',
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'starts_at' => '2026-10-31T10:00',
            'expires_at' => '2026-10-31T12:00',
            'applies_to' => Coupon::SERVICE_DOUBT_SESSION,
            'is_active' => '1',
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'ISTOFFER')->firstOrFail();
        $this->assertSame(
            CarbonImmutable::parse('2026-10-31T10:00', 'Asia/Kolkata')->utc()->toDateTimeString(),
            $coupon->starts_at->toDateTimeString()
        );
        $this->assertSame(
            CarbonImmutable::parse('2026-10-31T12:00', 'Asia/Kolkata')->utc()->toDateTimeString(),
            $coupon->expires_at->toDateTimeString()
        );

        $this->get(route('admin.coupons.edit', $coupon))
            ->assertOk()
            ->assertSee('value="2026-10-31T10:00"', false)
            ->assertSee('value="2026-10-31T12:00"', false);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAVE'.fake()->unique()->numerify('####'),
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'applies_to' => Coupon::SERVICE_BOTH,
            'is_active' => true,
        ], $overrides));
    }

    private function assertCouponRejected(Coupon $coupon, User $user, string $contains): void
    {
        try {
            app(CouponService::class)->quote($coupon->code, $user->id, Coupon::SERVICE_DOUBT_SESSION, 100);
            $this->fail('Expected coupon validation to reject this coupon.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString($contains, mb_strtolower($exception->errors()['coupon_code'][0]));
        }
    }

    private function createRedemption(Coupon $coupon, User $user, string $status, $reservedUntil): void
    {
        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'redeemable_type' => 'App\\Models\\DoubtSessionBooking',
            'redeemable_id' => random_int(100, 999999),
            'coupon_code' => $coupon->code,
            'original_amount' => 100,
            'discount_amount' => 10,
            'final_amount' => 90,
            'status' => $status,
            'reserved_until' => $reservedUntil,
        ]);
    }
}
