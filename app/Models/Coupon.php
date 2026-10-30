<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const SERVICE_DOUBT_SESSION = 'doubt_session';
    public const SERVICE_WEBINAR = 'webinar';
    public const SERVICE_BOTH = 'both';

    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'starts_at', 'expires_at',
        'usage_limit', 'per_user_limit', 'minimum_amount', 'maximum_discount',
        'applies_to', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function appliesTo(string $service): bool
    {
        return $this->applies_to === self::SERVICE_BOTH || $this->applies_to === $service;
    }
}
