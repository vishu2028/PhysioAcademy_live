@extends('layouts.frontend')

@section('title', 'My Doubts')

@section('content')
    <style>
        .my-doubts-page {
            padding-top: 120px;
        }

        .my-doubts-header {
            margin-bottom: 42px;
        }

        .my-doubts-subtitle-card {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            padding: 14px 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 18px 45px rgba(37, 99, 235, 0.10);
            color: #64748b;
            font-weight: 600;
            line-height: 1.5;
        }

        .my-doubts-subtitle-card i {
            color: #2563eb;
            font-size: 18px;
        }

        .doubt-card {
            padding: 34px;
            border-radius: 28px;
            margin-bottom: 28px;
        }

        .doubt-card-title {
            color: #0f172a;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .doubt-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        .doubt-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .doubt-status-badge {
            border-radius: 999px;
            padding: 9px 15px;
            font-size: 13px;
            font-weight: 800;
        }

        .doubt-content-box {
            padding: 20px 22px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            line-height: 1.7;
            font-size: 15px;
            word-break: break-word;
        }

        .admin-answer-box {
            padding: 22px 24px;
            border-radius: 20px;
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            border: 1px solid #bbf7d0;
            color: #14532d;
            word-break: break-word;
        }

        .admin-answer-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            color: #064e3b;
            font-weight: 800;
        }

        .admin-answer-text {
            margin-bottom: 14px;
            line-height: 1.75;
            font-size: 15px;
            color: #14532d;
        }

        .waiting-answer-box {
            padding: 22px 24px;
            border-radius: 20px;
            background: linear-gradient(135deg, #fffbeb, #fff7ed);
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .waiting-answer-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-weight: 800;
            color: #92400e;
        }

        .empty-doubts-card {
            padding: 56px;
            border-radius: 28px;
        }

        @media (max-width: 768px) {
            .my-doubts-page {
                padding-top: 90px;
            }

            .doubt-card {
                padding: 24px;
            }

            .my-doubts-subtitle-card {
                border-radius: 20px;
                align-items: flex-start;
            }
        }
    </style>

    <section class="section my-doubts-page">
        <div class="section-container">

            <div class="my-doubts-header">
                <span class="section-tag">Academic Support</span>

                <h1 class="section-title mb-0">
                    My <span class="text-gradient">Doubts</span>
                </h1>

                <div class="my-doubts-subtitle-card">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Track your submitted doubts, check their status, and read admin responses in one place.</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($doubts as $doubt)
                <div class="glass-card doubt-card">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h4 class="doubt-card-title">
                                {{ $doubt->topic ?: 'General Doubt' }}
                            </h4>

                            <div class="doubt-meta">
                            <span>
                                <i class="bi bi-calendar-check"></i>
                                {{ $doubt->academicYear?->name ?? '-' }}
                            </span>

                                <span>/</span>

                                <span>
                                <i class="bi bi-book"></i>
                                {{ $doubt->subject?->name ?? '-' }}
                            </span>

                                <span>/</span>

                                <span>
                                <i class="bi bi-clock"></i>
                                Submitted {{ $doubt->created_at?->format('d M Y h:i A') }}
                            </span>
                            </div>
                        </div>

                        @php
                            $badgeClass = match($doubt->status) {
                                'answered' => 'success',
                                'rejected' => 'danger',
                                'in_progress' => 'info',
                                default => 'warning',
                            };
                        @endphp

                        <span class="badge bg-{{ $badgeClass }} doubt-status-badge">
                        {{ ucfirst(str_replace('_', ' ', $doubt->status)) }}
                    </span>
                    </div>

                    <div class="mb-4">
                        <strong class="d-block mb-2">
                            <i class="bi bi-question-circle me-1"></i>
                            Your Doubt
                        </strong>

                        <div class="doubt-content-box">
                            {{ $doubt->message }}
                        </div>
                    </div>
                    <br>

                    @if($doubt->answer)
                        <div class="admin-answer-box">
                            <div class="admin-answer-title">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Admin Answer</span>
                            </div>

                            <p class="admin-answer-text">
                                {{ $doubt->answer }}
                            </p>

                            @if($doubt->answeredBy || $doubt->answered_at)
                                <small class="text-muted">
                                    Answered
                                    @if($doubt->answeredBy)
                                        by {{ $doubt->answeredBy?->name }}
                                    @endif

                                    @if($doubt->answered_at)
                                        on {{ $doubt->answered_at->format('d M Y h:i A') }}
                                    @endif
                                </small>
                            @endif
                        </div>
                    @else
                        <div class="waiting-answer-box">
                            <div class="waiting-answer-title">
                                <i class="bi bi-clock-history"></i>
                                <span>Waiting for Response</span>
                            </div>

                            <p class="mb-0">
                                Admin has not answered this doubt yet. Please check back later.
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="glass-card text-center empty-doubts-card">
                    <div class="mb-3" style="font-size: 42px;">📦</div>

                    <h3 class="fw-bold">No Doubts Found</h3>

                    <p class="text-muted mb-4">
                        You have not submitted any doubts yet.
                    </p>

                    <a href="{{ route('home') }}#ask-doubt" class="btn btn-primary rounded-pill px-4">
                        Submit Your First Doubt
                    </a>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $doubts->links() }}
            </div>
        </div>
    </section>
@endsection
