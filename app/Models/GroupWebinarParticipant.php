<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupWebinarParticipant extends Model
{
    public const STATUS_INVITATION_SENT = 'invitation_sent';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_FAILED = 'payment_failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_NOT_REQUIRED = 'not_required';

    protected $fillable = [
        'group_webinar_request_id',
        'user_id',
        'coupon_id',
        'name',
        'email',
        'email_normalized',
        'role',
        'status',
        'payment_status',
        'amount',
        'amount_due',
        'coupon_code',
        'invitation_token_hash',
        'invitation_expires_at',
        'invitation_sent_at',
    ];

    protected $hidden = ['invitation_token_hash'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'invitation_expires_at' => 'datetime',
            'invitation_sent_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarRequest::class, 'group_webinar_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GroupWebinarParticipantPayment::class, 'participant_id');
    }

    public function emailDeliveries(): HasMany
    {
        return $this->hasMany(GroupWebinarEmailDelivery::class, 'participant_id');
    }
}
