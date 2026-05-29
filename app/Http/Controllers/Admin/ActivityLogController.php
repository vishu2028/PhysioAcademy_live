<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Action Type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by Module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();
        
        $users = User::role('admin')->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return view('admin.activity_logs.index', compact('logs', 'users', 'actions', 'modules'));
    }
}
