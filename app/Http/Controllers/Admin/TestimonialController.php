<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::ordered()->get();

        $sectionEnabled = Testimonial::query()->value('section_enabled');
        $sectionEnabled = is_null($sectionEnabled) ? true : (bool) $sectionEnabled;

        return view('admin.testimonials.index', compact('testimonials', 'sectionEnabled'));
    }
    public function sectionToggle(Request $request)
    {
        $sectionEnabled = $request->boolean('section_enabled');

        Testimonial::query()->update([
            'section_enabled' => $sectionEnabled,
        ]);

        return back()->with('success', 'Testimonials section visibility updated successfully.');
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_designation' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'client_image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'status' => 'boolean',
        ]);

        $data = $request->only(['client_name', 'client_designation', 'content', 'rating', 'order']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('client_image')) {
            $data['client_image'] = $request->file('client_image')->store('testimonials', 'public');
        }

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_designation' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'client_image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'status' => 'boolean',
        ]);

        $data = $request->only(['client_name', 'client_designation', 'content', 'rating', 'order']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('client_image')) {
            if ($testimonial->client_image) {
                Storage::disk('public')->delete($testimonial->client_image);
            }
            $data['client_image'] = $request->file('client_image')->store('testimonials', 'public');
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->client_image) {
            Storage::disk('public')->delete($testimonial->client_image);
        }
        $testimonial->delete();
        return back()->with('success', 'Testimonial deleted successfully.');
    }
}
