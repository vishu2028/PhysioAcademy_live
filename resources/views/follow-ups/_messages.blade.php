@foreach($messages as $message)
    <article class="border-start border-3 ps-3 mb-3" id="follow-up-{{ $message->id }}">
        @if($message->is_hidden)
            <p class="text-secondary fst-italic mb-2">This message was hidden by an administrator.</p>
        @else
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                <strong>
                    {{ $message->user?->name ?? 'Former user' }}
                    @if($message->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN)
                        <span class="badge bg-primary ms-1">Admin</span>
                    @endif
                </strong>
                <time class="small text-secondary" datetime="{{ $message->created_at?->toIso8601String() }}">
                    {{ $message->created_at?->format('d M Y, h:i A') }}
                </time>
            </div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $message->question }}</p>
            <form method="POST" action="{{ route('follow-ups.replies.store', $threadId) }}" class="mb-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $message->id }}">
                <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <label class="visually-hidden" for="reply-{{ $message->id }}">Reply to {{ $message->user?->name ?? 'this user' }}</label>
                <div class="input-group">
                    <input id="reply-{{ $message->id }}" name="question" class="form-control" maxlength="5000" placeholder="Write a reply…" required>
                    <button class="btn btn-outline-primary" type="submit">Reply</button>
                </div>
            </form>
        @endif
        <div class="ms-3">
            @include('follow-ups._messages', ['messages' => $message->replies, 'threadId' => $threadId])
        </div>
    </article>
@endforeach
