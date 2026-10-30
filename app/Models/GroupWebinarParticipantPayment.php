<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupWebinarParticipantPayment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_NOT_REQUIRED = 'not_required';

    protected $fillable = [
        'participant_id',
        'coupon_id',
        'public_reference',
        'coupon_code',
        'original_amount',
        'discount_amount',
        'amount',
        'currency',
        'status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_error',
        'paid_at',
    ];

    protected $hidden = ['razorpay_signature'];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarParticipant::class, 'participant_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
