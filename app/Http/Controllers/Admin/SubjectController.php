<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('order')->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'status' => 'nullable',
        ]);

        try {
            $data = $request->only([
                'name',
                'code',
                'description',
                'icon',
                'order',
            ]);

            $data['slug'] = Str::slug($request->name);
            $data['status'] = $request->has('status') ? 1 : 0;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('subjects', 'public');
            }

            Subject::create($data);

            return redirect()
                ->route('admin.subjects.index')
                ->with('success', 'Subject created successfully.');
        } catch (\Exception $e) {
            \Log::error('Subject create failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create subject. Please try again.');
        }
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'status' => 'nullable',
        ]);

        try {
            $data = $request->only([
                'name',
                'code',
                'description',
                'icon',
                'order',
            ]);

            $data['slug'] = Str::slug($request->name);
            $data['status'] = $request->has('status') ? 1 : 0;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('subjects', 'public');
            }

            $subject->update($data);

            return redirect()
                ->route('admin.subjects.index')
                ->with('success', 'Subject updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Subject update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update subject. Please try again.');
        }
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }
}
