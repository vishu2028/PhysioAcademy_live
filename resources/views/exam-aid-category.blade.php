@extends('layouts.frontend')

@section('title', $category->name)

@push('styles')
    <style>
        .eac-page {
            padding: 130px 20px 80px;
            min-height: 70vh;
        }

        .eac-container {
            width: min(1000px, 100%);
            margin: 0 auto;
        }

        .eac-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #004AAD;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .eac-title {
            margin-top: 14px;
            font-family: var(--font-display, 'Sora', sans-serif);
            font-size: clamp(2rem, 4.5vw, 3rem);
            color: #0f172a;
        }

        .eac-desc {
            margin-top: 10px;
            max-width: 700px;
            color: #526176;
            line-height: 1.7;
        }

        .eac-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .eac-chip {
            padding: 6px 16px;
            border-radius: 999px;
            border: 1px solid rgba(0, 74, 173, 0.18);
            background: #fff;
            color: #334155;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .eac-chip.active,
        .eac-chip:hover {
            background: #004AAD;
            border-color: #004AAD;
            color: #fff;
        }

        .eac-list {
            display: grid;
            gap: 14px;
            margin-top: 30px;
        }

        .eac-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 20px 24px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(0, 74, 173, 0.1);
            box-shadow: 0 12px 34px rgba(0, 74, 173, 0.07);
        }

        .eac-item strong {
            display: block;
            color: #0f172a;
            font-size: 1.02rem;
        }

        .eac-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: 0.8rem;
        }

        .eac-type {
            display: inline-block;
            margin-right: 8px;
            padding: 2px 10px;
            border-radius: 999px;
            background: rgba(0, 74, 173, 0.08);
            color: #004AAD;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .eac-note {
            margin-top: 12px;
            color: #334155;
            line-height: 1.7;
        }

        .eac-action {
            flex-shrink: 0;
        }

        .eac-btn {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 10px;
            background: #004AAD;
            color: #fff;
            font-size: 0.88rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .eac-btn:hover {
            color: #fff;
            opacity: 0.9;
        }

        .eac-btn.eac-btn-light {
            background: rgba(0, 74, 173, 0.08);
            color: #004AAD;
        }

        .eac-empty {
            margin-top: 30px;
            padding: 50px 20px;
            text-align: center;
            color: #64748b;
            border: 1px dashed rgba(0, 74, 173, 0.25);
            border-radius: 18px;
        }

        @media (max-width: 640px) {
            .eac-item {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <main class="eac-page">
        <div class="eac-container">
            <a href="{{ route('exam-aid') }}" class="eac-back">
                <i class="bi bi-arrow-left"></i> Back to Exam Aid
            </a>

            <h1 class="eac-title">{{ $category->name }}</h1>

            @if($category->description)
                <p class="eac-desc">{{ $category->description }}</p>
            @endif

            @if($categories->count() > 1)
                <div class="eac-chips">
                    @foreach($categories as $item)
                        <a href="{{ route('exam-aid.category', $item->slug) }}"
                           class="eac-chip {{ $item->id === $category->id ? 'active' : '' }}">
                            {{ $item->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if($materials->count())
                <div class="eac-list">
                    @foreach($materials as $material)
                        <div class="eac-item">
                            <div>
                                <strong>{{ $material->title }}</strong>

                                <div class="eac-meta">
                                    <span class="eac-type">{{ strtoupper($material->type) }}</span>
                                    {{ $material->examAid->title }}
                                </div>

                                @auth
                                    @if($material->type === 'note' && $material->content)
                                        <div class="eac-note">{!! nl2br(e($material->content)) !!}</div>
                                    @endif
                                @endauth
                            </div>

                            <div class="eac-action">
                                @auth
                                    @if(in_array($material->type, ['pdf', 'document']) && $material->file_path)
                                        <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" rel="noopener"
                                           class="eac-btn">
                                            {{ $material->type === 'pdf' ? 'Open PDF' : 'Download' }}
                                        </a>
                                    @elseif($material->type === 'link' && $material->url)
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="eac-btn">
                                            Open Link
                                        </a>
                                    @elseif($material->type === 'video' && filter_var($material->content, FILTER_VALIDATE_URL))
                                        <a href="{{ $material->content }}" target="_blank" rel="noopener" class="eac-btn">
                                            Open Video
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="eac-btn eac-btn-light">Login to view</a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $materials->links() }}
                </div>
            @else
                <div class="eac-empty">No learning materials in this category yet.</div>
            @endif
        </div>
    </main>
@endsection
