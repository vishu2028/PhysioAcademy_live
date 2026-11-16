@extends('layouts.frontend')

@section('title', 'Follow-up Discussions')

@section('content')
    <main class="container py-5" style="max-width: 1000px; padding-top: 120px !important;">
        <header class="mb-4">
            <span class="text-primary fw-bold text-uppercase small">Community</span>
            <h1 class="fw-bold mt-2">Follow-up Discussions</h1>
            <p class="text-secondary">Ask a question, share an update, and continue the conversation with the community.</p>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('follow-ups.store') }}" class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5">
            @csrf
            <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
            <label for="follow-up-question" class="form-label fw-bold">Start a discussion</label>
            <textarea id="follow-up-question" name="question" class="form-control" rows="4" maxlength="5000" required>{{ old('question') }}</textarea>
            @error('question')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <button type="submit" class="btn btn-primary rounded-pill px-4 mt-3 align-self-start">Post discussion</button>
        </form>

        <section aria-label="Community follow-up discussions">
            @forelse($threads as $thread)
                <article class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <strong>{{ $thread->user?->name ?? 'Former user' }}</strong>
                        <time class="small text-secondary" datetime="{{ $thread->created_at?->toIso8601String() }}">
                            {{ $thread->created_at?->format('d M Y, h:i A') }}
                        </time>
                    </div>
                    <p class="mb-3" style="white-space: pre-wrap;">{{ $thread->question }}</p>
                    <a href="{{ route('follow-ups.show', $thread) }}" class="text-primary fw-semibold text-decoration-none">
                        Join discussion · {{ $thread->thread_messages_count }} {{ \Illuminate\Support\Str::plural('reply', $thread->thread_messages_count) }}
                    </a>
                </article>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-4 text-secondary">No discussions yet. Start the first one above.</div>
            @endforelse
            <div class="mt-4">{{ $threads->links() }}</div>
        </section>
    </main>
@endsection
