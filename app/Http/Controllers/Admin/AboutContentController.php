<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\AboutTimeline;
use Illuminate\Http\Request;

class AboutContentController extends Controller
{
    /**
     * Display About content and timelines.
     */
    public function index()
    {
        $aboutContent = AboutContent::first();

        $timelines = AboutTimeline::orderBy('id', 'asc')->get();

        return view('admin.about.index', compact(
            'aboutContent',
            'timelines'
        ));
    }

    /**
     * Show form for creating About content.
     */
    public function create()
    {
        // Only one About content record should exist.
        $aboutContent = AboutContent::first();

        if ($aboutContent) {
            return redirect()
                ->route('admin.about_content.edit', $aboutContent->id)
                ->with('info', 'About content already exists. You can edit it.');
        }

        return view('admin.about.about_content.create');
    }

    /**
     * Store About content.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'main_title' => 'required|string|max:255',
            'main_description' => 'required|string',
            'topic_count' => 'required|integer|min:0',
            'question_count' => 'required|integer|min:0',
            'student_count' => 'required|integer|min:0',
        ]);

        // Prevent multiple About content records.
        if (AboutContent::exists()) {
            return redirect()
                ->route('admin.about_content.index')
                ->with('error', 'About content already exists. Please edit the existing content.');
        }

        AboutContent::create($validated);

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About content created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $aboutContent = AboutContent::findOrFail($id);

        return view('admin.about.about_content.edit', compact('aboutContent'));
    }

    /**
     * Update About content.
     */
    public function update(Request $request, $id)
    {
        $aboutContent = AboutContent::findOrFail($id);

        $validated = $request->validate([
            'main_title' => 'required|string|max:255',
            'main_description' => 'required|string',
            'topic_count' => 'required|integer|min:0',
            'question_count' => 'required|integer|min:0',
            'student_count' => 'required|integer|min:0',
        ]);

        $aboutContent->update($validated);

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About content updated successfully.');
    }

    /**
     * Delete About content.
     */
    public function destroy($id)
    {
        $aboutContent = AboutContent::findOrFail($id);

        $aboutContent->delete();

        return redirect()
            ->route('admin.about_content.index')
            ->with('success', 'About content deleted successfully.');
    }
}