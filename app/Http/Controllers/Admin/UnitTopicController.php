<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\UnitTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UnitTopicController extends Controller
{
    public function index()
    {
        $unitTopics = UnitTopic::with(['subject', 'unit'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);

        return view('admin.unit-topics.index', compact('unitTopics'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();

        $units = old('subject_id')
            ? Unit::where('subject_id', old('subject_id'))->orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('admin.unit-topics.create', compact('subjects', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unit_topics', 'title')->where(function ($query) use ($request) {
                    return $query->where('unit_id', $request->unit_id);
                }),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']);

        UnitTopic::create($data);

        return redirect()
            ->route('admin.unit-topics.index')
            ->with('success', 'Topic created successfully.');
    }

    public function edit(UnitTopic $unitTopic)
    {
        $subjects = Subject::active()->orderBy('name')->get();

        $units = Unit::where('subject_id', old('subject_id', $unitTopic->subject_id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.unit-topics.edit', compact('unitTopic', 'subjects', 'units'));
    }

    public function update(Request $request, UnitTopic $unitTopic)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unit_topics', 'title')
                    ->where(function ($query) use ($request) {
                        return $query->where('unit_id', $request->unit_id);
                    })
                    ->ignore($unitTopic->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']);

        $unitTopic->update($data);

        return redirect()
            ->route('admin.unit-topics.index')
            ->with('success', 'Topic updated successfully.');
    }

    public function destroy(UnitTopic $unitTopic)
    {
        if ($unitTopic->lmsTopics()->exists()) {
            return back()->with('error', 'This topic is used in Topics & LMS. Please remove or move LMS records first.');
        }

        $unitTopic->delete();

        return back()->with('success', 'Topic deleted successfully.');
    }

    public function byUnit(Unit $unit)
    {
        $topics = $unit->unitTopics()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json($topics);
    }
}
