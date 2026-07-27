<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Return authenticated user's notifications.
     */
    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $validated = $request->validate([
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'all',
                    'read',
                    'unread',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $status = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 15);

        /*
         * Sirf authenticated user ki notifications.
         */
        $notifications = $request->user()
            ->notifications()

            ->when(
                $status === 'read',
                function ($query) {
                    $query->whereNotNull('read_at');
                }
            )

            ->when(
                $status === 'unread',
                function ($query) {
                    $query->whereNull('read_at');
                }
            )

            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        /*
         * Notification summary.
         */
        $totalCount = $request->user()
            ->notifications()
            ->count();

        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return NotificationResource::collection(
            $notifications
        )->additional([
            'summary' => [
                'total' => $totalCount,
                'read' => $totalCount - $unreadCount,
                'unread' => $unreadCount,
            ],
        ]);
    }
    /**
     * Mark all unread notifications of the
     * authenticated user as read.
     */
    public function markAllAsRead(
        Request $request
    ): JsonResponse {
        /*
         * Sirf logged-in user ki unread notifications
         * update hongi.
         */
        $updatedCount = $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json([
            'message' => $updatedCount > 0
                ? 'All notifications marked as read successfully.'
                : 'There are no unread notifications.',

            'data' => [
                'updated_count' => $updatedCount,
            ],

            'summary' => [
                'unread' => $unreadCount,
            ],
        ]);
    }
    public function markAsRead(
        Request $request,
        string $id
    ): JsonResponse {

        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $wasUnread = $notification->unread();

        if ($wasUnread) {
            $notification->markAsRead();
            $notification->refresh();
        }

        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json([
            'message' => $wasUnread
                ? 'Notification marked as read successfully.'
                : 'Notification is already marked as read.',

            'data' => (
            new NotificationResource($notification)
            )->resolve($request),

            'summary' => [
                'unread' => $unreadCount,
            ],
        ]);
    }
}
