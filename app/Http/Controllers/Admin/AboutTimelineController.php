<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutTimeline;
use Illuminate\Http\Request;

class AboutTimelineController extends Controller
{
    /**
     * Display timeline entries.
     *
     * Normally the main About page handles this,
     * but this method can still be used independently.
     */
    public function index()
    {
        $timelines = AboutTimeline::orderBy('id', 'asc')->get();

        return view('admin.about_timelines.index', compact('timelines'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.about.about_timelines.create');
    }

    /**
     * Store timeline.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        AboutTimeline::create($validated);

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About timeline added successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $timeline = AboutTimeline::findOrFail($id);

        return view('admin.about.about_timelines.edit', compact('timeline'));
    }

    /**
     * Update timeline.
     */
    public function update(Request $request, $id)
    {
        $timeline = AboutTimeline::findOrFail($id);

        $validated = $request->validate([
            'year' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $timeline->update($validated);

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About timeline updated successfully.');
    }

    /**
     * Delete timeline.
     */
    public function destroy($id)
    {
        $timeline = AboutTimeline::findOrFail($id);

        $timeline->delete();

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About timeline deleted successfully.');
    }
}