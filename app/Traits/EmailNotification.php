<?php

namespace App\Traits;

trait EmailNotification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function emailData(object $notifiable): array
    {
        $data = $this->toDatabase($notifiable);

        return [
            'title' => (string) ($data['title'] ?? 'Account update'),
            'message' => (string) ($data['message'] ?? ''),
            'action_url' => $this->emailActionUrl($data),
            'details' => method_exists($this, 'emailDetails')
                ? $this->emailDetails($notifiable)
                : [],
        ];
    }

    public function emailDeliveryKey(object $notifiable): string
    {
        return hash('sha256', static::class.'|'.$notifiable->getKey().'|'.$this->id);
    }

    protected function emailActionUrl(array $data): ?string
    {
        return match ($data['type'] ?? null) {
            'doubt_answered' => route('doubts.mine'),
            'doubt_session_status_updated' => route('doubt-sessions.history'),
            'doubt_session_follow_up_answered' => route('doubt-sessions.history') . '#session-booking-' . $data['booking_id'],
            'topic_request_status_updated' => $data['topic_link'] ?? route('topic-requests.mine'),
            'webinar_registration', 'webinar_updated' => route('webinars.my'),
            default => route('home'),
        };
    }
}
