@php
    $latestAdminMessage = $doubt->messages
        ->where('sender_role', \App\Models\DoubtMessage::ROLE_ADMIN)
        ->last();
    $displayAnswer = $doubt->answer ?: $latestAdminMessage?->message;
    $replyCount = (int) ($doubt->messages_count ?? 0)
        + (filled($doubt->answer) && ! $latestAdminMessage ? 1 : 0);
    $answerPreview = $displayAnswer
        ? trim(html_entity_decode(strip_tags($displayAnswer), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        : '';
    $statusLabel = match ($doubt->status) {
        \App\Models\Doubt::STATUS_ANSWERED => 'Answered',
        \App\Models\Doubt::STATUS_REJECTED, 'closed' => 'Closed',
        default => 'Pending',
    };
    $statusClass = match ($statusLabel) {
        'Answered' => 'answered',
        'Closed' => 'closed',
        default => 'pending',
    };
@endphp

<article class="doubt-library-card" id="doubt-{{ $doubt->id }}">
    <div class="doubt-library-card-heading">
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($doubt->user?->avatarUrl())
                <img src="{{ $doubt->user->avatarUrl() }}" alt="" width="40" height="40" class="rounded-circle" style="object-fit: cover;">
            @else
                <span class="doubt-community-avatar doubt-community-avatar-fallback" aria-hidden="true">
                    {{ strtoupper(mb_substr($doubt->user?->name ?? 'U', 0, 1)) }}
                </span>
            @endif
            <div class="d-flex flex-column gap-1">
                <strong>{{ $doubt->user?->name ?? 'Former user' }}</strong>
                <span class="doubt-library-card-tags">
                    <span class="doubt-subject-tag">{{ $doubt->subject?->name ?? 'Other Subjects' }}</span>
                    <span class="doubt-status-tag {{ $statusClass }}">{{ $statusLabel }}</span>
                </span>
            </div>
        </div>
        <time class="doubt-library-date" datetime="{{ $doubt->created_at?->toIso8601String() }}">
            {{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
        </time>
    </div>

    <h3 class="doubt-library-question-title">
        {{ $doubt->topic ?: \Illuminate\Support\Str::limit($doubt->message, 90) }}
    </h3>
    <p class="doubt-library-question">{{ $doubt->message }}</p>
    <p class="doubt-library-privacy">
        Original doubt · {{ $doubt->user?->hasAnyRole(['admin', 'super_admin']) ? 'Admin' : 'Student' }}
        · {{ $replyCount }} {{ \Illuminate\Support\Str::plural('reply', $replyCount) }}
        · {{ $doubt->discussion_follows_count ?? 0 }} {{ \Illuminate\Support\Str::plural('follower', $doubt->discussion_follows_count ?? 0) }}
    </p>

    @if($displayAnswer)
        <div class="doubt-library-answer">
            <h4>Latest admin explanation <span>by {{ $doubt->answeredBy?->name ?? 'Admin' }}</span></h4>
            <p class="doubt-library-answer-preview">{{ \Illuminate\Support\Str::limit($answerPreview, 300) }}</p>
            <details class="doubt-library-answer-details">
                <summary>Read Full Answer</summary>
                <div class="doubt-library-answer-full">
                    {!! \App\Models\Doubt::sanitizeHtml($displayAnswer) !!}
                </div>
            </details>
        </div>
    @else
        <p class="doubt-library-waiting">
            <i class="bi bi-hourglass-split" aria-hidden="true"></i>
            No admin explanation yet.
        </p>
    @endif

    <div class="doubt-library-actions">
        @if($doubt->status === \App\Models\Doubt::STATUS_ANSWERED && $displayAnswer && (int) $doubt->user_id === (int) auth()->id())
            @if($doubt->liked_by_user)
                <form method="POST" action="{{ route('doubts.likes.destroy', $doubt) }}">
                    @csrf
                    @method('DELETE')
                    <button class="doubt-library-action liked" type="submit" aria-pressed="true">
                        <i class="bi bi-hand-thumbs-up-fill" aria-hidden="true"></i>
                        Helpful <span class="doubt-like-count">{{ $doubt->likes_count }}</span>
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('doubts.likes.store', $doubt) }}">
                    @csrf
                    <button class="doubt-library-action" type="submit" aria-pressed="false">
                        <i class="bi bi-hand-thumbs-up" aria-hidden="true"></i>
                        Helpful <span class="doubt-like-count">{{ $doubt->likes_count }}</span>
                    </button>
                </form>
            @endif
        @endif

        @if($doubtFollowsAvailable)
            @if($doubt->followed_by_user ?? false)
                <form method="POST" action="{{ route('doubts.follow.destroy', $doubt) }}">
                    @csrf
                    @method('DELETE')
                    <button class="doubt-library-action" type="submit">
                        <i class="bi bi-bookmark-check" aria-hidden="true"></i> Unfollow
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('doubts.follow.store', $doubt) }}">
                    @csrf
                    <button class="doubt-library-action" type="submit">
                        <i class="bi bi-bookmark-plus" aria-hidden="true"></i> Follow
                    </button>
                </form>
            @endif
        @endif

        <a class="doubt-library-action" href="{{ route('doubts.show', $doubt) }}#follow-up-message">
            <i class="bi bi-chat-dots" aria-hidden="true"></i>
            Add follow-up
        </a>
        <a class="doubt-library-history-link" href="{{ route('doubts.show', $doubt) }}">
            View follow-ups <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
