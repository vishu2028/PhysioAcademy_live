<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrendingTopic;


class TrendingTopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.community_and_announcements.trending_topic.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'title'      => 'required|string|max:255',
    ]);

    TrendingTopic::create([
        'title'      => $request->title,        
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Trending topic created successfully.');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $trendingTopic = TrendingTopic::findOrFail($id);

        return view('admin.community_and_announcements.trending_topic.edit', compact('trendingTopic'));
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
        'title'      => 'required|string|max:255',        
    ]);

    $trending = TrendingTopic::findOrFail($id);

    $trending->update([
        'title'      => $request->title,        
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Trending topic updated successfully.');
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $trending = TrendingTopic::findOrFail($id);

        $trending->delete();

        return redirect()
            ->route('admin.community_and_announcements.index')
            ->with('success', 'Trending topic deleted successfully.');
        //
    }
}
