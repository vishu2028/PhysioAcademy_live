<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentTopic;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\UnitTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ParentTopicController extends Controller
{
    public function index()
    {
        $parentTopics = ParentTopic::with(['subject', 'unit', 'unitTopic'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.parent-topics.index', compact('parentTopics'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();

        $selectedSubjectId = old('subject_id');
        $selectedUnitId = old('unit_id');

        $units = $selectedSubjectId
            ? Unit::where('subject_id', $selectedSubjectId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $unitTopics = $selectedUnitId
            ? UnitTopic::where('unit_id', $selectedUnitId)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
            : collect();

        $parentTopic = new ParentTopic();

        return view('admin.parent-topics.create', compact(
            'subjects',
            'units',
            'unitTopics',
            'parentTopic',
            'selectedSubjectId',
            'selectedUnitId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',

            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],

            'unit_topic_id' => [
                'required',
                Rule::exists('unit_topics', 'id')->where(function ($query) use ($request) {
                    return $query->where('unit_id', $request->unit_id);
                }),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parent_topics', 'title')
                    ->where(function ($query) use ($request) {
                        return $query->where('unit_topic_id', $request->unit_topic_id);
                    }),
            ],

            'sort_order' => 'nullable|integer|min:0',
            'status' => 'sometimes|boolean',
        ]);

        ParentTopic::create([
            'subject_id' => $request->subject_id,
            'unit_id' => $request->unit_id,
            'unit_topic_id' => $request->unit_topic_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title . '-' . time()),
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status'),
        ]);

        return redirect()
            ->route('admin.parent-topics.index')
            ->with('success', 'Parent topic created successfully.');
    }

    public function edit(ParentTopic $parentTopic)
    {
        $subjects = Subject::active()->orderBy('name')->get();

        $selectedSubjectId = old('subject_id', $parentTopic->subject_id);
        $selectedUnitId = old('unit_id', $parentTopic->unit_id);

        $units = $selectedSubjectId
            ? Unit::where('subject_id', $selectedSubjectId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $unitTopics = $selectedUnitId
            ? UnitTopic::where('unit_id', $selectedUnitId)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
            : collect();

        return view('admin.parent-topics.edit', compact(
            'parentTopic',
            'subjects',
            'units',
            'unitTopics',
            'selectedSubjectId',
            'selectedUnitId'
        ));
    }

    public function update(Request $request, ParentTopic $parentTopic)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',

            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],

            'unit_topic_id' => [
                'required',
                Rule::exists('unit_topics', 'id')->where(function ($query) use ($request) {
                    return $query->where('unit_id', $request->unit_id);
                }),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parent_topics', 'title')
                    ->where(function ($query) use ($request) {
                        return $query->where('unit_topic_id', $request->unit_topic_id);
                    })
                    ->ignore($parentTopic->id),
            ],

            'sort_order' => 'nullable|integer|min:0',
            'status' => 'sometimes|boolean',
        ]);

        $parentTopic->update([
            'subject_id' => $request->subject_id,
            'unit_id' => $request->unit_id,
            'unit_topic_id' => $request->unit_topic_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title . '-' . $parentTopic->id),
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status'),
        ]);

        return redirect()
            ->route('admin.parent-topics.index')
            ->with('success', 'Parent topic updated successfully.');
    }

    public function destroy(ParentTopic $parentTopic)
    {
        if ($parentTopic->lmsTopics()->exists()) {
            return redirect()
                ->route('admin.parent-topics.index')
                ->with('error', 'This parent topic is used in Topics & LMS. Please remove or move related LMS topics first.');
        }

        $parentTopic->delete();

        return redirect()
            ->route('admin.parent-topics.index')
            ->with('success', 'Parent topic deleted successfully.');
    }

    public function byTopic(UnitTopic $unitTopic)
    {
        return ParentTopic::where('unit_topic_id', $unitTopic->id)
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}
