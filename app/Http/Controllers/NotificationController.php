<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read and redirect the student
     * to their doubt session history.
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

        return redirect()->route(
            'doubt-sessions.history'
        );
    }
}
