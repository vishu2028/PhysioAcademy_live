@foreach($messages as $message)
    <article class="border-start border-3 ps-3 mb-3" id="follow-up-{{ $message->id }}">
        @if($message->is_hidden)
            <p class="text-secondary fst-italic mb-2">This message is hidden from users.</p>
        @else
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                <strong>
                    {{ $message->user?->name ?? 'Former user' }}
                    @if($message->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN)<span class="badge bg-primary ms-1">Admin</span>@endif
                </strong>
                <time class="small text-secondary" datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->format('d M Y, h:i A') }}</time>
            </div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $message->question }}</p>
        @endif
        <div class="d-flex flex-wrap gap-2 align-items-start mb-3">
            <form method="POST" action="{{ route('admin.follow-ups.replies.store', $thread) }}" class="d-flex flex-grow-1 gap-2">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $message->id }}">
                <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <label class="visually-hidden" for="admin-reply-{{ $message->id }}">Reply to this message</label>
                <input id="admin-reply-{{ $message->id }}" name="question" class="form-control" maxlength="5000" placeholder="Reply as administrator…" required>
                <button class="btn btn-outline-primary" type="submit">Reply</button>
            </form>
            <form action="{{ route('admin.follow-ups.visibility.update', $message) }}" method="POST">
                @csrf @method('PATCH')
                <button class="btn btn-sm {{ $message->is_hidden ? 'btn-outline-success' : 'btn-outline-danger' }}" type="submit">
                    {{ $message->is_hidden ? 'Restore' : 'Hide' }}
                </button>
            </form>
        </div>
        <div class="ms-3">
            @include('admin.follow-ups._messages', ['messages' => $message->replies, 'thread' => $thread])
        </div>
    </article>
@endforeach
