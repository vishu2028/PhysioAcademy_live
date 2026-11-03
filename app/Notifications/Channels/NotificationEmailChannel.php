<?php

namespace App\Notifications\Channels;

use App\Jobs\SendNotificationEmail;
use App\Models\NotificationEmailDelivery;
use Illuminate\Database\QueryException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class NotificationEmailChannel
{
    public function send(object $notifiable, Notification $notification, ?array $notificationData = null): void
    {
        if (! filled($notifiable->email)) {
            return;
        }

        $delivery = null;
        $key = null;
        try {
            $data = $this->emailData($notifiable, $notification, $notificationData);
            $key = $this->deliveryKey($notifiable, $notification);
            [$delivery, $shouldSend] = DB::transaction(function () use ($key, $notifiable, $notification): array {
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
            if (! $key || ! $this->isUniqueConstraintViolation($exception)) {
                $this->logFailure($notifiable, $notification, $exception);
                return;
            }

            try {
                $delivery = NotificationEmailDelivery::where('delivery_key', $key)->first();
            } catch (Throwable $lookupException) {
                $this->logFailure($notifiable, $notification, $lookupException);
                return;
            }

            if (! $delivery) {
                $this->logFailure($notifiable, $notification, $exception);
                return;
            }

            $shouldSend = false;
        } catch (Throwable $exception) {
            $this->logFailure($notifiable, $notification, $exception);
            return;
        }

        if ($delivery->sent_at || ! $shouldSend) {
            return;
        }

        try {
            DB::afterCommit(function () use ($delivery, $data, $notifiable, $notification): void {
                try {
                    SendNotificationEmail::dispatchSync(
                        deliveryId: $delivery->id,
                        subject: $data['title'],
                        title: $data['title'],
                        message: $data['message'],
                        actionUrl: $data['action_url'],
                        details: $data['details'] ?? []
                    );
                } catch (Throwable $exception) {
                    try {
                        NotificationEmailDelivery::whereKey($delivery->id)->update([
                            'failed_at' => now(),
                            'failure_message' => 'Email delivery failed ('.$exception::class.').',
                        ]);
                    } catch (Throwable $recordingException) {
                        $this->logFailure($notifiable, $notification, $recordingException, $delivery->id);
                    }

                    $this->logFailure($notifiable, $notification, $exception, $delivery->id);
                }
            });
        } catch (Throwable $exception) {
            $this->logFailure($notifiable, $notification, $exception, $delivery->id);
        }
    }

    private function emailData(object $notifiable, Notification $notification, ?array $notificationData): array
    {
        if (method_exists($notification, 'emailData')) {
            return $notification->emailData($notifiable);
        }

        $data = $notificationData
            ?? (method_exists($notification, 'toDatabase')
                ? $notification->toDatabase($notifiable)
                : $notification->toArray($notifiable));

        return [
            'title' => (string) ($data['title'] ?? class_basename($notification)),
            'message' => (string) ($data['message'] ?? ''),
            'action_url' => route('home'),
            'details' => [],
        ];
    }

    private function deliveryKey(object $notifiable, Notification $notification): string
    {
        if (method_exists($notification, 'emailDeliveryKey')) {
            return $notification->emailDeliveryKey($notifiable);
        }

        $notification->id ??= (string) Str::uuid();

        return hash('sha256', $notification::class.'|'.$notifiable->getKey().'|'.$notification->id);
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true);
    }

    private function logFailure(
        object $notifiable,
        Notification $notification,
        Throwable $exception,
        ?int $deliveryId = null
    ): void {
        Log::error('Notification email could not be delivered.', [
            'delivery_id' => $deliveryId,
            'notifiable_id' => $notifiable->getKey(),
            'notification_type' => $notification::class,
            'exception' => $exception::class,
        ]);
    }
}
