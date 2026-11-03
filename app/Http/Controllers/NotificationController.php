<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read and open its relevant destination.
     */
    public function open(
        Request $request,
        string $notification
    ) {
        $databaseNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($databaseNotification->unread()) {
            $databaseNotification->markAsRead();
        }

        $doubtId = $databaseNotification->data['doubt_id'] ?? null;
        $topicRequestId = $databaseNotification->data['topic_request_id'] ?? null;
        $topicId = $databaseNotification->data['topic_id'] ?? null;
        $topicLink = $databaseNotification->data['topic_link'] ?? null;
        $bookingId = $databaseNotification->data['booking_id'] ?? null;
        $groupWebinarRequestId = $databaseNotification->data['group_webinar_request_id'] ?? null;

        if (($databaseNotification->data['type'] ?? null) === 'group_webinar' && $groupWebinarRequestId) {
            return redirect(url(route('webinars.my')).'#group-booking-'.$groupWebinarRequestId);
        }

        if (
            ($databaseNotification->data['type'] ?? null) === 'doubt_session_follow_up_answered'
            && $bookingId
        ) {
            return redirect(url(route('doubt-sessions.history')) . '#session-booking-' . $bookingId);
        }

        if ($doubtId) {
            return redirect(url(route('doubts.mine')) . '#doubt-' . $doubtId);
        }

        if ($topicRequestId) {
            if (
                is_string($topicLink)
                && filter_var($topicLink, FILTER_VALIDATE_URL)
                && in_array(
                    strtolower((string) parse_url($topicLink, PHP_URL_SCHEME)),
                    ['http', 'https'],
                    true
                )
            ) {
                return redirect()->away($topicLink);
            }

            $destination = route('topic-requests.mine') . '#topic-request-' . $topicRequestId;

            if ($topicId) {
                $topic = \App\Models\Topic::find($topicId);
                $destination = $topic
                    ? route('topics.show', ['slug' => $topic->slug])
                    : $destination;
            }

            return redirect($destination);
        }

        return redirect()->route('doubt-sessions.history');
    }

    public function poll(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(6)
            ->get()
            ->map(static function ($notification): array {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'created_at' => $notification->created_at?->diffForHumans(),
                    'unread' => $notification->unread(),
                    'url' => route('notifications.open', $notification->id),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
