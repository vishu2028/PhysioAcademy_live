<?php

namespace App\Http\Controllers;

use App\Models\DoubtSessionFollowUp;
use App\Services\FollowUpDiscussionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function index(): View
    {
        $threads = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->whereNull('doubt_session_booking_id')
            ->where('is_hidden', false)
            ->with(['user', 'threadMessages'])
            ->withCount('threadMessages')
            ->latest('created_at')
            ->paginate(10);

        return view('follow-ups.index', [
            'threads' => $threads,
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    public function show(int $followUp): View
    {
        $thread = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->whereNull('doubt_session_booking_id')
            ->where('is_hidden', false)
            ->with(['user', 'threadMessages'])
            ->findOrFail($followUp);

        $messages = $thread->threadMessages->keyBy('id');
        $thread->setRelation('replies', $messages->where('parent_id', $thread->id)->values());
        $this->attachReplies($thread->replies, $messages);

        return view('follow-ups.show', [
            'thread' => $thread,
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, FollowUpDiscussionService $discussions): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'submission_key' => ['required', 'uuid'],
        ]);

        $thread = $discussions->createThread(
            $request->user(),
            $validated['question'],
            $validated['submission_key']
        );

        return redirect()->route('follow-ups.show', $thread)
            ->with('success', $thread->wasRecentlyCreated
                ? 'Your follow-up discussion has been posted.'
                : 'This discussion was already submitted.');
    }

    public function reply(
        Request $request,
        int $followUp,
        FollowUpDiscussionService $discussions
    ): RedirectResponse {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'parent_id' => ['required', 'integer'],
            'submission_key' => ['required', 'uuid'],
        ]);

        $reply = $discussions->reply(
            $request->user(),
            $followUp,
            $validated['parent_id'],
            $validated['question'],
            $validated['submission_key']
        );

        return redirect()->route('follow-ups.show', $followUp)
            ->with('success', $reply->wasRecentlyCreated
                ? 'Your reply has been added.'
                : 'This reply was already submitted.');
    }

    private function attachReplies(iterable $parents, $messages): void
    {
        foreach ($parents as $parent) {
            $replies = $messages->where('parent_id', $parent->id)->values();
            $parent->setRelation('replies', $replies);
            $this->attachReplies($replies, $messages);
        }
    }
}
