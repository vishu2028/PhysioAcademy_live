<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => \App\Models\User::count(),
            'pages' => \App\Models\Page::count(),
            'subjects' => \App\Models\Subject::count(),
            'topics' => \App\Models\Topic::count(),
            'services' => \App\Models\Topic::count(), // Legacy mapping
            'features' => \App\Models\Feature::count(),
            'messages' => \App\Models\Message::count(),
        ];

        // User growth chart data (Last 7 days)
        $labels = [];
        $userData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $userData[] = \App\Models\User::whereDate('created_at', $date->toDateString())->count();
        }

        $chartData = [
            'labels' => $labels,
            'users' => $userData,
            'distribution' => [
                'Pages' => $stats['pages'],
                'Topics' => $stats['topics'],
                'Subjects' => $stats['subjects'],
                'Testimonials' => \App\Models\Testimonial::count(),
            ]
        ];

        $recentActivity = collect();
        
        \App\Models\User::latest()->take(3)->get()->each(function($u) use ($recentActivity) {
            $recentActivity->push([
                'user' => $u->name,
                'subtitle' => $u->email,
                'action' => 'User Joined',
                'timestamp' => $u->created_at->diffForHumans(),
                'status' => 'Success'
            ]);
        });

        \App\Models\Message::latest()->take(2)->get()->each(function($m) use ($recentActivity) {
            $recentActivity->push([
                'user' => $m->name,
                'subtitle' => $m->email,
                'action' => 'Sent Inquiry',
                'timestamp' => $m->created_at->diffForHumans(),
                'status' => 'New'
            ]);
        });

        \App\Models\Media::latest()->take(2)->get()->each(function($me) use ($recentActivity) {
            $recentActivity->push([
                'user' => 'Super Admin',
                'subtitle' => $me->file_type,
                'action' => 'Uploaded ' . Str::limit($me->file_name, 20),
                'timestamp' => $me->created_at->diffForHumans(),
                'status' => 'File'
            ]);
        });

        $recentActivity = $recentActivity->sortByDesc('timestamp')->take(8);

        return view('admin.dashboard', compact('stats', 'chartData', 'recentActivity'));
    }
}
