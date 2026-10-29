<?php

namespace App\Notifications;

use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebinarUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Webinar $webinar,
        public readonly string $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'webinar_updated',
            'title' => 'Webinar update',
            'message' => $this->message,
            'webinar_id' => $this->webinar->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
