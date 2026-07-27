<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

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
}
