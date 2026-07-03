<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doubt;
use Illuminate\Http\Request;

class DoubtController extends Controller
{
    public function index()
    {
        $doubts = Doubt::with([
            'user',
            'academicYear',
            'subject',
            'answeredBy',
        ])
            ->latest()
            ->paginate(20);

        return view('admin.doubts.index', compact('doubts'));
    }

    public function update(Request $request, Doubt $doubt)
    {
        $request->validate([
            'status' => ['required', 'in:pending,in_progress,answered,rejected'],
            'answer' => ['nullable', 'string'],
        ]);

        $data = [
            'status' => $request->status,
            'answer' => $request->answer,
        ];

        if ($request->status === 'answered' && $request->filled('answer')) {
            $data['answered_by'] = auth()->id();
            $data['answered_at'] = now();
        }

        $doubt->update($data);

        return back()->with('success', 'Doubt updated successfully.');
    }

    public function destroy(Doubt $doubt)
    {
        $doubt->delete();

        return back()->with('success', 'Doubt deleted successfully.');
    }
}
