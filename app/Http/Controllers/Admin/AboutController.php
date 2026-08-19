<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutContent;
use App\Models\AboutTimeline;

class AboutController extends Controller
{
    public function index()
    {
        // There should only be one About Content record
        $aboutContent = AboutContent::first();

        // Multiple timeline records
        $timelines = AboutTimeline::orderBy('id', 'asc')->get();

        return view('admin.about.index', compact(
            'aboutContent',
            'timelines'
        ));
    }
}
