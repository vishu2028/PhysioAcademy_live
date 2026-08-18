<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommunityActivity;
use App\Models\Announcement;
use App\Models\TrendingTopic;

class CommunityAndAnnouncementsController extends Controller
{
     public function index()
    {
        $activities = CommunityActivity::latest()->get();

        $announcements = Announcement::latest()->get();

        $trendingTopics = TrendingTopic::latest()->get();


        return view(
            'admin.community_and_announcements.index',
            compact(
                'activities',
                'announcements',
                'trendingTopics'
            )
        );
    }
}
