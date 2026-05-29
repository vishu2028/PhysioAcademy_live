<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'summary' => [
                'notifications_count' => $user->unreadNotifications()->count(),
                'messages_count' => \App\Models\Message::where('email', $user->email)->count(),
                'users_count' => \App\Models\Topic::count(), // Using available topics count
                'recent_activity' => [], 
            ],
            'widgets' => [
                [
                    'title' => 'Profile Strength',
                    'value' => '75%',
                    'type' => 'progress',
                ],
                [
                    'title' => 'Membership',
                    'value' => 'Premium',
                    'type' => 'badge',
                    'status' => 'info'
                ],
                [
                    'title' => 'Account Status',
                    'value' => $user->email_verified_at ? 'Verified' : 'Pending',
                    'type' => 'badge',
                    'status' => $user->email_verified_at ? 'success' : 'warning'
                ]
            ],
            'charts' => [
                'activity' => [
                    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'data' => [2, 5, 3, 8, 4, 10, 7]
                ]
            ]
        ]);
    }
}
