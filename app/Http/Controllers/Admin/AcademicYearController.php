<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::with('semesters')
            ->withCurriculumCounts()
            ->orderBy('order')
            ->get();
        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'semesters' => 'nullable|array',
            'semesters.*' => 'string|max:255',
        ]);

        $data = $request->except([
            'semesters',
            'units_count',
            'topics_count',
        ]);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->has('status');

        $year = AcademicYear::create($data);

        if ($request->has('semesters')) {
            foreach ($request->semesters as $index => $semesterName) {
                if ($semesterName) {
                    $year->semesters()->create([
                        'name' => $semesterName,
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.academic-years.index')->with('success', 'Academic Year created successfully.');
    }

    public function edit(AcademicYear $academicYear)
    {
        $academicYear->load('semesters');
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'semesters' => 'nullable|array',
            'semesters.*.id' => 'nullable|exists:semesters,id',
            'semesters.*.name' => 'required|string|max:255',
        ]);

        $data = $request->except([
            'semesters',
            'units_count',
            'topics_count',
        ]);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->has('status');

        $academicYear->update($data);

        // Simple sync for semesters
        $existingIds = [];
        if ($request->has('semesters')) {
            foreach ($request->semesters as $index => $semesterData) {
                if (isset($semesterData['id']) && $semesterData['id']) {
                    $semester = Semester::find($semesterData['id']);
                    $semester->update([
                        'name' => $semesterData['name'],
                        'order' => $index,
                    ]);
                    $existingIds[] = $semester->id;
                } else {
                    $newSemester = $academicYear->semesters()->create([
                        'name' => $semesterData['name'],
                        'order' => $index,
                    ]);
                    $existingIds[] = $newSemester->id;
                }
            }
        }
        $academicYear->semesters()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.academic-years.index')->with('success', 'Academic Year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return back()->with('success', 'Academic Year deleted successfully.');
    }
}
