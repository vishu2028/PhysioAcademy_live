<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAidCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamAidCategoryController extends Controller
{
    /**
     * Create a category from the modal on the Exam Aid form.
     */
    public function store(Request $request): JsonResponse
    {
        // Build the slug first so uniqueness is validated on the final value.
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name', '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:exam_aid_categories,slug'],
            'description' => ['nullable', 'string'],
        ]);

        $category = ExamAidCategory::create($validated + [
            'sort_order' => (int) ExamAidCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'url' => $category->public_url,
        ], 201);
    }
}
