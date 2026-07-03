<?php

namespace App\Http\Controllers;

use App\Models\Doubt;
use Illuminate\Http\Request;

class DoubtController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'topic' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        Doubt::create([
            'user_id' => auth()->id(),
            'academic_year_id' => $request->academic_year_id,
            'subject_id' => $request->subject_id,
            'topic' => $request->topic,
            'message' => $request->message,
            'status' => Doubt::STATUS_PENDING,
        ]);

        return back()->with('success', 'Your doubt has been submitted successfully.');
    }

    public function myDoubts()
    {
        $doubts = Doubt::with([
            'academicYear',
            'subject',
            'answeredBy',
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('my-doubts', compact('doubts'));
    }
}
