<?php

namespace App\Notifications;

use App\Models\DoubtSessionBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoubtSessionStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly DoubtSessionBooking $booking,
        public readonly string $previousStatus,
        public readonly string $newStatus
    ) {
    }

    /**
     * Store the notification in the database.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    /**
     * Notification data stored in the notifications table.
     */
    public function toDatabase(object $notifiable): array
    {
        $statusLabel = ucwords(
            str_replace(
                '_',
                ' ',
                $this->newStatus
            )
        );

        return [
            'type' =>
                'doubt_session_status_updated',

            'title' =>
                'Doubt Session Updated',

            'message' =>
                'Your doubt session booking status has been changed to '
                . $statusLabel
                . '.',

            'booking_id' =>
                $this->booking->id,

            'booking_reference' =>
                $this->booking->booking_reference,

            'previous_status' =>
                $this->previousStatus,

            'booking_status' =>
                $this->newStatus,

            'scheduled_at' =>
                $this->booking->scheduled_at?->toISOString(),

            'created_at' =>
                now()->toISOString(),
        ];
    }

    /**
     * Array representation.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase(
            $notifiable
        );
    }
}
