<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSectionItem;
use App\Models\PageSection;

class PageSectionItemController extends Controller
{
    public function create(Request $request)
    {
        $sectionId = $request->input('section_id');
        $section = PageSection::findOrFail($sectionId);
        return view('admin.page_section_items.create', compact('section'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id' => 'required|exists:page_sections,id',
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'meta' => 'nullable',
            'order' => 'nullable|integer',
            'enabled' => 'nullable'
        ]);

        $metaInput = $request->input('meta');
        if ($metaInput) {
            $decoded = json_decode($metaInput, true);
            $data['meta'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $metaInput;
        } else {
            $data['meta'] = null;
        }
        $data['enabled'] = $request->has('enabled');

        PageSectionItem::create($data);

        return redirect()->route('admin.page-sections.edit', $data['section_id'])->with('success', 'Item added.');
    }

    public function edit(PageSectionItem $page_section_item)
    {
        $item = $page_section_item;
        $section = $item->section;
        return view('admin.page_section_items.edit', compact('item', 'section'));
    }

    public function update(Request $request, PageSectionItem $page_section_item)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'meta' => 'nullable',
            'order' => 'nullable|integer',
            'enabled' => 'nullable|boolean'
        ]);

        $metaInput = $request->input('meta');
        if ($metaInput) {
            $decoded = json_decode($metaInput, true);
            $data['meta'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $metaInput;
        } else {
            $data['meta'] = null;
        }
        $data['enabled'] = $request->has('enabled');

        $page_section_item->update($data);

        return redirect()->route('admin.page-sections.edit', $page_section_item->section_id)->with('success', 'Item updated.');
    }

    public function destroy(PageSectionItem $page_section_item)
    {
        $page_section_item->delete();
        return back()->with('success', 'Item deleted.');
    }
}
