<?php

namespace App\Notifications;

use App\Models\TopicRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Traits\EmailNotification;

class TopicRequestStatusUpdated extends Notification
{
    use Queueable;
    use EmailNotification;

    public function __construct(public readonly TopicRequest $topicRequest)
    {
    }

    public function toDatabase(object $notifiable): array
    {
        $status = ucfirst(str_replace('_', ' ', $this->topicRequest->status));
        $hasTopicLink = filled($this->topicRequest->topic_link);

        return [
            'type' => 'topic_request_status_updated',
            'title' => $hasTopicLink
                ? 'Your requested topic is available'
                : 'Topic request updated',
            'message' => $hasTopicLink
                ? 'Your requested topic is available! Click here to view it.'
                : sprintf('"%s" is now %s.', $this->topicRequest->topic_name, $status),
            'topic_request_id' => $this->topicRequest->id,
            'topic_id' => $this->topicRequest->topic_id,
            'topic_link' => $this->topicRequest->topic_link,
            'topic_name' => $this->topicRequest->topic_name,
            'status' => $this->topicRequest->status,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
