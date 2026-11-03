@php
    $latestAdminMessage = $doubt->messages
        ->where('sender_role', \App\Models\DoubtMessage::ROLE_ADMIN)
        ->last();
    $displayAnswer = $doubt->answer ?: $latestAdminMessage?->message;
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
        <div class="doubt-library-card-tags">
            <span class="doubt-subject-tag">{{ $doubt->subject?->name ?? 'Other Subjects' }}</span>
            <span class="doubt-status-tag {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <span class="doubt-library-date">
            Submitted {{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y') }}
        </span>
    </div>

    <h3 class="doubt-library-question-title">
        {{ $doubt->topic ?: \Illuminate\Support\Str::limit($doubt->message, 90) }}
    </h3>
    <p class="doubt-library-question">{{ $doubt->message }}</p>
    <p class="doubt-library-privacy">Question submitted by a student · Student identity hidden</p>

    @if($displayAnswer)
        <div class="doubt-library-answer">
            <h4>Answer <span>Answered by PhysioSource</span></h4>
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
            Your doubt is awaiting an answer.
        </p>
    @endif

    <div class="doubt-library-actions">
        @if($doubt->status === \App\Models\Doubt::STATUS_ANSWERED && $displayAnswer)
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

        <a class="doubt-library-action" href="{{ route('doubts.show', $doubt) }}#follow-up-message">
            <i class="bi bi-chat-dots" aria-hidden="true"></i>
            Ask a Follow-up
        </a>
        <a class="doubt-library-history-link" href="{{ route('doubts.show', $doubt) }}">
            View full history <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
