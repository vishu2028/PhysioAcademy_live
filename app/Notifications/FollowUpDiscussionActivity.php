<?php

namespace App\Notifications;

use App\Models\DoubtSessionFollowUp;
use App\Traits\EmailNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FollowUpDiscussionActivity extends Notification
{
    use EmailNotification;

    public function __construct(
        private readonly DoubtSessionFollowUp $thread,
        private readonly DoubtSessionFollowUp $reply,
        private readonly string $url
    ) {
        $this->id = (string) Str::uuid();
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'follow_up_discussion_reply',
            'title' => 'New follow-up',
            'message' => ($this->reply->author_role === DoubtSessionFollowUp::ROLE_ADMIN ? 'An admin' : 'A user')
                .' added a follow-up.',
            'follow_up_id' => $this->thread->id,
            'reply_id' => $this->reply->id,
            'notification_key' => 'follow_up_reply_'.$this->reply->id,
            'url' => $this->url,
            'created_at' => now()->toISOString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function emailDetails(object $notifiable): array
    {
        return ['Reply' => $this->reply->question];
    }

    protected function emailActionUrl(array $data): ?string
    {
        return $data['url'] ?? $this->url;
    }
}
