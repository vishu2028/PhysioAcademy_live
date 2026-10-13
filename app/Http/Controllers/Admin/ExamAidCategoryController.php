<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAidCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamAidCategoryController extends Controller
{
    public function index()
    {
        $categories = ExamAidCategory::withCount(['materials', 'learningMaterials'])
            ->ordered()
            ->get();

        return view('admin.exam-aid-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.exam-aid-categories.create');
    }

    public function store(Request $request)
    {
        ExamAidCategory::create($this->validated($request));

        return redirect()
            ->route('admin.exam-aid-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ExamAidCategory $examAidCategory)
    {
        return view('admin.exam-aid-categories.edit', ['category' => $examAidCategory]);
    }

    public function update(Request $request, ExamAidCategory $examAidCategory)
    {
        $examAidCategory->update($this->validated($request, $examAidCategory));

        return redirect()
            ->route('admin.exam-aid-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ExamAidCategory $examAidCategory)
    {
        $examAidCategory->delete();

        return redirect()
            ->route('admin.exam-aid-categories.index')
            ->with('success', 'Category deleted. Its materials are now uncategorized.');
    }

    private function validated(Request $request, ?ExamAidCategory $category = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name', '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exam_aid_categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
