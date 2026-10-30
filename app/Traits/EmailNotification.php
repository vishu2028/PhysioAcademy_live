<?php

namespace App\Traits;

use App\Notifications\Channels\NotificationEmailChannel;

trait EmailNotification
{
    public function via(object $notifiable): array
    {
        return ['database', NotificationEmailChannel::class];
    }

    public function emailData(object $notifiable): array
    {
        $data = $this->toDatabase($notifiable);

        return [
            'title' => (string) ($data['title'] ?? 'Account update'),
            'message' => (string) ($data['message'] ?? ''),
            'action_url' => $this->emailActionUrl($data),
        ];
    }

    public function emailDeliveryKey(object $notifiable): string
    {
        $data = $this->toDatabase($notifiable);
        unset($data['created_at']);

        return hash('sha256', static::class.'|'.$notifiable->getKey().'|'.json_encode($data, JSON_THROW_ON_ERROR));
    }

    protected function emailActionUrl(array $data): ?string
    {
        return match ($data['type'] ?? null) {
            'doubt_answered' => route('doubts.mine'),
            'doubt_session_status_updated' => route('doubt-sessions.history'),
            'topic_request_status_updated' => $data['topic_link'] ?? route('topic-requests.mine'),
            'webinar_registration', 'webinar_updated' => route('webinars.my'),
            default => route('home'),
        };
    }
}
