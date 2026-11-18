<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    protected $fillable = [
        'source',
        'message_key',
        'author_name',
        'body',
        'is_public',
        'sync_status',
        'attachment_metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'attachment_metadata' => 'array',
        ];
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }
}
