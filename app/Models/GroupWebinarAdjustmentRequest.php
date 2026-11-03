<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupWebinarAdjustmentRequest extends Model
{
    public const TYPE_DROPOUT_REFUND = 'dropout_refund';

    public const TYPE_SESSION_CREDIT = 'session_credit';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'group_webinar_request_id',
        'participant_id',
        'payment_id',
        'requested_by',
        'processed_by',
        'type',
        'amount',
        'status',
        'provider_reference',
        'admin_response',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarRequest::class, 'group_webinar_request_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarParticipant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarParticipantPayment::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
