@extends('layouts.admin')

@section('title', 'Topic Requests')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold">Topic Requests</h2>
        <p class="text-secondary">Review student topic suggestions and keep their progress up to date.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <label class="visually-hidden" for="topic-request-search">Search topic requests</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </span>
                        <input
                            type="search"
                            id="topic-request-search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search topic, student, subject or description..."
                        >
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if(request('search'))
                        <a class="btn btn-outline-secondary" href="{{ route('admin.topic-requests.index') }}">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Topic Name</th>
                        <th>Subject</th>
                        <th>Academic Year / Semester</th>
                        <th>Requested By</th>
                        <th>Description</th>
                        <th>Submitted Date &amp; Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($topicRequests as $topicRequest)
                        @php
                            $badgeClass = match($topicRequest->status) {
                                'resolved' => 'success',
                                'in_progress' => 'info',
                                default => 'warning',
                            };
                        @endphp
                        <tr id="topic-request-{{ $topicRequest->id }}">
                            <td>#{{ $topicRequest->id }}</td>
                            <td>
                                <strong>{{ $topicRequest->topic_name }}</strong>
                                @if($topicRequest->priority === 'high')
                                    <span class="badge bg-danger-subtle text-danger ms-1">High</span>
                                @endif
                                @if($topicRequest->possible_topic && !$topicRequest->topic_id)
                                    <small class="d-block text-primary mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        A matching topic may already exist.
                                    </small>
                                @endif
                            </td>
                            <td>{{ $topicRequest->subject?->name ?? '-' }}</td>
                            <td>
                                {{ $topicRequest->academicYear?->name ?? '-' }}
                                @if($topicRequest->semester)
                                    <small class="d-block text-muted">{{ $topicRequest->semester->name }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $topicRequest->user?->name ?? 'N/A' }}</strong>
                                <small class="d-block text-muted">{{ $topicRequest->user?->email ?? '-' }}</small>
                            </td>
                            <td style="min-width: 240px; max-width: 320px;">
                                <details>
                                    <summary class="text-primary">View details</summary>
                                    <div class="small text-muted mt-2" style="white-space: pre-wrap;">{{ $topicRequest->description }}</div>
                                </details>
                            </td>
                            <td class="text-nowrap">
                                {{ $topicRequest->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}
                                @if($topicRequest->resolved_at)
                                    <small class="d-block text-muted">
                                        Resolved {{ $topicRequest->resolved_at->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    </small>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $topicRequest->status)) }}</span></td>
                            <td style="min-width: 280px;">
                                <form action="{{ route('admin.topic-requests.update', $topicRequest) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="visually-hidden" for="status-{{ $topicRequest->id }}">Status</label>
                                    <select class="form-select form-select-sm mb-2" id="status-{{ $topicRequest->id }}" name="status">
                                        <option value="pending" @selected($topicRequest->status === 'pending')>Pending</option>
                                        <option value="in_progress" @selected($topicRequest->status === 'in_progress')>In Progress</option>
                                        <option value="resolved" @selected($topicRequest->status === 'resolved')>Resolved</option>
                                    </select>
                                    <label class="form-label small fw-semibold mb-1" for="link-{{ $topicRequest->id }}">
                                        Topic Link / Response
                                    </label>
                                    <input
                                        type="url"
                                        class="form-control form-control-sm mb-2"
                                        id="link-{{ $topicRequest->id }}"
                                        name="topic_link"
                                        value="{{ $topicRequest->topic_link }}"
                                        placeholder="https://..."
                                        maxlength="2048"
                                        inputmode="url"
                                    >
                                    @if($topicRequest->admin_note)
                                        <small class="d-block text-muted mb-2">
                                            Existing admin note: {{ $topicRequest->admin_note }}
                                        </small>
                                    @endif
                                    <label class="visually-hidden" for="topic-{{ $topicRequest->id }}">Published topic</label>
                                    <select class="form-select form-select-sm mb-2" id="topic-{{ $topicRequest->id }}" name="topic_id">
                                        <option value="">No published topic linked</option>
                                        @if($topicRequest->possible_topic)
                                            <option value="{{ $topicRequest->possible_topic->id }}" @selected($topicRequest->topic_id === $topicRequest->possible_topic->id)>
                                                {{ $topicRequest->possible_topic->title }}
                                            </option>
                                        @endif
                                        @if($topicRequest->topic && !$topicRequest->possible_topic)
                                            <option value="{{ $topicRequest->topic->id }}" selected>{{ $topicRequest->topic->title }}</option>
                                        @endif
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Save Update</button>
                                </form>
                                @if($topicRequest->topic)
                                    <a class="btn btn-link btn-sm px-0" href="{{ route('topics.show', $topicRequest->topic->slug) }}" target="_blank" rel="noopener">
                                        View published topic
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">No topic requests found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($topicRequests->hasPages())
        <div class="mt-4">{{ $topicRequests->links() }}</div>
    @endif
@endsection
