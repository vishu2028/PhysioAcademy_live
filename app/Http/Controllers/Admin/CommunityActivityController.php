<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommunityActivity;

class CommunityActivityController extends Controller
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
        return view('admin.community_and_announcements.community.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'title'   => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'time'    => 'required|string|max:100',
    ]);

    CommunityActivity::create([
        'title'   => $request->title,
        'subject' => $request->subject,
        'time'    => $request->time,
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Recent activity created successfully.');
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
        $communityActivity = CommunityActivity::findOrFail($id);

        return view('admin.community_and_announcements.community.edit', compact('communityActivity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
        'title'   => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'time'    => 'required|string|max:100',
    ]);

    $activity = CommunityActivity::findOrFail($id);

    $activity->update([
        'title'   => $request->title,
        'subject' => $request->subject,
        'time'    => $request->time,
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Recent activity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activity = CommunityActivity::findOrFail($id);

        $activity->delete();

        return redirect()
            ->route('admin.community_and_announcements.index')
            ->with('success', 'Recent activity deleted successfully.');
    }
}
