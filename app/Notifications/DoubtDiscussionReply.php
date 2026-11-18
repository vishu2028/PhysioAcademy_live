<?php

namespace App\Notifications;

use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Traits\EmailNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DoubtDiscussionReply extends Notification
{
    use EmailNotification;

    public function __construct(
        private readonly Doubt $doubt,
        private readonly DoubtMessage $reply
    ) {
        $this->id = (string) Str::uuid();
    }

    public function toDatabase(object $notifiable): array
    {
        $author = $this->reply->sender?->name
            ?? ($this->reply->sender_role === DoubtMessage::ROLE_ADMIN ? 'An admin' : 'A student');

        return [
            'type' => 'doubt_discussion_reply',
            'title' => 'New follow-up on a doubt you follow',
            'message' => $author.' added a follow-up to '.($this->doubt->topic ?: 'a doubt').'.',
            'doubt_id' => $this->doubt->id,
            'reply_id' => $this->reply->id,
            'notification_key' => 'doubt_discussion_reply_'.$this->reply->id,
            'url' => route('doubts.show', $this->doubt).'#doubt-message-'.$this->reply->id,
            'created_at' => now()->toISOString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function emailDetails(object $notifiable): array
    {
        return ['Reply' => $this->reply->message];
    }

    protected function emailActionUrl(array $data): ?string
    {
        return $data['url'] ?? route('doubts.show', $this->doubt).'#doubt-message-'.$this->reply->id;
    }
}
