@extends('layouts.frontend')

@section('title', $topic->title . ' - ' . ($topic->subject->name ?? 'Physio Topic'))

@section('content')
<main class="topic-page">
    <!-- PAGE HERO -->
    <header class="topic-hero">
        <div class="topic-hero-bg">
            <div class="hero-orb orb-1"></div>
            <div class="hero-orb orb-2"></div>
        </div>

        <div class="topic-hero-container">
            <nav class="topic-breadcrumb reveal-up">
                <a href="{{ route('home') }}">Home</a>
                <span class="bc-sep">/</span>
                <a href="{{ route('topics.index') }}">Subjects</a>
                <span class="bc-sep">/</span>
                <a href="{{ route('topics.index') }}">{{ $topic->subject->name ?? 'General' }}</a>
                <span class="bc-sep">/</span>
                <span class="bc-current">{{ $topic->title }}</span>
            </nav>

            <div class="topic-header-flex reveal-up delay-1">
                <div class="topic-title-wrap">
                    <span class="topic-badge">{{ $topic->subject->name ?? 'Physiology Core' }}</span>
                    <h1 class="topic-title">{{ $topic->title }}</h1>
                </div>
                <div class="topic-actions">
                    <button class="btn-share" title="Share Topic">
                        <i class="bi bi-share"></i>
                    </button>
                    <button class="btn-bookmark {{ $topic->isBookmarked() ? 'active' : '' }}"
                            onclick="toggleBookmark({{ $topic->id }}, 'Topic', this)"
                            title="{{ $topic->isBookmarked() ? 'Remove from Saved' : 'Save for Later' }}">
                        <i class="bi {{ $topic->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                    </button>
                </div>
            </div>

            <div class="topic-meta-grid reveal-up delay-2">
                <div class="meta-item">
                    <div class="meta-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="meta-info">
                        <span class="meta-label">Academic Year</span>
                        <span class="meta-value">{{ $topic->academicYear->name ?? 'All Years' }}</span>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-icon"><i class="bi bi-layer-backward"></i></div>
                    <div class="meta-info">
                        <span class="meta-label">Unit</span>
                        <span class="meta-value">{{ $topic->module_number ?: 'General Mastery' }}</span>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="meta-info">
                        <span class="meta-label">Est. Study Time</span>
                        <span class="meta-value">45 – 60 Mins</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="topic-content-section">
        <div class="topic-container">
            <div class="topic-main-layout">
                <!-- MAIN CONTENT -->
                <div class="topic-main-body reveal-up">
                    <div class="content-glass-card">
                        <div class="content-section">
                            <h2 class="section-heading">Description & Overview</h2>
                            <div class="topic-long-desc">
                                @auth
                                    {!! $topic->description !!}
                                @else
                                    <div class="preview-content" style="position: relative;">
                                        {!! Str::limit($topic->description, strlen($topic->description) / 2) !!}
                                        <div class="content-overlay" style="background: linear-gradient(to bottom, transparent, #fff); height: 100px; margin-top: -100px; position: relative; z-index: 1;"></div>
                                        <div class="restricted-notice mt-4 px-4 py-3 rounded-4 border border-blue-100 bg-blue-50/50 text-center">
                                            <p class="mb-2 fw-bold text-blue-900">✨ Reading in Preview Mode (50%)</p>
                                            <p class="text-sm text-blue-700 mb-3">Join Physio Academy to unlock full detailed descriptions, diagrams, and clinical insights.</p>
                                            <a href="{{ route('login') }}" class="btn-sidebar-primary d-inline-block px-5 py-2 w-auto">Login to Continue</a>
                                        </div>
                                    </div>
                                @endauth
                            </div>
                        </div>

                        <div class="content-section">
                            <h2 class="section-heading">Learning Materials</h2>
                            <div class="materials-list">
                                @forelse($topic->materials as $material)
                                    <div class="material-card type-{{ $material->type }}">
                                        <div class="mc-icon">
                                            @if($material->type == 'pdf') <i class="bi bi-file-earmark-pdf"></i>
                                            @elseif($material->type == 'video') <i class="bi bi-play-btn"></i>
                                            @elseif($material->type == 'link') <i class="bi bi-link-45deg"></i>
                                            @else <i class="bi bi-journal-text"></i> @endif
                                        </div>
                                        <div class="mc-body">
                                            <div class="flex items-center justify-between">
                                                <h4 class="mc-title">{{ $material->title }}</h4>
                                                <button onclick="toggleBookmark({{ $material->id }}, 'LearningMaterial', this)"
                                                        class="material-bookmark-btn {{ $material->isBookmarked() ? 'text-blue-600' : 'text-slate-300' }}"
                                                        title="Bookmark Resource">
                                                    <i class="bi {{ $material->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                                </button>
                                            </div>
                                            <span class="mc-tag">{{ strtoupper($material->type) }}</span>

                                            @if($material->type == 'note' && $material->content)
                                                <div class="mc-note-preview">
                                                    {!! nl2br(e($material->content)) !!}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mc-action">
                                            @auth
                                                @if($material->type == 'pdf')
                                                    <a href="{{ route('materials.download', $material->id) }}" target="_blank" class="btn-mc-primary">Download</a>
                                                @elseif($material->type == 'video')
                                                    <a href="{{ $material->url }}" target="_blank" class="btn-mc-primary">Watch Now</a>
                                                @elseif($material->type == 'link')
                                                    <a href="{{ $material->url }}" target="_blank" class="btn-mc-secondary">Open Link</a>
                                                @endif
                                            @else
                                                <button onclick="window.location.href='{{ route('login') }}'" class="btn-mc-primary flex items-center gap-2">
                                                    <i class="bi bi-lock-fill"></i> Unlock
                                                </button>
                                            @endauth
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-materials">
                                        <div class="em-icon">📦</div>
                                        <h4>Syncing Resources...</h4>
                                        <p>Detailed notes, case studies, and clinical videos are being uploaded for this module.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($topic->content_protection == '1')
                        <div class="protected-notice">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>This content is protected for authenticity and plagiarism control.</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="topic-sidebar">
                    <div class="sidebar-sticky">
                        <!-- TOPIC INSIGHTS -->
                        <div class="sidebar-glass-card reveal-right">
                            <h3 class="sidebar-title">Topic Insights</h3>
                            <div class="insight-list">
                                <div class="insight-item">
                                    <span class="ii-label">Total Resources</span>
                                    <span class="ii-value">{{ $topic->materials->count() }} Files</span>
                                </div>
                                <div class="insight-item">
                                    <span class="ii-label">Complexity</span>
                                    <span class="ii-value val-med">Moderate</span>
                                </div>
                                <div class="insight-item">
                                    <span class="ii-label">Exam Frequency</span>
                                    <span class="ii-value val-high">High</span>
                                </div>
                            </div>
                            <div class="sidebar-cta">
                                <p>Having trouble understanding this?</p>
                                <a href="{{ route('search') }}#ask-doubt" class="btn-sidebar-primary">Ask a Doubt</a>
                            </div>
                        </div>

                        <!-- SUBTOPICS -->
                        @if($topic->subtopics->count() > 0)
                        <div class="sidebar-glass-card reveal-right delay-1" style="margin-top: 24px;">
                            <h3 class="sidebar-title">Masterclass Subtopics</h3>
                            <div class="subtopics-nav">
                                @foreach($topic->subtopics as $sub)
                                    <a href="{{ route('topics.show', ['slug' => $sub->slug]) }}" class="subnav-item">
                                        <div class="sni-dot"></div>
                                        <span>{{ $sub->title }}</span>
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- RELATED TOPICS -->
                        @if($relatedTopics->count() > 0)
                        <div class="sidebar-glass-card reveal-right delay-2" style="margin-top: 24px;">
                            <h3 class="sidebar-title">Keep Exploring</h3>
                            <div class="related-list">
                                @foreach($relatedTopics as $rel)
                                    <a href="{{ route('topics.show', ['slug' => $rel->slug]) }}" class="related-item">
                                        <div class="ri-icon">{{ $rel->subject->icon ?: '📚' }}</div>
                                        <div class="ri-body">
                                            <span class="ri-title">{{ $rel->title }}</span>
                                            <span class="ri-meta">{{ $rel->module_number ?: 'Advanced clinical' }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                             <a href="{{ route('topics.index') }}" class="btn-all-topics mt-3">Browse Subject <i class="bi bi-arrow-right"></i></a>
                        </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>

@push('styles')
<style>
    /* ─── TOPIC DETAIL PAGE STYLES ────────────────────────── */
    :root {
        --topic-bg: #f8fbff;
        --topic-blue: #2563eb;
        --topic-cyan: #38bdf8;
        --topic-card: rgba(255,255,255,0.7);
    }

    .topic-page {
        min-height: 100vh;
        background: var(--topic-bg);
        font-family: var(--font-body);
        padding-top: 72px; /* Navbar height */
    }

    /* ─── HERO SECTION ────────────────────────────────── */
    .topic-hero {
        position: relative;
        padding: 80px 0 100px;
        background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(56,189,248,0.06));
        overflow: hidden;
        border-bottom: 1px solid rgba(37,99,235,0.05);
    }

    .topic-hero-bg {
        position: absolute; inset: 0;
        z-index: 0;
    }
    .hero-orb {
        position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
    }
    .orb-1 { width: 300px; height: 300px; background: rgba(37,99,235,0.15); top: -100px; right: 10%; }
    .orb-2 { width: 250px; height: 250px; background: rgba(56,189,248,0.15); bottom: -50px; left: -50px; }

    .topic-hero-container {
        position: relative; z-index: 1;
        max-width: 1280px; margin: 0 auto; padding: 0 24px;
    }

    .topic-breadcrumb {
        display: flex; align-items: center; gap: 10px;
        font-size: 0.8rem; font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.1em;
        margin-bottom: 32px;
    }
    .topic-breadcrumb a { transition: color 0.2s; }
    .topic-breadcrumb a:hover { color: var(--topic-blue); }
    .bc-sep { opacity: 0.3; }
    .bc-current { color: var(--topic-blue); }

    .topic-header-flex {
        display: flex; justify-content: space-between; align-items: flex-end;
        gap: 30px; margin-bottom: 40px;
    }
    .topic-badge {
        display: inline-block; padding: 6px 16px;
        background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.15);
        border-radius: 99px; color: var(--topic-blue); font-size: 0.75rem;
        font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
        margin-bottom: 12px;
    }
    .topic-title {
        font-family: var(--font-display);
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 800; color: var(--text-primary);
        line-height: 1.1; letter-spacing: -0.02em;
        margin: 0;
    }
    .topic-actions { display: flex; gap: 12px; }
    .btn-share, .btn-bookmark {
        width: 46px; height: 46px; border-radius: 14px;
        background: white; border: 1px solid rgba(37,99,235,0.1);
        display: grid; place-items: center; font-size: 1.2rem;
        color: var(--text-secondary); transition: all 0.3s;
    }
    .btn-share:hover, .btn-bookmark:hover, .btn-bookmark.active {
        background: var(--topic-blue); color: white; transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(37,99,235,0.2);
    }
    .btn-bookmark.active i::before { content: "\f199"; } /* bi-bookmark-fill */
    .material-bookmark-btn { background: none; border: none; padding: 5px; cursor: pointer; transition: all 0.2s; }
    .material-bookmark-btn:hover { transform: scale(1.2); }

    .topic-meta-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    .meta-item {
        display: flex; align-items: center; gap: 15px;
        background: rgba(255,255,255,0.5); padding: 16px 20px;
        border-radius: 20px; border: 1px solid rgba(255,255,255,0.5);
        backdrop-filter: blur(10px);
    }
    .meta-icon {
        width: 40px; height: 40px; border-radius: 12px;
        background: white; color: var(--topic-blue);
        display: grid; place-items: center; font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .meta-label { display: block; font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; }
    .meta-value { display: block; font-size: 0.95rem; font-weight: 800; color: var(--text-primary); }

    /* ─── MAIN CONTENT ────────────────────────────────── */
    .topic-content-section { padding-bottom: 100px; margin-top: -40px; position: relative; z-index: 2; }
    .topic-container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
    .topic-main-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; align-items: start; }

    .content-glass-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.8));
        border: 1px solid rgba(255,255,255,0.8);
        border-radius: 36px; padding: 40px;
        box-shadow: 0 30px 60px rgba(37,99,235,0.06);
    }

    .content-section + .content-section { margin-top: 60px; }
    .section-heading {
        font-family: var(--font-display); font-size: 1.5rem; font-weight: 800;
        margin-bottom: 24px; color: var(--text-primary);
        display: flex; align-items: center; gap: 12px;
    }
    .section-heading::after {
        content: ''; height: 2px; flex: 1;
        background: linear-gradient(90deg, rgba(37,99,235,0.1), transparent);
    }

    .topic-long-desc {
        font-size: 1.15rem; line-height: 1.8; color: var(--text-secondary);
    }
    .topic-long-desc img {
        max-width: 100%; height: auto; border-radius: 16px; margin: 20px 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Materials */
    .materials-list { display: grid; gap: 16px; }
    .material-card {
        display: flex; align-items: center; gap: 20px;
        padding: 24px; border-radius: 24px; background: #fbfdff;
        border: 1px solid #f1f5f9; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .material-card:hover {
        transform: scale(1.01) translateY(-4px);
        border-color: var(--topic-blue);
        box-shadow: 0 15px 40px rgba(37,99,235,0.08);
        background: white;
    }

    .mc-icon {
        width: 60px; height: 60px; border-radius: 18px;
        display: grid; place-items: center; font-size: 1.8rem;
        flex-shrink: 0;
    }
    .type-pdf .mc-icon { background: rgba(239,68,68,0.08); color: #ef4444; }
    .type-video .mc-icon { background: rgba(37,99,235,0.08); color: #2563eb; }
    .type-link .mc-icon { background: rgba(16,185,129,0.08); color: #10b981; }
    .type-note .mc-icon { background: rgba(245,158,11,0.08); color: #f59e0b; }

    .mc-body { flex: 1; }
    .mc-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .mc-tag { font-size: 0.65rem; font-weight: 800; color: var(--text-muted); letter-spacing: 0.1em; }
    .mc-note-preview {
        margin-top: 15px; padding: 15px; background: #fffbeb; border: 1px solid rgba(245,158,11,0.1);
        border-radius: 14px; font-size: 0.9rem; color: #92400e; line-height: 1.6;
    }

    .btn-mc-primary {
        padding: 10px 24px; background: var(--text-primary); color: white;
        border-radius: 14px; font-size: 0.85rem; font-weight: 700;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-mc-primary:hover { background: var(--topic-blue); transform: translateY(-2px); }
    .btn-mc-secondary {
        padding: 10px 24px; background: white; border: 1.5px solid #e2e8f0;
        color: var(--text-secondary); border-radius: 14px; font-size: 0.85rem; font-weight: 700;
        transition: all 0.2s;
    }
    .btn-mc-secondary:hover { border-color: var(--topic-blue); color: var(--topic-blue); }

    .empty-materials {
        padding: 60px 40px; text-align: center; background: #f8fbff;
        border: 2px dashed #e2e8f0; border-radius: 30px;
    }
    .em-icon { font-size: 3rem; margin-bottom: 16px; }
    .em-icon + h4 { font-family: var(--font-display); font-weight: 800; color: var(--text-primary); }
    .em-icon + h4 + p { color: var(--text-muted); font-size: 0.95rem; }

    .protected-notice {
        margin-top: 40px; padding: 20px; background: #fef2f2; border: 1px solid rgba(239,68,68,0.1);
        border-radius: 20px; display: flex; align-items: center; gap: 15px;
        color: #b91c1c; font-size: 0.85rem; font-weight: 600;
    }
    .protected-notice i { font-size: 1.2rem; }

    /* ─── SIDEBAR ─────────────────────────────────────── */
    .sidebar-sticky { position: sticky; top: 100px; }
    .sidebar-glass-card {
        background: white; border: 1px solid #f1f5f9;
        padding: 30px; border-radius: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }
    .sidebar-title {
        font-family: var(--font-display); font-size: 1.15rem; font-weight: 800;
        color: var(--text-primary); margin-bottom: 24px;
        display: flex; align-items: center; gap: 10px;
    }
    .sidebar-title::before { content: ''; width: 4px; height: 20px; background: var(--topic-blue); border-radius: 4px; }

    .insight-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-bottom: 1px solid #f1f5f9;
    }
    .insight-item:last-child { border: none; }
    .ii-label { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }
    .ii-value { font-size: 0.9rem; font-weight: 800; color: #0f172a; }
    .val-med { color: var(--amber); }
    .val-high { color: #ef4444; }

    .sidebar-cta { margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9; text-align: center; }
    .sidebar-cta p { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; }
    .btn-sidebar-primary {
        display: block; width: 100%; padding: 14px; background: var(--topic-blue);
        color: white; border-radius: 16px; font-weight: 700; font-size: 0.9rem;
        transition: all 0.2s;
    }
    .btn-sidebar-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(37,99,235,0.25); }

    .subtopics-nav { display: grid; gap: 10px; }
    .subnav-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; background: #f8fbff; border-radius: 14px;
        font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);
        transition: all 0.2s;
    }
    .subnav-item:hover { background: var(--topic-blue); color: white; transform: translateX(5px); }
    .subnav-item i { margin-left: auto; font-size: 0.8rem; opacity: 0.5; }
    .sni-dot { width: 6px; height: 6px; background: currentColor; border-radius: 50%; opacity: 0.4; }

    .related-list { display: grid; gap: 16px; }
    .related-item {
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; transition: all 0.2s;
    }
    .related-item:hover { transform: scale(1.02); }
    .ri-icon {
        width: 44px; height: 44px; background: #f8fbff; border: 1px solid #edf2f7;
        border-radius: 12px; display: grid; place-items: center; font-size: 1.2rem;
    }
    .ri-title { display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .ri-meta { display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-top: 2px; }

    .btn-all-topics {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px; font-size: 0.8rem; font-weight: 800; color: var(--topic-blue);
        text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.2s;
    }
    .btn-all-topics:hover { gap: 12px; }

    /* ─── RESPONSIVE ────────────────────────────────── */
    @media (max-width: 1024px) {
        .topic-main-layout { grid-template-columns: 1fr; }
        .sidebar-sticky { position: static; }
    }
    @media (max-width: 768px) {
        .content-glass-card { padding: 30px 20px; border-radius: 28px; }
        .topic-header-flex { flex-direction: column; align-items: flex-start; }
        .material-card { flex-direction: column; align-items: flex-start; }
        .mc-action { width: 100%; margin-top: 15px; }
        .btn-mc-primary { width: 100%; text-align: center; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple reveal observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal-up, .reveal-right').forEach(el => observer.observe(el));
    });

    function toggleBookmark(id, type, btn) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif

        const icon = btn.querySelector('i');

        fetch("{{ route('bookmarks.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id, type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.isBookmarked) {
                icon.classList.remove('bi-bookmark');
                icon.classList.add('bi-bookmark-fill');
                btn.classList.add('active');
                if (type === 'LearningMaterial') btn.classList.add('text-blue-600');
            } else {
                icon.classList.remove('bi-bookmark-fill');
                icon.classList.add('bi-bookmark');
                btn.classList.remove('active');
                if (type === 'LearningMaterial') {
                    btn.classList.remove('text-blue-600');
                    btn.classList.add('text-slate-300');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endpush
@endsection
