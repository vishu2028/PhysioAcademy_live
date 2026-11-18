<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Traits\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportTicketUpdated extends Notification
{
    use EmailNotification;
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $eventKey,
        public readonly string $event,
        public readonly string $notificationMessage,
    ) {}

    public function toDatabase(object $notifiable): array
    {
        [$title, $message] = match ($this->event) {
            'created' => ['Support ticket submitted', $this->notificationMessage],
            'reply' => ['Support replied to your ticket', $this->notificationMessage],
            default => ['Support ticket status updated', $this->notificationMessage],
        };

        return [
            'type' => 'support_ticket_'.$this->event,
            'title' => $title,
            'message' => $message,
            'support_ticket_id' => $this->ticket->id,
            'ticket_reference' => $this->ticket->rootflash_ticket_id ?: $this->ticket->source_reference,
            'event_key' => $this->eventKey,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function emailDetails(object $notifiable): array
    {
        return [
            'Ticket reference' => $this->ticket->rootflash_ticket_id ?: $this->ticket->source_reference,
            'Subject' => $this->ticket->title,
        ];
    }

    protected function emailActionUrl(array $data): ?string
    {
        return route('admin.help.support-ticket.show', $this->ticket);
    }
}
