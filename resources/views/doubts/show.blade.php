@extends('layouts.frontend')

@section('title', 'Doubt Conversation')

@section('content')
    <style>
        .doubt-conversation-page { padding-top: 120px; }
        .doubt-conversation-card { padding: clamp(22px, 4vw, 38px); border-radius: 26px; }
        .doubt-conversation-meta { display: flex; flex-wrap: wrap; gap: 10px 18px; color: #64748b; }
        .doubt-conversation-meta span { display: inline-flex; gap: 7px; align-items: center; }
        .doubt-conversation-timeline { display: grid; gap: 18px; margin: 30px 0; }
        .doubt-conversation-message { padding: 20px; border: 1px solid #e2e8f0; border-radius: 18px; background: #f8fafc; }
        .doubt-conversation-message.admin { border-color: #bbf7d0; background: #f0fdf4; }
        .doubt-conversation-message.student-follow-up { border-color: #bfdbfe; background: #eff6ff; }
        .doubt-conversation-message h3 { margin: 0 0 8px; font-size: 1rem; font-weight: 800; }
        .doubt-conversation-message time { color: #64748b; font-size: .85rem; }
        .doubt-conversation-body { margin-top: 14px; color: #334155; line-height: 1.75; overflow-wrap: anywhere; }
        .doubt-conversation-body img { max-width: 100%; height: auto; }
        .doubt-conversation-body table { width: 100%; border-collapse: collapse; }
        .doubt-conversation-body th, .doubt-conversation-body td { padding: 8px; border: 1px solid #cbd5e1; }
        .doubt-follow-up-form textarea { min-height: 130px; }
        @media (max-width: 768px) { .doubt-conversation-page { padding-top: 90px; } }
    </style>

    @php
        $statusLabel = in_array($doubt->status, ['rejected', 'closed'], true)
            ? 'Closed'
            : ucfirst(str_replace('_', ' ', $doubt->status));
        $badgeClass = match($doubt->status) {
            'answered' => 'success',
            'rejected', 'closed' => 'secondary',
            'in_progress' => 'info',
            default => 'warning',
        };
        $hasAdminMessages = $doubt->messages->contains(
            fn ($message) => $message->sender_role === \App\Models\DoubtMessage::ROLE_ADMIN
        );
    @endphp

    <section class="section doubt-conversation-page">
        <div class="section-container">
            <a href="{{ route('doubts.mine') }}" class="btn btn-outline-primary rounded-pill mb-4">
                <i class="bi bi-arrow-left me-1"></i> Back to My Doubts
            </a>

            @if(session('success'))
                <div class="alert alert-success rounded-4">{{ session('success') }}</div>
            @endif

            <article class="glass-card doubt-conversation-card">
                @if($doubt->status === \App\Models\Doubt::STATUS_ANSWERED)
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        @if($doubt->liked_by_user)
                            <form method="POST" action="{{ route('doubts.likes.destroy', $doubt) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-primary rounded-pill" type="submit" aria-pressed="true">
                                    <i class="bi bi-hand-thumbs-up-fill me-1" aria-hidden="true"></i>
                                    Helpful · {{ $doubt->likes_count }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('doubts.likes.store', $doubt) }}">
                                @csrf
                                <button class="btn btn-outline-primary rounded-pill" type="submit" aria-pressed="false">
                                    <i class="bi bi-hand-thumbs-up me-1" aria-hidden="true"></i>
                                    Helpful · {{ $doubt->likes_count }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <span class="section-tag">Ask Doubt Conversation</span>
                        <h1 class="section-title mb-3">{{ $doubt->topic ?: 'General Doubt' }}</h1>
                        <div class="doubt-conversation-meta">
                            <span><i class="bi bi-book"></i>{{ $doubt->subject?->name ?? 'Subject not specified' }}</span>
                            @if($doubt->academicYear)
                                <span><i class="bi bi-calendar3"></i>{{ $doubt->academicYear->name }}</span>
                            @endif
                            <span><i class="bi bi-clock"></i>Submitted {{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                            <span><i class="bi bi-arrow-repeat"></i>Updated {{ $doubt->updated_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                    <span class="badge bg-{{ $badgeClass }} rounded-pill px-3 py-2">{{ $statusLabel }}</span>
                </div>

                <div class="doubt-conversation-timeline" aria-label="Doubt conversation history">
                    <section class="doubt-conversation-message student-follow-up">
                        <h3>Your question</h3>
                        <time datetime="{{ $doubt->created_at?->toIso8601String() }}">
                            {{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                        </time>
                        <div class="doubt-conversation-body">{{ $doubt->message }}</div>
                    </section>

                    @forelse($doubt->messages as $message)
                        <section class="doubt-conversation-message {{ $message->sender_role === \App\Models\DoubtMessage::ROLE_ADMIN ? 'admin' : 'student-follow-up' }}">
                            <h3>
                                {{ $message->sender_role === \App\Models\DoubtMessage::ROLE_ADMIN
                                    ? ($message->sender?->name ?? 'Admin response')
                                    : 'Your follow-up' }}
                            </h3>
                            <time datetime="{{ $message->created_at?->toIso8601String() }}">
                                {{ $message->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                            </time>
                            <div class="doubt-conversation-body">
                                @if($message->sender_role === \App\Models\DoubtMessage::ROLE_ADMIN)
                                    {!! \App\Models\Doubt::sanitizeHtml($message->message) !!}
                                @else
                                    {{ $message->message }}
                                @endif
                            </div>
                        </section>
                    @empty
                        @if($doubt->answer)
                            <section class="doubt-conversation-message admin">
                                <h3>{{ $doubt->answeredBy?->name ?? 'Admin response' }}</h3>
                                <time datetime="{{ ($doubt->answered_at ?? $doubt->updated_at)?->toIso8601String() }}">
                                    {{ ($doubt->answered_at ?? $doubt->updated_at)?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </time>
                                <div class="doubt-conversation-body">{!! \App\Models\Doubt::sanitizeHtml($doubt->answer) !!}</div>
                            </section>
                        @endif
                    @endforelse
                </div>

                @if(in_array($doubt->status, [\App\Models\Doubt::STATUS_PENDING, \App\Models\Doubt::STATUS_IN_PROGRESS], true)
                    && ! $hasAdminMessages
                    && ! $doubt->answer)
                    <div class="alert alert-warning rounded-4">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Your doubt is awaiting an admin response.
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('doubts.messages.store', $doubt) }}"
                    class="doubt-follow-up-form border-top pt-4 mt-4"
                >
                    @csrf
                    <label class="form-label fw-bold" for="follow-up-message">Ask a follow-up question</label>
                    <textarea
                        class="form-control rounded-4 @error('message') is-invalid @enderror"
                        id="follow-up-message"
                        name="message"
                        maxlength="10000"
                        required
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                        <small class="text-muted">A follow-up reopens this conversation as Pending.</small>
                        <button class="btn btn-primary rounded-pill px-4" type="submit">Send Follow-up</button>
                    </div>
                </form>
            </article>
        </div>
    </section>
@endsection
