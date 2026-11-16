@foreach($messages as $message)
    <article class="border-start border-3 ps-3 mb-3" id="follow-up-{{ $message->id }}">
        @if($message->is_hidden)
            <p class="text-secondary fst-italic mb-2">This message was hidden by an administrator.</p>
        @else
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                <strong>
                    {{ $message->user?->name ?? 'Former user' }}
                    @if($message->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN)
                        <span class="badge bg-primary">Admin</span>
                    @endif
                </strong>
                <time class="small text-secondary" datetime="{{ $message->created_at?->toIso8601String() }}">
                    {{ $message->created_at?->format('d M Y, h:i A') }}
                </time>
            </div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $message->question }}</p>
            <form method="POST" action="{{ route('doubt-sessions.follow-ups.replies.store', [$booking, $followUp]) }}" class="mb-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $message->id }}">
                <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <label class="visually-hidden" for="session-reply-{{ $message->id }}">Reply to {{ $message->user?->name ?? 'this user' }}</label>
                <div class="input-group">
                    <input id="session-reply-{{ $message->id }}" name="question" class="form-control" maxlength="5000" placeholder="Write a reply…" required>
                    <button class="btn btn-outline-primary" type="submit">Reply</button>
                </div>
            </form>
        @endif
        <div class="ms-3">
            @include('doubt-sessions._follow-up-messages', [
                'messages' => $message->replies,
                'booking' => $booking,
                'followUp' => $followUp,
            ])
        </div>
    </article>
@endforeach
