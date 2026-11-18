<?php

namespace App\Http\Controllers;

use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Models\DoubtSessionFollowUp;
use App\Models\Subject;
use App\Services\DoubtDiscussionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $doubt = Doubt::create([
            'user_id' => auth()->id(),
            'academic_year_id' => $request->academic_year_id,
            'subject_id' => $request->subject_id,
            'topic' => $request->topic,
            'message' => $request->message,
            'status' => Doubt::STATUS_PENDING,
        ]);

        if (Schema::hasTable('discussion_follows')) {
            $doubt->discussionFollows()->firstOrCreate(['user_id' => auth()->id()]);
        }

        return back()->with('success', 'Your doubt has been submitted successfully.');
    }

    public function myDoubts(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,pending,answered,closed'],
            'search' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'regex:/^(other|[1-9][0-9]*)$/'],
            'discussion_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $userId = (int) auth()->id();
        $doubtFollowsAvailable = Schema::hasTable('discussion_follows');
        $query = Doubt::with(['user', 'academicYear', 'subject', 'answeredBy', 'messages.sender'])
            ->withCount(['likes', 'messages'])
            ->withExists(['likes as liked_by_user' => fn (Builder $likes) => $likes->where('user_id', $userId)]);
        if ($doubtFollowsAvailable) {
            $query->withCount('discussionFollows')
                ->withExists([
                    'discussionFollows as followed_by_user' => fn (Builder $follows) => $follows->where('user_id', $userId),
                ]);
        }
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
                    ->orWhereHas('user', fn (Builder $users) => $users->where('name', 'like', $term))
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
            ->paginate(10, ['*'], 'doubts_page')
            ->withQueryString();
        $subjects = Subject::active()->orderBy('name')->get(['id', 'name']);
        $hasOtherSubjectDoubts = Doubt::whereNull('subject_id')->exists();

        $followUpSchemaAvailable = Schema::hasColumns('doubt_session_follow_ups', [
            'parent_id',
            'thread_id',
            'is_hidden',
            'doubt_session_booking_id',
        ]);
        $communityThreads = collect();
        $featuredCommunityThread = null;

        if ($followUpSchemaAvailable) {
            $communityQuery = DoubtSessionFollowUp::query()
                ->whereNull('parent_id')
                ->whereNull('doubt_session_booking_id')
                ->where('is_hidden', false)
                ->with(['user', 'threadMessages.user'])
                ->withCount('threadMessages');

            if ($doubtFollowsAvailable) {
                $communityQuery->withCount('discussionFollows')
                    ->withExists([
                        'discussionFollows as followed_by_user' => fn (Builder $follows) => $follows->where('user_id', $userId),
                    ]);
            }

            if (isset($validated['discussion_id'])) {
                $featuredCommunityThread = (clone $communityQuery)
                    ->whereKey($validated['discussion_id'])
                    ->firstOrFail();
                $this->attachCommunityReplies($featuredCommunityThread);
            }

            $communityListQuery = clone $communityQuery;
            if ($featuredCommunityThread) {
                $communityListQuery->where('id', '<>', $featuredCommunityThread->id);
            }
            if ($search !== '') {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $communityListQuery->where(function (Builder $threads) use ($term): void {
                    $threads->where('question', 'like', $term)
                        ->orWhereHas('user', fn (Builder $users) => $users->where('name', 'like', $term))
                        ->orWhereHas('threadMessages', fn (Builder $messages) => $messages->where('question', 'like', $term));
                });
            }

            $communityThreads = $communityListQuery
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'community_page')
                ->withQueryString();
            $communityThreads->getCollection()->each(fn (DoubtSessionFollowUp $thread) => $this->attachCommunityReplies($thread));
        }

        return view('my-doubts', compact(
            'doubts',
            'recentlyAnsweredDoubts',
            'subjects',
            'hasOtherSubjectDoubts',
            'status',
            'search',
            'subjectId',
            'communityThreads',
            'featuredCommunityThread',
            'followUpSchemaAvailable',
            'doubtFollowsAvailable'
        ));
    }

    public function show(int $doubt)
    {
        $query = Doubt::with([
            'user',
            'academicYear',
            'subject',
            'answeredBy',
            'messages.sender',
        ])
            ->withCount(['likes', 'messages'])
            ->withExists(['likes as liked_by_user' => fn (Builder $likes) => $likes->where('user_id', auth()->id())]);
        if (Schema::hasTable('discussion_follows')) {
            $query->withCount('discussionFollows')
                ->withExists([
                    'discussionFollows as followed_by_user' => fn (Builder $follows) => $follows->where('user_id', auth()->id()),
                ]);
        }
        $doubt = $query->findOrFail($doubt);

        return view('doubts.show', [
            'doubt' => $doubt,
            'doubtFollowsAvailable' => Schema::hasTable('discussion_follows'),
        ]);
    }

    public function follow(int $doubt, DoubtDiscussionService $discussions)
    {
        abort_unless(Schema::hasTable('discussion_follows'), 503, 'Follow options are unavailable until the migration is applied.');
        $record = Doubt::findOrFail($doubt);
        $discussions->follow($record, auth()->user());

        return back()->with('success', 'You are now following this doubt.');
    }

    public function unfollow(int $doubt, DoubtDiscussionService $discussions)
    {
        abort_unless(Schema::hasTable('discussion_follows'), 503, 'Follow options are unavailable until the migration is applied.');
        $record = Doubt::findOrFail($doubt);
        $discussions->unfollow($record, auth()->user());

        return back()->with('success', 'You unfollowed this doubt.');
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

    public function storeMessage(Request $request, int $doubt, DoubtDiscussionService $discussions)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $reply = DB::transaction(function () use ($validated, $doubt): DoubtMessage {
            $doubtRecord = Doubt::query()
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

            $sender = auth()->user();
            $reply = DoubtMessage::create([
                'doubt_id' => $doubtRecord->id,
                'sender_id' => $sender->id,
                'sender_role' => $sender->hasAnyRole(['admin', 'super_admin'])
                    ? DoubtMessage::ROLE_ADMIN
                    : DoubtMessage::ROLE_STUDENT,
                'message' => trim($validated['message']),
            ]);

            $doubtRecord->update([
                'answer' => null,
                'answered_by' => null,
                'answered_at' => null,
                'status' => Doubt::STATUS_PENDING,
            ]);

            return $reply;
        });

        $discussions->notifyFollowers($reply->doubt, $reply);

        return redirect()
            ->route('doubts.show', $doubt)
            ->with('success', 'Your follow-up has been added.');
    }

    private function attachCommunityReplies(DoubtSessionFollowUp $thread): void
    {
        $messages = $thread->threadMessages->keyBy('id');
        $thread->setRelation('replies', $messages->where('parent_id', $thread->id)->values());
        $this->attachNestedCommunityReplies($thread->replies, $messages);
    }

    private function attachNestedCommunityReplies(iterable $parents, $messages): void
    {
        foreach ($parents as $parent) {
            $replies = $messages->where('parent_id', $parent->id)->values();
            $parent->setRelation('replies', $replies);
            $this->attachNestedCommunityReplies($replies, $messages);
        }
    }
}
