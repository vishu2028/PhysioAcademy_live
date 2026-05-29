<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    public function index()
    {
        $heroes = HeroSection::latest()->get();
        return view('admin.hero.index', compact('heroes'));
    }

    public function create()
    {
        return view('admin.hero.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'status' => 'boolean',
        ]);

        $data = $request->only(['title', 'subtitle', 'button_text', 'button_url']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('hero', 'public');
        }

        HeroSection::create($data);

        return redirect()->route('admin.hero.index')->with('success', 'Hero section created successfully.');
    }

    public function edit(HeroSection $hero)
    {
        return view('admin.hero.edit', compact('hero'));
    }

    public function update(Request $request, HeroSection $hero)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'status' => 'boolean',
        ]);

        $data = $request->only(['title', 'subtitle', 'button_text', 'button_url']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            if ($hero->image_path) {
                Storage::disk('public')->delete($hero->image_path);
            }
            $data['image_path'] = $request->file('image')->store('hero', 'public');
        }

        $hero->update($data);

        return redirect()->route('admin.hero.index')->with('success', 'Hero section updated successfully.');
    }

    public function destroy(HeroSection $hero)
    {
        if ($hero->image_path) {
            Storage::disk('public')->delete($hero->image_path);
        }
        $hero->delete();
        return back()->with('success', 'Hero section deleted successfully.');
    }
}
