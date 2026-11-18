<?php

namespace App\Services;

use App\Models\DoubtSessionBooking;
use App\Models\DoubtSessionFollowUp;
use App\Models\User;
use App\Notifications\FollowUpDiscussionActivity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FollowUpDiscussionService
{
    public function createThread(
        User $author,
        string $question,
        string $submissionKey,
        ?DoubtSessionBooking $booking = null
    ): DoubtSessionFollowUp {
        $post = DoubtSessionFollowUp::firstOrCreate(
            [
                'user_id' => $author->id,
                'submission_key' => $submissionKey,
            ],
            [
                'doubt_session_booking_id' => $booking?->id,
                'question' => trim($question),
                'author_role' => $this->authorRole($author),
                'status' => DoubtSessionFollowUp::STATUS_PENDING,
            ]
        );

        if ((int) $post->thread_id !== (int) $post->id) {
            $post->forceFill(['thread_id' => $post->id])->save();
        }

        if ($booking === null && Schema::hasTable('discussion_follows')) {
            $post->discussionFollows()->firstOrCreate(['user_id' => $author->id]);
        }

        return $post;
    }

    public function reply(
        User $author,
        int $threadId,
        int $parentId,
        string $question,
        string $submissionKey,
        bool $asAdmin = false,
        bool $allowBookingOwner = false
    ): DoubtSessionFollowUp {
        $root = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->where('id', $threadId)
            ->where('thread_id', $threadId)
            ->firstOrFail();

        if (
            ! $asAdmin
            && (
                $root->is_hidden
                || ($root->doubt_session_booking_id !== null
                    && (! $allowBookingOwner || (int) $root->user_id !== (int) $author->id))
                || ($root->doubt_session_booking_id === null && $allowBookingOwner)
            )
        ) {
            abort(404);
        }

        $parent = DoubtSessionFollowUp::query()
            ->where('thread_id', $threadId)
            ->whereKey($parentId)
            ->firstOrFail();

        if (! $asAdmin && $parent->is_hidden) {
            throw ValidationException::withMessages([
                'parent_id' => 'Replies cannot be added to hidden content.',
            ]);
        }

        $reply = DoubtSessionFollowUp::firstOrCreate(
            [
                'user_id' => $author->id,
                'submission_key' => $submissionKey,
            ],
            [
                'doubt_session_booking_id' => $root->doubt_session_booking_id,
                'parent_id' => $parent->id,
                'thread_id' => $threadId,
                'author_role' => $asAdmin ? DoubtSessionFollowUp::ROLE_ADMIN : $this->authorRole($author),
                'question' => trim($question),
                'status' => DoubtSessionFollowUp::STATUS_PENDING,
            ]
        );

        if ($reply->wasRecentlyCreated) {
            $this->notifyParticipants($root, $parent, $reply, $author, $asAdmin);
        }

        return $reply;
    }

    private function notifyParticipants(
        DoubtSessionFollowUp $root,
        DoubtSessionFollowUp $parent,
        DoubtSessionFollowUp $reply,
        User $author,
        bool $asAdmin
    ): void {
        if ($root->doubt_session_booking_id === null) {
            $participantIds = Schema::hasTable('discussion_follows')
                ? $root->discussionFollows()->pluck('user_id')
                : collect();
        } else {
            $participantIds = collect([$root->user_id, $parent->user_id]);

            if ($asAdmin) {
                $participantIds = $participantIds->merge(
                    DoubtSessionFollowUp::query()
                        ->where('thread_id', $root->id)
                        ->whereNotNull('user_id')
                        ->pluck('user_id')
                );
            }
        }

        $notificationKey = 'follow_up_reply_'.$reply->id;
        $url = $root->doubt_session_booking_id
            ? route('doubt-sessions.history').'#session-booking-'.$root->doubt_session_booking_id
            : route('doubts.mine', ['discussion_id' => $root->id]).'#community-thread-'.$root->id;

        User::query()
            ->whereIn('id', $participantIds->filter()->unique()->reject(
                fn ($userId): bool => (int) $userId === (int) $author->id
            ))
            ->get()
            ->each(function (User $recipient) use ($notificationKey, $root, $reply, $url): void {
                $alreadyNotified = $recipient->notifications()
                    ->where('data->notification_key', $notificationKey)
                    ->exists();

                if (! $alreadyNotified) {
                    $recipient->notify(new FollowUpDiscussionActivity($root, $reply, $url));
                }
            });
    }

    private function authorRole(User $author): string
    {
        return $author->hasAnyRole(['admin', 'super_admin'])
            ? DoubtSessionFollowUp::ROLE_ADMIN
            : DoubtSessionFollowUp::ROLE_USER;
    }
}
