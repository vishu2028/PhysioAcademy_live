<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->with(['user', 'booking.user'])
            ->withCount('threadMessages')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.follow-ups.index', compact('threads'));
    }

    public function show(int $followUp): View
    {
        $thread = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->with(['user', 'booking.user', 'threadMessages'])
            ->findOrFail($followUp);

        $messages = $thread->threadMessages->keyBy('id');
        $thread->setRelation('replies', $messages->where('parent_id', $thread->id)->values());
        $this->attachReplies($thread->replies, $messages);

        return view('admin.follow-ups.show', [
            'thread' => $thread,
            'submissionKey' => (string) Str::uuid(),
        ]);
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

        $discussions->reply(
            $request->user(),
            $followUp,
            $validated['parent_id'],
            $validated['question'],
            $validated['submission_key'],
            asAdmin: true
        );

        return redirect()->route('admin.follow-ups.show', $followUp)
            ->with('success', 'Your admin reply has been added.');
    }

    public function toggleVisibility(int $followUp): RedirectResponse
    {
        $entry = DoubtSessionFollowUp::findOrFail($followUp);
        $entry->update(['is_hidden' => ! $entry->is_hidden]);

        return back()->with(
            'success',
            $entry->is_hidden ? 'Content hidden from users.' : 'Content restored for users.'
        );
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
