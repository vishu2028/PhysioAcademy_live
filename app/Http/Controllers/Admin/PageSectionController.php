<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSection;
use App\Models\Page;
use Illuminate\Support\Str;

class PageSectionController extends Controller
{
    public function index(Request $request)
    {
        $query = PageSection::with('page')->orderBy('order');
        
        if ($request->has('page_id')) {
            $query->where('page_id', $request->page_id);
        }

        $sections = $query->get();
        return view('admin.page_sections.index', compact('sections'));
    }

    public function create()
    {
        $pages = Page::active()->get();
        return view('admin.page_sections.create', compact('pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'page_id' => 'nullable|exists:pages,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:page_sections,slug',
            'type' => 'nullable|string|max:100',
            'content' => 'nullable',
            'order' => 'nullable|integer',
            'enabled' => 'nullable|boolean'
        ]);

        $data['slug'] = $request->slug ?: Str::slug($request->name);
        
        if ($request->filled('content')) {
            $decoded = json_decode($request->input('content'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['content'] = $decoded;
            } else {
                return back()->withInput()->with('error', 'Invalid JSON content format.');
            }
        } else {
            $data['content'] = null;
        }

        $data['enabled'] = $request->has('enabled');

        PageSection::create($data);

        return redirect()->route('admin.page-sections.index')->with('success', 'Section created.');
    }

    public function edit(PageSection $section)
    {
        $pages = Page::active()->get();
        return view('admin.page_sections.edit', compact('section', 'pages'));
    }

    public function update(Request $request, PageSection $section)
    {
        $data = $request->validate([
            'page_id' => 'nullable|exists:pages,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:page_sections,slug,' . $section->id,
            'type' => 'nullable|string|max:100',
            'content' => 'nullable',
            'order' => 'nullable|integer',
            'enabled' => 'nullable|boolean'
        ]);

        $data['slug'] = $request->slug ?: Str::slug($request->name);
        
        if ($request->filled('content')) {
            $decoded = json_decode($request->input('content'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['content'] = $decoded;
            } else {
                return back()->withInput()->with('error', 'Invalid JSON content format.');
            }
        } else {
            $data['content'] = null;
        }

        $data['enabled'] = $request->has('enabled');

        $section->update($data);

        return redirect()->route('admin.page-sections.index', ['page_id' => $section->page_id])
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(PageSection $section)
    {
        $section->delete();
        return back()->with('success', 'Section deleted.');
    }
}
