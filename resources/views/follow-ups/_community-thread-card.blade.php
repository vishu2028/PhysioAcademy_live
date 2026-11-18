<article class="doubt-library-card" id="community-thread-{{ $thread->id }}">
    <div class="doubt-library-card-heading">
        <div class="doubt-community-author">
            @if($thread->user?->avatarUrl())
                <img class="doubt-community-avatar" src="{{ $thread->user->avatarUrl() }}" alt="">
            @else
                <span class="doubt-community-avatar doubt-community-avatar-fallback" aria-hidden="true">
                    {{ strtoupper(mb_substr($thread->user?->name ?? 'U', 0, 1)) }}
                </span>
            @endif
            <div>
                <strong>{{ $thread->user?->name ?? 'Former user' }}</strong>
                <span class="doubt-community-role">
                    {{ $thread->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN ? 'Admin' : 'Student' }}
                </span>
            </div>
        </div>
        <time class="doubt-library-date" datetime="{{ $thread->created_at?->toIso8601String() }}">
            {{ $thread->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
        </time>
    </div>

    <p class="doubt-library-question">{{ $thread->question }}</p>
    <div class="doubt-library-actions">
        <span class="doubt-library-action" aria-label="{{ $thread->thread_messages_count }} replies">
            <i class="bi bi-chat-dots" aria-hidden="true"></i>
            {{ $thread->thread_messages_count }} {{ \Illuminate\Support\Str::plural('reply', $thread->thread_messages_count) }}
        </span>
        <span class="doubt-library-action" aria-label="{{ $thread->discussion_follows_count ?? 0 }} followers">
            <i class="bi bi-people" aria-hidden="true"></i>
            {{ $thread->discussion_follows_count ?? 0 }} followers
        </span>
        @if($doubtFollowsAvailable)
            @if($thread->followed_by_user ?? false)
                <form method="POST" action="{{ route('follow-ups.follow.destroy', $thread) }}">
                    @csrf
                    @method('DELETE')
                    <button class="doubt-library-action" type="submit">
                        <i class="bi bi-bookmark-check" aria-hidden="true"></i> Unfollow
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('follow-ups.follow.store', $thread) }}">
                    @csrf
                    <button class="doubt-library-action" type="submit">
                        <i class="bi bi-bookmark-plus" aria-hidden="true"></i> Follow
                    </button>
                </form>
            @endif
        @endif
    </div>

    <div class="ms-2 ms-md-4 mt-3">
        @include('follow-ups._messages', ['messages' => $thread->replies, 'threadId' => $thread->id])
    </div>

    <form method="POST" action="{{ route('follow-ups.replies.store', $thread) }}" class="border-top pt-3 mt-3">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $thread->id }}">
        <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <label class="form-label fw-semibold" for="community-reply-{{ $thread->id }}">Add a follow-up</label>
        <textarea id="community-reply-{{ $thread->id }}" name="question" class="form-control" rows="2" maxlength="5000" required>{{ old('question') }}</textarea>
        <button type="submit" class="btn btn-primary rounded-pill px-4 mt-2">Reply</button>
    </form>
</article>
