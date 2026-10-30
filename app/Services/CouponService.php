<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Return public coupons that are currently usable for at least one
     * available, paid booking price.
     *
     * @param array<string, array<float>> $serviceAmounts
     */
    public function publicCoupons(array $serviceAmounts): Collection
    {
        $serviceAmounts = array_filter(
            $serviceAmounts,
            static fn (array $amounts): bool => count(array_filter(
                $amounts,
                static fn (float $amount): bool => $amount > 0
            )) > 0
        );
        if ($serviceAmounts === []) {
            return new Collection();
        }

        $now = now();
        $coupons = Coupon::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', $now))
            ->withCount([
                'redemptions as active_redemptions_count' => fn ($query) => $query
                    ->where(function ($redemptions) use ($now): void {
                        $redemptions->where('status', CouponRedemption::STATUS_REDEEMED)
                            ->orWhere(function ($reserved) use ($now): void {
                                $reserved->where('status', CouponRedemption::STATUS_RESERVED)
                                    ->where('reserved_until', '>', $now);
                            });
                    }),
            ])
            ->get();

        return $coupons->map(function (Coupon $coupon) use ($serviceAmounts): ?Coupon {
            if ($coupon->usage_limit !== null && $coupon->active_redemptions_count >= $coupon->usage_limit) {
                return null;
            }

            $applicableServices = [];
            $bestDiscount = 0.0;
            foreach ($serviceAmounts as $service => $amounts) {
                if (! $coupon->appliesTo($service)) {
                    continue;
                }

                foreach ($amounts as $amount) {
                    if ($coupon->minimum_amount !== null && $amount < (float) $coupon->minimum_amount) {
                        continue;
                    }

                    $discount = $coupon->discount_type === Coupon::TYPE_PERCENTAGE
                        ? $amount * ((float) $coupon->discount_value / 100)
                        : (float) $coupon->discount_value;
                    if ($coupon->maximum_discount !== null) {
                        $discount = min($discount, (float) $coupon->maximum_discount);
                    }
                    $discount = round(min(max($discount, 0), $amount), 2);
                    if ($discount <= 0) {
                        continue;
                    }

                    $applicableServices[$service] = true;
                    $bestDiscount = max($bestDiscount, $discount);
                }
            }

            if ($applicableServices === []) {
                return null;
            }

            $coupon->setAttribute('public_services', array_keys($applicableServices));
            $coupon->setAttribute('public_discount_rank', $bestDiscount);

            return $coupon;
        })
            ->filter()
            ->sortByDesc('public_discount_rank')
            ->values();
    }

    public function quote(string $code, ?int $userId, string $service, float $amount, bool $lock = false, ?string $email = null): array
    {
        $query = Coupon::query()->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))]);
        if ($lock) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();
        if (! $coupon) {
            $this->invalid('Invalid coupon code.');
        }
        if (! $coupon->is_active) {
            $this->invalid('This coupon is inactive.');
        }
        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            $this->invalid('This coupon is not active yet.');
        }
        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            $this->invalid('This coupon has expired.');
        }
        if (! $coupon->appliesTo($service)) {
            $this->invalid('This coupon is not applicable to this service.');
        }
        if ($coupon->minimum_amount !== null && $amount < (float) $coupon->minimum_amount) {
            $this->invalid('Minimum booking amount not met.');
        }

        $activeRedemptions = $coupon->redemptions()
            ->where(function ($query): void {
                $query->where('status', CouponRedemption::STATUS_REDEEMED)
                    ->orWhere(function ($reserved): void {
                        $reserved->where('status', CouponRedemption::STATUS_RESERVED)
                            ->where('reserved_until', '>', now());
                    });
            });

        if ($coupon->usage_limit !== null && (clone $activeRedemptions)->count() >= $coupon->usage_limit) {
            $this->invalid('This coupon has reached its total usage limit.');
        }
        if ($coupon->per_user_limit !== null) {
            $userRedemptions = (clone $activeRedemptions);
            if ($userId !== null) {
                $userRedemptions->where('user_id', $userId);
            } elseif (filled($email)) {
                $userRedemptions->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))]);
            } else {
                $userRedemptions->whereRaw('1 = 0');
            }

            if ($userRedemptions->count() >= $coupon->per_user_limit) {
                $this->invalid('You have reached the usage limit for this coupon.');
            }
        }

        $discount = $coupon->discount_type === Coupon::TYPE_PERCENTAGE
            ? $amount * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;
        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }
        $discount = round(min(max($discount, 0), $amount), 2);

        if ($discount <= 0) {
            $this->invalid('This coupon does not provide a discount for this amount.');
        }

        return [
            'coupon' => $coupon,
            'original_amount' => round($amount, 2),
            'discount_amount' => $discount,
            'final_amount' => round(max($amount - $discount, 0), 2),
        ];
    }

    public function reserve(array $quote, ?int $userId, Model $redeemable, ?string $email = null): CouponRedemption
    {
        /** @var Coupon $coupon */
        $coupon = $quote['coupon'];

        return CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'user_id' => $userId,
            'email' => $email ? mb_strtolower(trim($email)) : null,
            'redeemable_type' => $redeemable::class,
            'redeemable_id' => $redeemable->getKey(),
            'coupon_code' => $coupon->code,
            'original_amount' => $quote['original_amount'],
            'discount_amount' => $quote['discount_amount'],
            'final_amount' => $quote['final_amount'],
            'status' => $quote['final_amount'] <= 0
                ? CouponRedemption::STATUS_REDEEMED
                : CouponRedemption::STATUS_RESERVED,
            'reserved_until' => $quote['final_amount'] <= 0 ? null : now()->addMinutes(30),
            'redeemed_at' => $quote['final_amount'] <= 0 ? now() : null,
        ]);
    }

    public function redeem(Model $redeemable): void
    {
        CouponRedemption::query()
            ->where('redeemable_type', $redeemable::class)
            ->where('redeemable_id', $redeemable->getKey())
            ->where('status', CouponRedemption::STATUS_RESERVED)
            ->update([
                'status' => CouponRedemption::STATUS_REDEEMED,
                'redeemed_at' => now(),
                'reserved_until' => null,
                'updated_at' => now(),
            ]);
    }

    public function release(Model $redeemable): void
    {
        CouponRedemption::query()
            ->where('redeemable_type', $redeemable::class)
            ->where('redeemable_id', $redeemable->getKey())
            ->where('status', CouponRedemption::STATUS_RESERVED)
            ->update([
                'status' => CouponRedemption::STATUS_RELEASED,
                'reserved_until' => null,
                'updated_at' => now(),
            ]);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['coupon_code' => $message]);
    }
}
