<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CouponRedemption extends Model
{
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_REDEEMED = 'redeemed';
    public const STATUS_RELEASED = 'released';

    protected $fillable = [
        'coupon_id', 'user_id', 'email', 'redeemable_type', 'redeemable_id',
        'coupon_code', 'original_amount', 'discount_amount', 'final_amount',
        'status', 'reserved_until', 'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'reserved_until' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function redeemable(): MorphTo
    {
        return $this->morphTo();
    }
}
