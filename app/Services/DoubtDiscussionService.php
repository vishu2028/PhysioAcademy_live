<?php

namespace App\Services;

use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Models\User;
use App\Notifications\DoubtDiscussionReply;
use Illuminate\Support\Facades\Schema;

class DoubtDiscussionService
{
    public function follow(Doubt $doubt, User $user): void
    {
        $doubt->discussionFollows()->firstOrCreate(['user_id' => $user->id]);
    }

    public function unfollow(Doubt $doubt, User $user): void
    {
        $doubt->discussionFollows()->where('user_id', $user->id)->delete();
    }

    public function notifyFollowers(Doubt $doubt, DoubtMessage $reply, array $excludeUserIds = []): void
    {
        if (! Schema::hasTable('discussion_follows')) {
            return;
        }

        $notificationKey = 'doubt_discussion_reply_'.$reply->id;
        $excludedUserIds = collect($excludeUserIds)
            ->map(static fn ($userId): int => (int) $userId)
            ->push((int) $reply->sender_id)
            ->unique();

        $doubt->discussionFollows()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->reject(fn (User $user): bool => $excludedUserIds->contains((int) $user->id))
            ->each(function (User $recipient) use ($doubt, $reply, $notificationKey): void {
                $alreadyNotified = $recipient->notifications()
                    ->where('data->notification_key', $notificationKey)
                    ->exists();

                if (! $alreadyNotified) {
                    $recipient->notify(new DoubtDiscussionReply($doubt, $reply));
                }
            });
    }
}
