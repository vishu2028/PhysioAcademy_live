<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ExamAid;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExamAidController extends Controller
{
    public function index()
    {
        $examAids = ExamAid::with([
            'subject',
            'unit',
            'academicYear',
            'semester',
        ])
            ->latest()
            ->paginate(20);

        return view('admin.exam-aids.index', compact('examAids'));
    }

    public function create()
    {
        return view('admin.exam-aids.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($request, $validated) {
            $examAid = ExamAid::create($this->examAidData($validated, $request));

            $this->syncMaterials($request, $examAid);
        });

        return redirect()
            ->route('admin.exam-aids.index')
            ->with('success', 'Exam Aid created successfully.');
    }

    public function edit(ExamAid $examAid)
    {
        $examAid->load('materials');

        return view('admin.exam-aids.edit', array_merge(
            $this->formData(),
            compact('examAid')
        ));
    }

    public function update(Request $request, ExamAid $examAid)
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($request, $validated, $examAid) {
            $oldFilePaths = $examAid->materials()
                ->whereNotNull('file_path')
                ->pluck('file_path')
                ->toArray();

            $examAid->update($this->examAidData($validated, $request));

            $examAid->materials()->delete();

            $newFilePaths = $this->syncMaterials($request, $examAid);

            foreach ($oldFilePaths as $oldFilePath) {
                if (! in_array($oldFilePath, $newFilePaths)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }
        });

        return redirect()
            ->route('admin.exam-aids.index')
            ->with('success', 'Exam Aid updated successfully.');
    }

    public function destroy(ExamAid $examAid)
    {
        foreach ($examAid->materials as $material) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
        }

        $examAid->delete();

        return redirect()
            ->route('admin.exam-aids.index')
            ->with('success', 'Exam Aid deleted successfully.');
    }

    private function formData()
    {
        return [
            'subjects' => Subject::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'years' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ];
    }

    private function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'subject_id' => ['required', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],

            'viva_question' => ['nullable', 'string'],
            'exam_question' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],

            'materials' => ['nullable', 'array'],
            'materials.*.title' => ['nullable', 'string', 'max:255'],
            'materials.*.type' => ['nullable', 'in:pdf,video,link,note'],
            'materials.*.file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'materials.*.url' => ['nullable', 'url'],
            'materials.*.content' => ['nullable', 'string'],
            'materials.*.existing_file_path' => ['nullable', 'string'],
        ];
    }

    private function examAidData(array $validated, Request $request)
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,

            'subject_id' => $validated['subject_id'],
            'unit_id' => $validated['unit_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'semester_id' => $validated['semester_id'] ?? null,

            'viva_question' => $validated['viva_question'] ?? null,
            'exam_question' => $validated['exam_question'] ?? null,

            'status' => $request->boolean('status'),
        ];
    }

    private function syncMaterials(Request $request, ExamAid $examAid)
    {
        $savedFilePaths = [];

        foreach ($request->input('materials', []) as $index => $material) {
            if (empty($material['title'])) {
                continue;
            }

            $type = $material['type'] ?? 'pdf';

            $filePath = null;
            $url = null;
            $content = null;

            if ($type === 'pdf') {
                $filePath = $material['existing_file_path'] ?? null;

                if ($request->hasFile("materials.$index.file")) {
                    $filePath = $request
                        ->file("materials.$index.file")
                        ->store('exam-aids/materials', 'public');
                }

                if (empty($filePath)) {
                    continue;
                }

                $savedFilePaths[] = $filePath;
            }

            if ($type === 'video' || $type === 'note') {
                $content = $material['content'] ?? null;
            }

            if ($type === 'link') {
                $url = $material['url'] ?? null;
            }

            $examAid->materials()->create([
                'title' => $material['title'],
                'type' => $type,
                'file_path' => $filePath,
                'url' => $url,
                'content' => $content,
                'order' => $index,
            ]);
        }

        return $savedFilePaths;
    }
}
