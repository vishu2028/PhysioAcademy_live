<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogAdminActivity
{
    protected $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            $this->logAction($event->user, 'LOGIN', 'Admin session started');
        } elseif ($event instanceof Logout) {
            $this->logAction($event->user, 'LOGOUT', 'Admin session ended');
        }
    }

    protected function logAction($user, $action, $description)
    {
        if (!$user) return;

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'module' => 'Auth',
            'description' => $description,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}
