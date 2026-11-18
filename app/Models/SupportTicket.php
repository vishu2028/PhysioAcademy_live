<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_WAITING_FOR_CLIENT = 'waiting_for_client';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_WAITING_FOR_CLIENT,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public const REPLY_ALLOWED_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_WAITING_FOR_CLIENT,
    ];

    protected $fillable = [
        'user_id',
        'requester_name',
        'requester_email',
        'source_reference',
        'rootflash_ticket_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'attachment_paths',
        'submission_hash',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'attachment_paths' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    public function latestPublicReply(): HasOne
    {
        return $this->hasOne(SupportTicketReply::class)
            ->where('is_public', true)
            ->latestOfMany('created_at');
    }
}
