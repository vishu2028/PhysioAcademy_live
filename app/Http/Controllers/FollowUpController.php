<?php

namespace App\Http\Controllers;

use App\Models\DoubtSessionFollowUp;
use App\Services\FollowUpDiscussionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FollowUpController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('doubts.mine');
    }

    public function show(int $followUp): RedirectResponse
    {
        abort_unless(
            Schema::hasColumns('doubt_session_follow_ups', ['parent_id', 'doubt_session_booking_id', 'is_hidden']),
            503,
            'Follow-ups are not available until the follow-up migration is applied.'
        );

        $thread = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->whereNull('doubt_session_booking_id')
            ->where('is_hidden', false)
            ->findOrFail($followUp);

        return $this->communityRedirect($thread);
    }

    public function follow(int $followUp): RedirectResponse
    {
        abort_unless(Schema::hasTable('discussion_follows'), 503, 'Follow options are unavailable until the migration is applied.');
        $thread = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->whereNull('doubt_session_booking_id')
            ->where('is_hidden', false)
            ->findOrFail($followUp);
        $thread->discussionFollows()->firstOrCreate(['user_id' => auth()->id()]);

        return $this->communityRedirect($thread)->with('success', 'You are now following this follow-up.');
    }

    public function unfollow(int $followUp): RedirectResponse
    {
        abort_unless(Schema::hasTable('discussion_follows'), 503, 'Follow options are unavailable until the migration is applied.');
        $thread = DoubtSessionFollowUp::query()
            ->whereNull('parent_id')
            ->whereNull('doubt_session_booking_id')
            ->where('is_hidden', false)
            ->findOrFail($followUp);
        $thread->discussionFollows()->where('user_id', auth()->id())->delete();

        return $this->communityRedirect($thread)->with('success', 'You unfollowed this follow-up.');
    }

    public function store(Request $request, FollowUpDiscussionService $discussions): RedirectResponse
    {
        abort_unless(
            Schema::hasColumns('doubt_session_follow_ups', ['parent_id', 'thread_id', 'submission_key']),
            503,
            'Follow-ups are not available until the follow-up migration is applied.'
        );

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'submission_key' => ['required', 'uuid'],
        ]);

        $thread = $discussions->createThread(
            $request->user(),
            $validated['question'],
            $validated['submission_key']
        );

        return $this->communityRedirect($thread)
            ->with('success', $thread->wasRecentlyCreated
                ? 'Your follow-up has been posted.'
                : 'This follow-up was already submitted.');
    }

    public function reply(
        Request $request,
        int $followUp,
        FollowUpDiscussionService $discussions
    ): RedirectResponse {
        abort_unless(
            Schema::hasColumns('doubt_session_follow_ups', ['parent_id', 'thread_id', 'submission_key']),
            503,
            'Follow-ups are not available until the follow-up migration is applied.'
        );

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

        return $this->communityRedirect(DoubtSessionFollowUp::findOrFail($followUp))
            ->with('success', $reply->wasRecentlyCreated
                ? 'Your reply has been added.'
                : 'This reply was already submitted.');
    }

    private function communityRedirect(DoubtSessionFollowUp $thread): RedirectResponse
    {
        return redirect()->to(
            route('doubts.mine', ['discussion_id' => $thread->thread_id ?: $thread->id])
            .'#community-thread-'.($thread->thread_id ?: $thread->id)
        );
    }
}
