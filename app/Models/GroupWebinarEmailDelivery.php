<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupWebinarEmailDelivery extends Model
{
    protected $fillable = [
        'participant_id',
        'delivery_key',
        'notification_type',
        'recipient',
        'subject',
        'title',
        'message',
        'action_url',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'action_url' => 'encrypted',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(GroupWebinarParticipant::class, 'participant_id');
    }
}
