<?php

namespace App\Notifications;

use App\Models\DoubtSessionFollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Traits\EmailNotification;

class DoubtSessionFollowUpAnswered extends Notification
{
    use Queueable;
    use EmailNotification;

    public function __construct(
        public readonly DoubtSessionFollowUp $followUp
    ) {
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'doubt_session_follow_up_answered',
            'title' => 'Your follow-up has been answered',
            'message' => 'An admin responded to your follow-up question for session '
                . $this->followUp->booking->booking_reference . '.',
            'booking_id' => $this->followUp->doubt_session_booking_id,
            'booking_reference' => $this->followUp->booking->booking_reference,
            'follow_up_id' => $this->followUp->id,
            'created_at' => now()->toISOString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
