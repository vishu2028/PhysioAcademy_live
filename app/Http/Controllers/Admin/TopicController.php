<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\ParentTopic;
use App\Models\UnitTopic;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::with(['subject', 'unit', 'academicYear', 'semester', 'materials'])
            ->orderBy('order')
            ->get();

        return view('admin.topics.index', compact('topics'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();

        $years = AcademicYear::active()
            ->with('semesters')
            ->orderBy('order')
            ->get();

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

        $topics = Topic::active()
            ->orderBy('title')
            ->get();
        $selectedUnitTopicId = old('unit_topic_id');

        $parentTopics = $selectedUnitTopicId
            ? ParentTopic::where('unit_topic_id', $selectedUnitTopicId)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
            : collect();

        return view('admin.topics.create', compact(
            'subjects',
            'years',
            'topics',
            'units',
            'unitTopics',
            'parentTopics',
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
            'parent_topic_id' => [
                'nullable',
                Rule::exists('parent_topics', 'id')->where(function ($query) use ($request) {
                    return $query->where('unit_topic_id', $request->unit_topic_id);
                }),
            ],

            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'parent_id' => 'nullable|exists:topics,id',
            'module_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'status' => 'sometimes|boolean',
            'is_protected' => 'sometimes|boolean',

            'materials' => 'nullable|array',
            'materials.*.title' => 'required|string|max:255',
            'materials.*.type' => 'required|in:pdf,video,link,note',
            'materials.*.content' => 'nullable|string',
            'materials.*.url' => 'nullable|url',
            'materials.*.file' => 'nullable|file|max:10240',
        ]);

        $unitTopic = UnitTopic::findOrFail($request->unit_topic_id);

        $data = $request->except('materials', 'unit_id');

        $data['title'] = $unitTopic->title;
        $data['slug'] = Str::slug($unitTopic->title . '-' . time());
        $data['status'] = $request->has('status');
        $data['is_protected'] = $request->has('is_protected');

        $topic = Topic::create($data);

        if ($request->has('materials')) {
            $orderIndex = 0;

            foreach ($request->materials as $materialData) {
                $material = new LearningMaterial([
                    'title' => $materialData['title'],
                    'type' => $materialData['type'],
                    'content' => $materialData['content'] ?? null,
                    'url' => $materialData['url'] ?? null,
                    'order' => $orderIndex,
                ]);

                if (isset($materialData['file']) && $materialData['file']) {
                    $material->file_path = $materialData['file']->store('materials', 'public');
                }

                $topic->materials()->save($material);

                $orderIndex++;
            }
        }

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Topic created successfully.');
    }

    public function edit(Topic $topic)
    {
        $topic->load(['materials', 'unitTopic.unit']);

        $subjects = Subject::active()->orderBy('name')->get();

        $years = AcademicYear::active()
            ->with('semesters')
            ->orderBy('order')
            ->get();

        $selectedSubjectId = old('subject_id', $topic->subject_id);

        $selectedUnitId = old(
            'unit_id',
            $topic->unitTopic?->unit_id
        );

        $selectedUnitTopicId = old(
            'unit_topic_id',
            $topic->unit_topic_id
        );

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

        $parentTopics = $selectedUnitTopicId
            ? ParentTopic::where('unit_topic_id', $selectedUnitTopicId)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
            : collect();

        return view('admin.topics.edit', compact(
            'topic',
            'subjects',
            'years',
            'units',
            'unitTopics',
            'parentTopics',
            'selectedSubjectId',
            'selectedUnitId',
            'selectedUnitTopicId'
        ));
    }

    public function update(Request $request, Topic $topic)
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
            'parent_topic_id' => [
                'nullable',
                Rule::exists('parent_topics', 'id')->where(function ($query) use ($request) {
                    return $query->where('unit_topic_id', $request->unit_topic_id);
                }),
            ],

            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'parent_id' => 'nullable|exists:topics,id',
            'module_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'status' => 'sometimes|boolean',
            'is_protected' => 'sometimes|boolean',

            'materials' => 'nullable|array',
            'materials.*.id' => 'nullable|exists:learning_materials,id',
            'materials.*.title' => 'required|string|max:255',
            'materials.*.type' => 'required|in:pdf,video,link,note',
            'materials.*.content' => 'nullable|string',
            'materials.*.url' => 'nullable|url',
            'materials.*.file' => 'nullable|file|max:10240',
        ]);

        $unitTopic = UnitTopic::findOrFail($request->unit_topic_id);

        $data = $request->except('materials', 'unit_id');

        $data['title'] = $unitTopic->title;
        $data['slug'] = Str::slug($unitTopic->title . '-' . $topic->id);
        $data['status'] = $request->has('status');
        $data['is_protected'] = $request->has('is_protected');

        $topic->update($data);

        $existingMaterialIds = [];

        if ($request->has('materials')) {
            $orderIndex = 0;

            foreach ($request->materials as $materialData) {
                if (isset($materialData['id']) && $materialData['id']) {
                    $material = LearningMaterial::find($materialData['id']);

                    if ($material) {
                        $material->update([
                            'title' => $materialData['title'],
                            'type' => $materialData['type'],
                            'content' => $materialData['content'] ?? null,
                            'url' => $materialData['url'] ?? null,
                            'order' => $orderIndex,
                        ]);

                        if (isset($materialData['file']) && $materialData['file']) {
                            $material->file_path = $materialData['file']->store('materials', 'public');
                            $material->save();
                        }

                        $existingMaterialIds[] = $material->id;
                    }
                } else {
                    $material = new LearningMaterial([
                        'title' => $materialData['title'],
                        'type' => $materialData['type'],
                        'content' => $materialData['content'] ?? null,
                        'url' => $materialData['url'] ?? null,
                        'order' => $orderIndex,
                    ]);

                    if (isset($materialData['file']) && $materialData['file']) {
                        $material->file_path = $materialData['file']->store('materials', 'public');
                    }

                    $topic->materials()->save($material);

                    $existingMaterialIds[] = $material->id;
                }

                $orderIndex++;
            }
        }

        $topic->materials()->whereNotIn('id', $existingMaterialIds)->delete();

        return redirect()
            ->route('admin.topics.index')
            ->with('success', 'Topic updated successfully.');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted successfully.');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $path = $file->storeAs('uploads/topics', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json(['error' => ['message' => 'No file uploaded']], 400);
    }
}
