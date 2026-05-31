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
        
        if ($request->filled('page_id')) {
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
            'enabled' => 'nullable'
        ]);

        $data['slug'] = $request->slug ?: Str::slug($request->name);
        
        // Handle form-based content_fields (takes precedence over raw JSON content)
        if ($request->has('config')) {
            $config = $request->input('config');
            
            // Handle images specifically if any were uploaded
            if ($request->hasFile('config_images')) {
                foreach($request->file('config_images') as $key => $file) {
                    $path = $file->store('pages/sections', 'public');
                    $config['images'][$key] = '/storage/' . $path;
                }
            }
            
            $data['content'] = $config;
        } 
        // Fallback to raw JSON input if provided
        elseif ($request->filled('content')) {
            $inputContent = $request->input('content');
            $decoded = json_decode($inputContent, true);
            $data['content'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $inputContent;
        } else {
            $data['content'] = null;
        }

        $data['enabled'] = $request->has('enabled');

        \Illuminate\Support\Facades\Log::info('Creating Section', ['data' => $data]);
        PageSection::create($data);

        return redirect()->route('admin.page-sections.index')->with('success', 'Section created successfully.');
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
            'enabled' => 'nullable'
        ]);

        $data['slug'] = $request->slug ?: Str::slug($request->name);
        
        // Handle form-based config fields
        if ($request->has('config')) {
            $config = $request->input('config');
            
            // Handle images specifically
            if ($request->hasFile('config_images')) {
                foreach($request->file('config_images') as $field => $file) {
                    $path = $file->store('pages/sections', 'public');
                    $config[$field] = '/storage/' . $path;
                }
            }

            // Keep existing images if not re-uploaded
            if (isset($section->content['images'])) {
                $config['images'] = array_merge($section->content['images'], $config['images'] ?? []);
            }
            
            $data['content'] = array_merge($section->content ?? [], $config);
        }
        // Fallback to raw JSON
        elseif ($request->filled('content')) {
            $inputContent = $request->input('content');
            $decoded = json_decode($inputContent, true);
            $data['content'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $inputContent;
        } else {
            $data['content'] = $section->content;
        }

        $data['enabled'] = $request->has('enabled');

        \Illuminate\Support\Facades\Log::info('Updating Section', ['id' => $section->id, 'data' => $data]);
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
