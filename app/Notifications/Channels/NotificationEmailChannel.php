<?php

namespace App\Notifications\Channels;

use App\Jobs\SendNotificationEmail;
use App\Models\NotificationEmailDelivery;
use Illuminate\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class NotificationEmailChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! filled($notifiable->email) || ! method_exists($notification, 'emailData')) {
            return;
        }

        $data = $notification->emailData($notifiable);
        $key = $notification->emailDeliveryKey($notifiable);

        try {
            [$delivery, $shouldDispatch] = DB::transaction(function () use ($key, $notifiable, $notification): array {
                $existing = NotificationEmailDelivery::where('delivery_key', $key)
                    ->lockForUpdate()
                    ->first();

                if ($existing && ! $existing->failed_at) {
                    return [$existing, false];
                }

                if ($existing) {
                    $existing->forceFill([
                        'failed_at' => null,
                        'failure_message' => null,
                    ])->save();

                    return [$existing, true];
                }

                return [NotificationEmailDelivery::create([
                    'delivery_key' => $key,
                    'user_id' => $notifiable->getKey(),
                    'email' => $notifiable->email,
                    'notification_type' => $notification::class,
                ]), true];
            });
        } catch (QueryException $exception) {
            if (! str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                throw $exception;
            }

            $delivery = NotificationEmailDelivery::where('delivery_key', $key)->firstOrFail();
            $shouldDispatch = false;
        }

        if ($delivery->sent_at || ! $shouldDispatch) {
            return;
        }

        SendNotificationEmail::dispatch(
            deliveryId: $delivery->id,
            recipient: $notifiable->email,
            subject: $data['title'],
            title: $data['title'],
            message: $data['message'],
            actionUrl: $data['action_url']
        );
    }
}
