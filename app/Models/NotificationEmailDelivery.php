<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEmailDelivery extends Model
{
    protected $fillable = [
        'delivery_key',
        'user_id',
        'email',
        'notification_type',
        'sent_at',
        'failed_at',
        'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
