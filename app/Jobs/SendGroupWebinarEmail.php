<?php

namespace App\Jobs;

use App\Mail\UserNotificationMail;
use App\Models\GroupWebinarEmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendGroupWebinarEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $deliveryId)
    {
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $delivery = GroupWebinarEmailDelivery::find($this->deliveryId);
        if (! $delivery || $delivery->sent_at) {
            return;
        }

        Mail::mailer(config('mail.default'))->to($delivery->recipient)->send(new UserNotificationMail(
            subjectLine: $delivery->subject,
            title: $delivery->title,
            notificationMessage: $delivery->message,
            actionUrl: $delivery->action_url
        ));

        $delivery->forceFill([
            'sent_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ])->save();

        if ($delivery->notification_type === 'invitation') {
            $delivery->participant()->update(['invitation_sent_at' => now()]);
        }
    }

    public function failed(Throwable $exception): void
    {
        GroupWebinarEmailDelivery::whereKey($this->deliveryId)->update([
            'failed_at' => now(),
            'failure_message' => 'Email delivery failed after retries ('.$exception::class.').',
        ]);

        Log::error('Group webinar email delivery failed after retries.', [
            'delivery_id' => $this->deliveryId,
            'exception' => $exception::class,
        ]);
    }
}
