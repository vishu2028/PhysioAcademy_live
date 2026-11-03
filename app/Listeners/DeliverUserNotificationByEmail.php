<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\Channels\NotificationEmailChannel;
use Illuminate\Notifications\Events\NotificationSent;

class DeliverUserNotificationByEmail
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || ! $event->notifiable instanceof User) {
            return;
        }

        app(NotificationEmailChannel::class)->send(
            $event->notifiable,
            $event->notification
        );
    }
}
