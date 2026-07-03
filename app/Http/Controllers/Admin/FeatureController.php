<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::ordered()->get();

        $sectionEnabled = Feature::query()->value('section_enabled');

        $sectionEnabled = is_null($sectionEnabled) ? true : (bool) $sectionEnabled;

        return view('admin.features.index', compact('features', 'sectionEnabled'));
    }
    public function sectionToggle(Request $request)
    {
        $sectionEnabled = $request->boolean('section_enabled');

        Feature::query()->update([
            'section_enabled' => $sectionEnabled,
        ]);

        return back()->with('success', 'Platform Features section visibility updated successfully.');
    }

    public function create()
    {
        return view('admin.features.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'order' => 'nullable|integer',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        Feature::create($data);

        return redirect()->route('admin.features.index')->with('success', 'Feature created successfully.');
    }

    public function edit(Feature $feature)
    {
        return view('admin.features.edit', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'order' => 'nullable|integer',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        $feature->update($data);

        return redirect()->route('admin.features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();
        return back()->with('success', 'Feature deleted successfully.');
    }
}
