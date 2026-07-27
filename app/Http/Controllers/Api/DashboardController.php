<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $profileFields = [
            $user->name,
            $user->email,
            $user->email_verified_at,
        ];

        $completedFields = collect($profileFields)
            ->filter(fn ($value) => filled($value))
            ->count();

        $profileStrength = (int) round(
            ($completedFields / count($profileFields)) * 100
        );

        $recentNotifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title']
                            ?? 'Notification',
                    'message' => $notification->data['message']
                            ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at
                        ?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'summary' => [
                'unread_notifications_count' => $user
                    ->unreadNotifications()
                    ->count(),

                'messages_count' => Message::where(
                    'email',
                    $user->email
                )->count(),

                'topics_count' => Topic::count(),
            ],

            'profile' => [
                'completion_percentage' => $profileStrength,
                'account_status' => $user->email_verified_at
                    ? 'Verified'
                    : 'Pending',
            ],

            'recent_notifications' => $recentNotifications,
        ]);
    }
}
