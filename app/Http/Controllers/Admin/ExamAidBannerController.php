<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAidBanner;
use Illuminate\Http\Request;

class ExamAidBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = ExamAidBanner::latest()->get();

        return view('admin.exam-aid-banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.exam-aid-banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'main_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'title_1' => 'nullable|string|max:255',
            'title_2' => 'nullable|string|max:255',
            'heading_1' => 'nullable|string|max:255',
            'percentage_value' => 'required|integer|min:0|max:100',
        ]);

        $banner = ExamAidBanner::first();

        if ($banner) {
            $banner->update([
                'main_title' => $request->main_title,
                'description' => $request->description,
                'title_1' => $request->title_1,
                'title_2' => $request->title_2,
                'heading_1' => $request->heading_1,
                'percentage_value' => $request->percentage_value,
            ]);
        } else {
            ExamAidBanner::create([
                'main_title' => $request->main_title,
                'description' => $request->description,
                'title_1' => $request->title_1,
                'title_2' => $request->title_2,
                'heading_1' => $request->heading_1,
                'percentage_value' => $request->percentage_value,
            ]);
        }

        return redirect()
            ->route('admin.exam-aid-banner.index')
            ->with('success', 'Exam Aid Banner saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = ExamAidBanner::findOrFail($id);

        return view('admin.exam-aid-banner.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $banner = ExamAidBanner::findOrFail($id);

        return view('admin.exam-aid-banner.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $banner = ExamAidBanner::findOrFail($id);

        $request->validate([
            'main_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'title_1' => 'nullable|string|max:255',
            'title_2' => 'nullable|string|max:255',
            'heading_1' => 'nullable|string|max:255',
            'percentage_value' => 'required|integer|min:0|max:100',
        ]);

        $banner->update([
            'main_title' => $request->main_title,
            'description' => $request->description,
            'title_1' => $request->title_1,
            'title_2' => $request->title_2,
            'heading_1' => $request->heading_1,
            'percentage_value' => $request->percentage_value,
        ]);

        return redirect()
            ->route('admin.exam-aid-banner.index')
            ->with('success', 'Exam Aid Banner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = ExamAidBanner::findOrFail($id);

        $banner->delete();

        return redirect()
            ->route('admin.exam-aid-banner.index')
            ->with('success', 'Exam Aid Banner deleted successfully.');
    }
}