<?php

namespace App\Http\Controllers;

use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function myDoubts(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,pending,answered,closed'],
            'search' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'regex:/^(other|[1-9][0-9]*)$/'],
        ]);

        $userId = (int) auth()->id();
        $query = Doubt::with(['academicYear', 'subject', 'answeredBy', 'messages.sender'])
            ->withCount('likes')
            ->withExists(['likes as liked_by_user' => fn (Builder $likes) => $likes->where('user_id', $userId)])
            ->where('user_id', $userId);
        $status = $validated['status'] ?? 'all';
        $search = trim($validated['search'] ?? '');
        $subjectId = $validated['subject_id'] ?? '';

        if ($subjectId === 'other') {
            $query->whereNull('subject_id');
        } elseif ($subjectId !== '') {
            $query->where('subject_id', (int) $subjectId);
        }

        if ($search !== '') {
            $term = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function (Builder $doubts) use ($term): void {
                $doubts
                    ->where('topic', 'like', $term)
                    ->orWhere('message', 'like', $term)
                    ->orWhere('answer', 'like', $term)
                    ->orWhereHas('subject', fn (Builder $subjects) => $subjects->where('name', 'like', $term))
                    ->orWhereHas('messages', fn (Builder $messages) => $messages->where('message', 'like', $term));
            });
        }

        $recentlyAnsweredDoubts = in_array($status, ['all', 'answered'], true)
            ? (clone $query)
                ->where('status', Doubt::STATUS_ANSWERED)
                ->orderByDesc('answered_at')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get()
            : collect();

        match ($status) {
            'pending' => $query->whereIn('status', [Doubt::STATUS_PENDING, Doubt::STATUS_IN_PROGRESS]),
            'answered' => $query->where('status', Doubt::STATUS_ANSWERED),
            'closed' => $query->whereIn('status', [Doubt::STATUS_REJECTED, 'closed']),
            default => null,
        };

        $doubts = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $subjects = Subject::active()->orderBy('name')->get(['id', 'name']);
        $hasOtherSubjectDoubts = Doubt::where('user_id', $userId)->whereNull('subject_id')->exists();

        return view('my-doubts', compact(
            'doubts',
            'recentlyAnsweredDoubts',
            'subjects',
            'hasOtherSubjectDoubts',
            'status',
            'search',
            'subjectId'
        ));
    }

    public function show(int $doubt)
    {
        $doubt = Doubt::with([
            'academicYear',
            'subject',
            'answeredBy',
            'messages.sender',
        ])
            ->withCount('likes')
            ->withExists(['likes as liked_by_user' => fn (Builder $likes) => $likes->where('user_id', auth()->id())])
            ->where('user_id', auth()->id())
            ->findOrFail($doubt);

        return view('doubts.show', compact('doubt'));
    }

    public function like(int $doubt)
    {
        $record = Doubt::where('user_id', auth()->id())->findOrFail($doubt);
        abort_unless($record->status === Doubt::STATUS_ANSWERED, 403);

        $record->likes()->firstOrCreate(['user_id' => auth()->id()]);

        return back()->with('success', 'Marked as helpful.');
    }

    public function unlike(int $doubt)
    {
        $record = Doubt::where('user_id', auth()->id())->findOrFail($doubt);
        $record->likes()->where('user_id', auth()->id())->delete();

        return back()->with('success', 'Your like was removed.');
    }

    public function storeMessage(Request $request, int $doubt)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($validated, $doubt): void {
            $doubtRecord = Doubt::where('user_id', auth()->id())
                ->lockForUpdate()
                ->findOrFail($doubt);

            if (filled($doubtRecord->answer)) {
                $answerAlreadyInHistory = $doubtRecord->messages()
                    ->where('sender_role', DoubtMessage::ROLE_ADMIN)
                    ->where('message', $doubtRecord->answer)
                    ->exists();

                if (! $answerAlreadyInHistory) {
                    DoubtMessage::create([
                        'doubt_id' => $doubtRecord->id,
                        'sender_id' => $doubtRecord->answered_by,
                        'sender_role' => DoubtMessage::ROLE_ADMIN,
                        'message' => $doubtRecord->answer,
                        'created_at' => $doubtRecord->answered_at ?? $doubtRecord->updated_at,
                    ]);
                }
            }

            DoubtMessage::create([
                'doubt_id' => $doubtRecord->id,
                'sender_id' => auth()->id(),
                'sender_role' => DoubtMessage::ROLE_STUDENT,
                'message' => trim($validated['message']),
            ]);

            $doubtRecord->update([
                'answer' => null,
                'answered_by' => null,
                'answered_at' => null,
                'status' => Doubt::STATUS_PENDING,
            ]);
        });

        return redirect()
            ->route('doubts.show', $doubt)
            ->with('success', 'Your follow-up has been added to the conversation.');
    }
}
