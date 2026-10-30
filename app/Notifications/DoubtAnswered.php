<?php

namespace App\Notifications;

use App\Models\Doubt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Traits\EmailNotification;

class DoubtAnswered extends Notification
{
    use Queueable;
    use EmailNotification;

    public function __construct(public readonly Doubt $doubt)
    {
    }

    public function toDatabase(object $notifiable): array
    {
        $context = $this->doubt->subject?->name
            ?? $this->doubt->topic
            ?? 'your doubt';

        return [
            'type' => 'doubt_answered',
            'title' => 'Your doubt has been answered',
            'message' => 'An admin answered your ' . $context . ' doubt.',
            'doubt_id' => $this->doubt->id,
            'subject' => $this->doubt->subject?->name,
            'topic' => $this->doubt->topic,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
