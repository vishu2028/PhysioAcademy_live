@extends('layouts.admin')

@section('title', 'Follow-up Discussion')

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-3" id="follow-up-{{ $thread->id }}">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div class="fw-bold">
                {{ $thread->user?->name ?? $thread->booking?->student_name ?? 'Former user' }}
                @if($thread->author_role === \App\Models\DoubtSessionFollowUp::ROLE_ADMIN)<span class="badge bg-primary">Admin</span>@endif
            </div>
            <time class="text-secondary small" datetime="{{ $thread->created_at?->toIso8601String() }}">{{ $thread->created_at?->format('d M Y, h:i A') }}</time>
        </div>
        @if($thread->booking)
            <div class="small text-secondary mb-2">Private follow-up for booking {{ $thread->booking->booking_reference }}</div>
        @endif
        <p class="mb-2" style="white-space: pre-wrap;">{{ $thread->question }}</p>
        <form action="{{ route('admin.follow-ups.visibility.update', $thread) }}" method="POST">
            @csrf @method('PATCH')
            <button class="btn btn-sm {{ $thread->is_hidden ? 'btn-outline-success' : 'btn-outline-danger' }}" type="submit">
                {{ $thread->is_hidden ? 'Restore for users' : 'Hide from users' }}
            </button>
        </form>
    </div>

    <section class="ms-2 ms-md-4" aria-label="Discussion replies">
        @include('admin.follow-ups._messages', ['messages' => $thread->replies, 'thread' => $thread])
    </section>

    <form method="POST" action="{{ route('admin.follow-ups.replies.store', $thread) }}" class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mt-4">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $thread->id }}">
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
        <label for="admin-reply" class="form-label fw-bold">Reply as administrator</label>
        <textarea id="admin-reply" name="question" class="form-control" rows="4" maxlength="5000" required>{{ old('question') }}</textarea>
        @error('question')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <button type="submit" class="btn btn-primary mt-3 align-self-start">Send admin reply</button>
    </form>
@endsection
