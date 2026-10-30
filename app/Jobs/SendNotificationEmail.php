<?php

namespace App\Jobs;

use App\Mail\UserNotificationMail;
use App\Models\NotificationEmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $deliveryId,
        public readonly string $recipient,
        public readonly string $subject,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl
    ) {
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $delivery = NotificationEmailDelivery::find($this->deliveryId);

        if (! $delivery || $delivery->sent_at) {
            return;
        }

        Mail::mailer(config('mail.default'))->to($this->recipient)->send(new UserNotificationMail(
            subjectLine: $this->subject,
            title: $this->title,
            notificationMessage: $this->message,
            actionUrl: $this->actionUrl
        ));

        $delivery->forceFill([
            'sent_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        NotificationEmailDelivery::whereKey($this->deliveryId)->update([
            'failed_at' => now(),
            'failure_message' => 'Email delivery failed after retries ('.$exception::class.').',
        ]);

        Log::error('Notification email delivery failed after retries.', [
            'delivery_id' => $this->deliveryId,
            'exception' => $exception::class,
        ]);
    }
}
