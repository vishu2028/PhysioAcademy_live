<?php

namespace App\Http\Controllers;

use App\Models\TopicRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopicRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('topicRequest', [
            'submission_token' => ['required', 'uuid'],
            'topic_name' => ['required', 'string', 'max:255'],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('status', true),
            ],
            'academic_year_id' => [
                'nullable',
                Rule::exists('academic_years', 'id')->where('status', true),
            ],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', 'in:normal,high'],
        ]);

        if ($request->filled('semester_id')) {
            $semesterBelongsToYear = \App\Models\Semester::query()
                ->whereKey($validated['semester_id'])
                ->when(
                    $request->filled('academic_year_id'),
                    fn ($query) => $query->where('academic_year_id', $validated['academic_year_id'])
                )
                ->exists();

            if (! $semesterBelongsToYear) {
                return back()
                    ->withErrors([
                        'semester_id' => 'Select a semester from the chosen academic year.',
                    ], 'topicRequest')
                    ->withInput();
            }
        }

        $topicRequest = TopicRequest::firstOrCreate(
            ['submission_token' => $validated['submission_token']],
            [
                'user_id' => $request->user()->id,
                'topic_name' => $validated['topic_name'],
                'subject_id' => $validated['subject_id'],
                'academic_year_id' => $validated['academic_year_id'] ?? null,
                'semester_id' => $validated['semester_id'] ?? null,
                'description' => $validated['description'],
                'priority' => $validated['priority'] ?? TopicRequest::PRIORITY_NORMAL,
                'status' => TopicRequest::STATUS_PENDING,
            ]
        );

        return redirect(url(route('home')) . '#request-topic')
            ->with(
                'topic_request_success',
                $topicRequest->wasRecentlyCreated
                    ? 'Your topic request has been submitted successfully.'
                    : 'This topic request was already submitted.'
            );
    }

    public function mine(Request $request)
    {
        $topicRequests = TopicRequest::with([
            'subject',
            'academicYear',
            'semester',
            'topic',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('my-topic-requests', compact('topicRequests'));
    }
}
