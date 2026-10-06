<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('subject')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();

        return view('admin.units.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->subject_id);
                }),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['name']);

        Unit::create($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        $subjects = Subject::orderBy('name')->get();

        return view('admin.units.edit', compact('unit', 'subjects'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')
                    ->where(function ($query) use ($request) {
                        return $query->where('subject_id', $request->subject_id);
                    })
                    ->ignore($unit->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['name'], $unit->id);

        $unit->update($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->topics()->exists()) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'This unit has topics. Please delete or move topics first.');
        }

        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit deleted successfully.');
    }

    public function toggleStatus(Unit $unit)
    {
        $unit->update([
            'is_active' => ! $unit->is_active,
        ]);

        return back()->with('success', 'Unit status updated successfully.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
        Unit::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
    public function bySubject(Subject $subject)
    {
        $units = $subject->units()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($units);
    }
}
