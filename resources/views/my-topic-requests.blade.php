@extends('layouts.frontend')

@section('title', 'My Topic Requests')

@section('content')
    <style>
        .topic-requests-page { padding-top: 120px; }
        .topic-request-card { padding: 28px; border-radius: 24px; margin-bottom: 20px; }
        .topic-request-meta { display: flex; flex-wrap: wrap; gap: 12px; color: #64748b; font-size: 14px; font-weight: 600; }
        .topic-request-description { white-space: pre-wrap; color: #334155; line-height: 1.7; }
        .topic-request-status { border-radius: 999px; padding: 8px 14px; font-size: 13px; font-weight: 800; }
    </style>

    <section class="section topic-requests-page">
        <div class="section-container">
            <div class="mb-5">
                <span class="section-tag">Academic Support</span>
                <h1 class="section-title">My <span class="text-gradient">Topic Requests</span></h1>
                <p class="section-subtitle">Track suggestions you have submitted and see when they are reviewed.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4 mb-4">{{ session('success') }}</div>
            @endif

            @forelse($topicRequests as $topicRequest)
                @php
                    $badgeClass = match($topicRequest->status) {
                        'resolved' => 'success',
                        'in_progress' => 'info',
                        default => 'warning',
                    };
                    $safeTopicLink = $topicRequest->safe_topic_link;
                @endphp
                <article class="glass-card topic-request-card" id="topic-request-{{ $topicRequest->id }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div>
                            <h3 class="h4 fw-bold mb-2">{{ $topicRequest->topic_name }}</h3>
                            <div class="topic-request-meta">
                                <span><i class="bi bi-book me-1"></i>{{ $topicRequest->subject?->name ?? '-' }}</span>
                                <span><i class="bi bi-calendar3 me-1"></i>{{ $topicRequest->academicYear?->name ?? '-' }}</span>
                                @if($topicRequest->semester)
                                    <span><i class="bi bi-calendar2-week me-1"></i>{{ $topicRequest->semester->name }}</span>
                                @endif
                                <span><i class="bi bi-clock me-1"></i>{{ $topicRequest->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                @if($topicRequest->resolved_at)
                                    <span><i class="bi bi-check2-circle me-1"></i>Resolved {{ $topicRequest->resolved_at->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                @elseif($topicRequest->updated_at && $topicRequest->updated_at->ne($topicRequest->created_at))
                                    <span><i class="bi bi-arrow-repeat me-1"></i>Updated {{ $topicRequest->updated_at->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-{{ $badgeClass }} topic-request-status">{{ ucfirst(str_replace('_', ' ', $topicRequest->status)) }}</span>
                    </div>

                    <div class="topic-request-description mb-3">{{ $topicRequest->description }}</div>

                    @if($topicRequest->admin_note)
                        <div class="alert alert-light border mb-3">
                            <strong><i class="bi bi-chat-left-text me-1"></i> Admin note:</strong>
                            <div class="mt-1" style="white-space: pre-wrap;">{{ $topicRequest->admin_note }}</div>
                        </div>
                    @endif

                    @if($safeTopicLink)
                        <a href="{{ $safeTopicLink }}" class="btn btn-primary btn-sm text-decoration-none" target="_blank" rel="noopener noreferrer">
                            View Topic <i class="bi bi-arrow-up-right ms-1"></i>
                        </a>
                    @elseif($topicRequest->topic)
                        <a href="{{ route('topics.show', $topicRequest->topic->slug) }}" class="btn btn-primary btn-sm text-decoration-none">
                            View published topic <i class="bi bi-arrow-up-right ms-1"></i>
                        </a>
                    @endif
                </article>
            @empty
                <div class="glass-card p-5 text-center">
                    <h3 class="h5 fw-bold">No topic requests yet</h3>
                    <p class="text-secondary">Use the Request a Topic form on the homepage to suggest a subject.</p>
                    <a href="{{ route('home') }}#request-topic" class="btn btn-primary text-decoration-none">Request a Topic</a>
                </div>
            @endforelse

            @if($topicRequests->hasPages())
                <div class="mt-4">{{ $topicRequests->links() }}</div>
            @endif
        </div>
    </section>
@endsection
