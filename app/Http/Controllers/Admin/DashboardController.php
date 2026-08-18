<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Feature;
use App\Models\Message;
use App\Models\Testimonial;
use App\Models\Media;
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
            'topics'     => Topic::count(),
            'services'   => Topic::count(), // Change to Service::count() if Service model exists
            'features'   => Feature::count(),
            'messages'   => Message::count(),
        ];

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
                'Pages'        => $stats['pages'],
                'Topics'       => $stats['topics'],
                'Subjects'     => $stats['subjects'],
                'Testimonials' => Testimonial::count(),
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