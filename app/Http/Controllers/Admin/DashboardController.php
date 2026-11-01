<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DoubtSessionBooking;
use App\Models\ExamAid;
use App\Models\GroupWebinarRequest;
use App\Models\Media;
use App\Models\Message;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'users'      => User::count(),
            'pages'      => Page::count(),
            'subjects'   => Subject::count(),
            'academic_years' => AcademicYear::count(),
            'exam_aids' => ExamAid::count(),
            'doubt_sessions' => DoubtSessionBooking::count(),
            'group_webinars' => GroupWebinarRequest::count(),
        ];

        $topicStatusCounts = Topic::query()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as active_count,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as inactive_count',
                [true, false]
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | User Growth - Last 7 Days
        |--------------------------------------------------------------------------
        */

        $labels = [];
        $userData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $labels[] = $date->format('D');

            $userData[] = User::whereDate(
                'created_at',
                $date->toDateString()
            )->count();
        }

        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        $chartData = [
            'labels' => $labels,

            'users' => $userData,

            'distribution' => [
                'Active Topics' => (int) $topicStatusCounts->active_count,
                'Inactive Topics' => (int) $topicStatusCounts->inactive_count,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentActivity = collect();

        /*
        |--------------------------------------------------------------------------
        | Recent Users
        |--------------------------------------------------------------------------
        */

        $recentUsers = User::latest('created_at')
            ->take(3)
            ->get();

        foreach ($recentUsers as $user) {
            $recentActivity->push([
                'user'      => $user->name,
                'subtitle'  => $user->email,
                'action'    => 'User Joined',
                'timestamp' => $user->created_at,
                'time'      => $user->created_at->diffForHumans(),
                'status'    => 'Success',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Messages
        |--------------------------------------------------------------------------
        */

        $recentMessages = Message::latest('created_at')
            ->take(2)
            ->get();

        foreach ($recentMessages as $message) {
            $recentActivity->push([
                'user'      => $message->name,
                'subtitle'  => $message->email,
                'action'    => 'Sent Inquiry',
                'timestamp' => $message->created_at,
                'time'      => $message->created_at->diffForHumans(),
                'status'    => 'New',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Media
        |--------------------------------------------------------------------------
        */

        $recentMedia = Media::latest('created_at')
            ->take(2)
            ->get();

        foreach ($recentMedia as $media) {
            $recentActivity->push([
                'user'      => 'Super Admin',
                'subtitle'  => $media->file_type,
                'action'    => 'Uploaded ' . Str::limit($media->file_name, 20),
                'timestamp' => $media->created_at,
                'time'      => $media->created_at->diffForHumans(),
                'status'    => 'File',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentActivity = $recentActivity
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard', [
            'stats'          => $stats,
            'chartData'      => $chartData,
            'recentActivity' => $recentActivity,
        ]);
    }
}