@extends('layouts.frontend')

@section('title', 'Follow-up Discussion')

@section('content')
    <main class="container py-5" style="max-width: 1000px; padding-top: 120px !important;">
        <a href="{{ route('follow-ups.index') }}" class="text-decoration-none">&larr; All discussions</a>
        @if(session('success'))
            <div class="alert alert-success mt-3" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mt-3" role="alert">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <article class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mt-3 mb-4" id="follow-up-{{ $thread->id }}">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                <strong>
                    {{ $thread->user?->name ?? 'Former user' }}
                    @if($thread->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN)
                        <span class="badge bg-primary ms-1">Admin</span>
                    @endif
                </strong>
                <time class="small text-secondary" datetime="{{ $thread->created_at?->toIso8601String() }}">
                    {{ $thread->created_at?->format('d M Y, h:i A') }}
                </time>
            </div>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $thread->question }}</p>
        </article>

        <section class="ms-2 ms-md-4" aria-label="Replies">
            @include('follow-ups._messages', ['messages' => $thread->replies, 'threadId' => $thread->id])
        </section>

        <form method="POST" action="{{ route('follow-ups.replies.store', $thread) }}" class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mt-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $thread->id }}">
            <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
            <label for="thread-reply" class="form-label fw-bold">Add to the discussion</label>
            <textarea id="thread-reply" name="question" class="form-control" rows="3" maxlength="5000" required>{{ old('question') }}</textarea>
            @error('question')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <button type="submit" class="btn btn-primary rounded-pill px-4 mt-3 align-self-start">Reply</button>
        </form>
    </main>
@endsection
