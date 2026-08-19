<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\Announcement;
=======
>>>>>>> 278c271ed5712adc2f252bf549ffb28180cb23af

class AnnouncementController extends Controller
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
        return view('admin.community_and_announcements.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
<<<<<<< HEAD
        $request->validate([
        'title' => 'required|string|max:255',
        // 'icon'  => 'required|string|max:100',
        'date'  => 'required|date',
    ]);

    Announcement::create([
        'title' => $request->title,
        // 'icon'  => $request->icon,
        'date'  => $request->date,
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Announcement created successfully.');
=======
        //
>>>>>>> 278c271ed5712adc2f252bf549ffb28180cb23af
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
        $announcement = Announcement::findOrFail($id);

        return view('admin.community_and_announcements.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
          $request->validate([
        'title' => 'required|string|max:255',
        // 'icon'  => 'required|string|max:100',
        'date'  => 'required|date',
    ]);

    $announcement = Announcement::findOrFail($id);

    $announcement->update([
        'title' => $request->title,
        // 'icon'  => $request->icon,
        'date'  => $request->date,
    ]);

    return redirect()
        ->route('admin.community_and_announcements.index')
        ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $announcement = Announcement::findOrFail($id);

        $announcement->delete();

        return redirect()
            ->route('admin.community_and_announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
