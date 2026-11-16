@extends('layouts.admin')

@section('title', 'Follow-up Discussions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Follow-up Discussions</h2>
            <p class="text-secondary mb-0">Review community discussions and private booking follow-ups.</p>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Discussion</th><th>Author</th><th>Type</th><th>Replies</th><th>Started</th><th></th></tr></thead>
                <tbody>
                    @forelse($threads as $thread)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($thread->question, 100) }}</div>
                                @if($thread->is_hidden)<span class="badge bg-danger">Hidden</span>@endif
                            </td>
                            <td>{{ $thread->user?->name ?? $thread->booking?->student_name ?? 'Former user' }}</td>
                            <td>{{ $thread->booking ? 'Private session' : 'Community' }}</td>
                            <td>{{ $thread->thread_messages_count }}</td>
                            <td>{{ $thread->created_at?->format('d M Y, h:i A') }}</td>
                            <td><a href="{{ route('admin.follow-ups.show', $thread) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">No follow-ups have been submitted.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $threads->links() }}</div>
@endsection
