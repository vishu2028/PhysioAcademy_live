<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::ordered()->get();

        return view('admin.team-members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.team-members.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('team', 'public');
        }

        TeamMember::create($data);

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member added successfully.');
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team-members.edit', ['member' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            if ($teamMember->photo_path) {
                Storage::disk('public')->delete($teamMember->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('team', 'public');
        } elseif ($request->boolean('remove_photo') && $teamMember->photo_path) {
            Storage::disk('public')->delete($teamMember->photo_path);
            $data['photo_path'] = null;
        }

        $teamMember->update($data);

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $teamMember)
    {
        if ($teamMember->photo_path) {
            Storage::disk('public')->delete($teamMember->photo_path);
        }

        $teamMember->delete();

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
