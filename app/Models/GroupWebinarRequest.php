<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupWebinarRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'organizer_id',
        'reference',
        'organizer_name',
        'organizer_email',
        'organizer_whatsapp',
        'topic',
        'description',
        'preferred_slots',
        'participant_count',
        'minimum_participants',
        'maximum_participants',
        'duration_minutes',
        'price_per_person',
        'total_amount',
        'currency',
        'is_free',
        'status',
        'scheduled_at',
        'meeting_link',
        'admin_response',
        'confirmed_by',
        'policy_version',
        'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'participant_count' => 'integer',
            'minimum_participants' => 'integer',
            'maximum_participants' => 'integer',
            'duration_minutes' => 'integer',
            'price_per_person' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_free' => 'boolean',
            'scheduled_at' => 'datetime',
            'preferred_slots' => 'array',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GroupWebinarParticipant::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(GroupWebinarAdjustmentRequest::class, 'group_webinar_request_id');
    }
}
