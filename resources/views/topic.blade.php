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
                        <button class="btn-share" type="button" title="Share Topic"
                                aria-label="Share this topic" aria-haspopup="dialog"
                                aria-controls="topicShareDialog" aria-expanded="false">
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
                            <span class="meta-value"> {{ $topic->study_time ?? 'Not specified' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="topic-share-dialog" id="topicShareDialog" role="dialog"
             aria-modal="true" aria-labelledby="topicShareTitle" hidden>
            <div class="topic-share-backdrop" data-share-close></div>
            <div class="topic-share-panel" role="document">
                <div class="topic-share-header">
                    <div>
                        <span class="topic-share-eyebrow">Share topic</span>
                        <h2 id="topicShareTitle">Share this topic</h2>
                    </div>
                    <button type="button" class="topic-share-close" data-share-close
                            aria-label="Close share dialog">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <p class="topic-share-description">Share this topic with a classmate or save the link for later.</p>
                <div class="topic-share-options">
                    <button type="button" class="topic-share-option" data-share-action="native">
                        <i class="bi bi-share"></i><span>Native Share</span>
                    </button>
                    <button type="button" class="topic-share-option" data-share-action="copy">
                        <i class="bi bi-link-45deg"></i><span>Copy Link</span>
                    </button>
                    <a class="topic-share-option" data-share-link="whatsapp" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-whatsapp"></i><span>WhatsApp</span>
                    </a>
                    <a class="topic-share-option" data-share-link="telegram" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-telegram"></i><span>Telegram</span>
                    </a>
                    <a class="topic-share-option" data-share-link="facebook" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-facebook"></i><span>Facebook</span>
                    </a>
                    <a class="topic-share-option" data-share-link="x" target="_blank" rel="noopener noreferrer">
                        <strong class="topic-share-x">X</strong><span>Post on X</span>
                    </a>
                    <a class="topic-share-option" data-share-link="email">
                        <i class="bi bi-envelope"></i><span>Email</span>
                    </a>
                </div>
                <p class="topic-share-feedback" id="topicShareFeedback" role="status" aria-live="polite"></p>
            </div>
        </div>

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
                                            <div class="content-overlay" style="background: linear-gradient(to bottom, transparent, #FFFFFF); height: 100px; margin-top: -100px; position: relative; z-index: 1;"></div>
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
                                                @elseif($material->type == 'document') <i class="bi bi-file-earmark-text"></i>
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
                                                    @if(in_array($material->type, ['pdf', 'document']))
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
                            @if(filled($topic->bottom_line))
                                <div class="content-section bottom-line-section">
                                    <div class="bottom-line-card">
                                        <div class="bottom-line-icon">
                                            <i class="bi bi-lightbulb-fill"></i>
                                        </div>

                                        <div class="bottom-line-content">
                <span class="bottom-line-label">
                    Key Takeaway
                </span>

                                            <h2 class="bottom-line-title">
                                                Bottom Line
                                            </h2>

                                            <div class="bottom-line-text">
                                                {!! $topic->bottom_line !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($topic->content_protection == '1')
                                <div class="protected-notice">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <span>This content is protected for authenticity and plagiarism control.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- SIDEBAR -->
                    {{-- <aside class="topic-sidebar">
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
                    </aside> --}}
                </div>
            </div>
        </section>
    </main>

    @push('styles')
        <style>
            /* Bottom Line / Key Takeaway */
            .topic-page .bottom-line-section {
                margin-top: 32px;
            }

            .topic-page .bottom-line-card {
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 20px;
                padding: 28px;
                overflow: hidden;
                border: 1px solid rgba(0, 74, 173, 0.18);
                border-radius: 24px;
                background:
                    linear-gradient(
                        135deg,
                        rgba(0, 74, 173, 0.08),
                        rgba(217, 217, 217, 0.18)
                    ),
                    #FFFFFF;
                box-shadow: 0 16px 40px rgba(0, 74, 173, 0.08);
            }

            .topic-page .bottom-line-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 5px;
                height: 100%;
                background: #004AAD;
            }

            .topic-page .bottom-line-icon {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
                background: #004AAD;
                color: #FFFFFF;
                font-size: 1.4rem;
                box-shadow: 0 10px 24px rgba(0, 74, 173, 0.20);
            }

            .topic-page .bottom-line-content {
                min-width: 0;
                flex: 1;
            }

            .topic-page .bottom-line-label {
                display: inline-block;
                margin-bottom: 5px;
                color: #004AAD;
                font-size: 0.75rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .topic-page .bottom-line-title {
                margin: 0 0 10px;
                color: #0F172A;
                font-size: 1.5rem;
                font-weight: 800;
                line-height: 1.25;
            }

            .topic-page .bottom-line-text {
                color: #475569;
                font-size: 1rem;
                line-height: 1.8;
                overflow-wrap: anywhere;
                white-space: pre-line;
            }
            /* ─── TOPIC DETAIL PAGE STYLES ────────────────────────── */
            :root {
                --topic-bg: #FFFFFF;
                --topic-blue: #004AAD;
                --topic-cyan: #004AAD;
                --topic-card: rgba(255,255,255,0.7);
            }

            .topic-page {
                min-height: 100vh;
                background: var(--topic-bg);
                font-family: var(--font-body);
                padding-top: 72px; /* Navbar height */
                overflow-x: hidden;
                box-sizing: border-box;
            }
            .topic-page *,
            .topic-page *::before,
            .topic-page *::after { box-sizing: border-box; }

            /* ─── HERO SECTION ────────────────────────────────── */
            .topic-hero {
                position: relative;
                padding: 80px 0 100px;
                background: linear-gradient(135deg, rgba(0, 74, 173, 0.06), rgba(0, 74, 173, 0.06));
                overflow: hidden;
                border-bottom: 1px solid rgba(0, 74, 173, 0.05);
            }

            .topic-hero-bg {
                position: absolute; inset: 0;
                z-index: 0;
            }
            .hero-orb {
                position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            }
            .orb-1 { width: 300px; height: 300px; background: rgba(0, 74, 173, 0.15); top: -100px; right: 10%; }
            .orb-2 { width: 250px; height: 250px; background: rgba(0, 74, 173, 0.15); bottom: -50px; left: -50px; }

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
                background: rgba(0, 74, 173, 0.08); border: 1px solid rgba(0, 74, 173, 0.15);
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
                background: white; border: 1px solid rgba(0, 74, 173, 0.1);
                display: grid; place-items: center; font-size: 1.2rem;
                color: var(--text-secondary); transition: all 0.3s;
            }
            .btn-share:hover, .btn-bookmark:hover, .btn-bookmark.active {
                background: var(--topic-blue); color: white; transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(0, 74, 173, 0.2);
            }
            .topic-share-dialog[hidden] { display: none; }
            .topic-share-dialog {
                position: fixed; inset: 0; z-index: 1100;
                display: grid; place-items: center; padding: 20px;
            }
            .topic-share-backdrop {
                position: absolute; inset: 0;
                background: rgba(15, 23, 42, 0.48);
                backdrop-filter: blur(4px);
            }
            .topic-share-panel {
                position: relative; width: min(100%, 520px); padding: 28px;
                border: 1px solid rgba(148, 163, 184, 0.24);
                border-radius: 24px; background: #fff;
                box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
            }
            .topic-share-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
            .topic-share-eyebrow {
                display: block; margin-bottom: 6px; color: var(--topic-blue);
                font-size: .75rem; font-weight: 800; letter-spacing: .08em;
                text-transform: uppercase;
            }
            .topic-share-header h2 { margin: 0; color: #0f172a; font-size: 1.45rem; }
            .topic-share-close {
                width: 36px; height: 36px; border: 0; border-radius: 10px;
                background: #f1f5f9; color: #475569; cursor: pointer;
            }
            .topic-share-description { margin: 14px 0 20px; color: #64748b; }
            .topic-share-options {
                display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px;
            }
            .topic-share-option {
                display: flex; align-items: center; gap: 10px; min-height: 48px;
                padding: 11px 13px; border: 1px solid #e2e8f0; border-radius: 12px;
                background: #fff; color: #1e293b; font: inherit; font-weight: 700;
                text-decoration: none; cursor: pointer; transition: .2s ease;
            }
            .topic-share-option:hover, .topic-share-option:focus-visible {
                border-color: var(--topic-blue); color: var(--topic-blue);
                background: #eff6ff; outline: none;
            }
            .topic-share-option i { width: 20px; color: var(--topic-blue); font-size: 1.1rem; text-align: center; }
            .topic-share-x { width: 20px; color: #0f172a; font-size: 1.1rem; text-align: center; }
            .topic-share-feedback { min-height: 21px; margin: 16px 0 0; color: #047857; font-size: .9rem; font-weight: 700; }
            body.topic-share-open { overflow: hidden; }
            @media (max-width: 480px) {
                .topic-share-panel { padding: 22px 18px; border-radius: 20px; }
                .topic-share-options { grid-template-columns: 1fr; }
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
            .topic-main-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 40px; align-items: start; }
            .topic-main-layout > * { min-width: 0; }

            .content-glass-card {
                background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.8));
                border: 1px solid rgba(255,255,255,0.8);
                border-radius: 36px; padding: 40px;
                box-shadow: 0 30px 60px rgba(0, 74, 173, 0.06);
            }

            .content-section + .content-section { margin-top: 60px; }
            .section-heading {
                font-family: var(--font-display); font-size: 1.5rem; font-weight: 800;
                margin-bottom: 24px; color: var(--text-primary);
                display: flex; align-items: center; gap: 12px;
            }
            .section-heading::after {
                content: ''; height: 2px; flex: 1;
                background: linear-gradient(90deg, rgba(0, 74, 173, 0.1), transparent);
            }

            .topic-long-desc {
                font-size: 1.15rem; line-height: 1.8; color: var(--text-secondary);
                min-width: 0;
                overflow-wrap: break-word;
                word-break: normal;
            }
            .topic-long-desc > * { max-width: 100%; }
            .topic-long-desc p,
            .topic-long-desc li,
            .topic-long-desc blockquote,
            .topic-long-desc h1,
            .topic-long-desc h2,
            .topic-long-desc h3,
            .topic-long-desc h4,
            .topic-long-desc h5,
            .topic-long-desc h6 {
                overflow-wrap: break-word;
                word-break: normal;
            }
            .topic-long-desc img {
                max-width: 100%; height: auto; border-radius: 16px; margin: 20px 0;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            .topic-long-desc video,
            .topic-long-desc iframe,
            .topic-long-desc audio {
                max-width: 100%;
            }
            .topic-long-desc a {
                overflow-wrap: break-word;
                word-break: normal;
            }
            .topic-long-desc pre {
                max-width: 100%;
                overflow-x: auto;
                white-space: pre-wrap;
                overflow-wrap: break-word;
            }
            .topic-table-scroll {
                width: 100%;
                max-width: 100%;
                margin: 20px 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
            }
            .topic-table-scroll table {
                width: 100%;
                min-width: 0;
                table-layout: auto;
                border-collapse: collapse;
            }
            .topic-table-scroll th,
            .topic-table-scroll td {
                min-width: 0;
                overflow-wrap: normal;
                word-break: normal;
                white-space: normal;
            }

            /* Materials */
            .materials-list { display: grid; gap: 16px; }
            .material-card {
                display: flex; align-items: center; gap: 20px;
                padding: 24px; border-radius: 24px; background: #FFFFFF;
                border: 1px solid #D9D9D9; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            }
            .material-card:hover {
                transform: scale(1.01) translateY(-4px);
                border-color: var(--topic-blue);
                box-shadow: 0 15px 40px rgba(0, 74, 173, 0.08);
                background: white;
            }

            .mc-icon {
                width: 60px; height: 60px; border-radius: 18px;
                display: grid; place-items: center; font-size: 1.8rem;
                flex-shrink: 0;
            }
            .type-pdf .mc-icon { background: rgba(239,68,68,0.08); color: #ef4444; }
            .type-video .mc-icon { background: rgba(0, 74, 173, 0.08); color: #004AAD; }
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
                padding: 10px 24px; background: white; border: 1.5px solid #D9D9D9;
                color: var(--text-secondary); border-radius: 14px; font-size: 0.85rem; font-weight: 700;
                transition: all 0.2s;
            }
            .btn-mc-secondary:hover { border-color: var(--topic-blue); color: var(--topic-blue); }

            .empty-materials {
                padding: 60px 40px; text-align: center; background: rgba(217,217,217,0.18);
                border: 2px dashed #D9D9D9; border-radius: 30px;
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
                background: white; border: 1px solid #D9D9D9;
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
                padding: 12px 0; border-bottom: 1px solid #D9D9D9;
            }
            .insight-item:last-child { border: none; }
            .ii-label { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }
            .ii-value { font-size: 0.9rem; font-weight: 800; color: #0f172a; }
            .val-med { color: var(--amber); }
            .val-high { color: #ef4444; }

            .sidebar-cta { margin-top: 24px; padding-top: 24px; border-top: 1px solid #D9D9D9; text-align: center; }
            .sidebar-cta p { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; }
            .btn-sidebar-primary {
                display: block; width: 100%; padding: 14px; background: var(--topic-blue);
                color: white; border-radius: 16px; font-weight: 700; font-size: 0.9rem;
                transition: all 0.2s;
            }
            .btn-sidebar-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0, 74, 173, 0.25); }

            .subtopics-nav { display: grid; gap: 10px; }
            .subnav-item {
                display: flex; align-items: center; gap: 12px;
                padding: 12px 16px; background: rgba(217,217,217,0.18); border-radius: 14px;
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
                width: 44px; height: 44px; background: rgba(217,217,217,0.18); border: 1px solid #D9D9D9;
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


            /* ─── PHYSIO SOURCE TOPIC PAGE BRAND OVERRIDES ─────────── */
            .topic-page .text-blue-600,
            .topic-page .text-blue-700,
            .topic-page .text-blue-900 {
                color: #004AAD !important;
            }

            .topic-page .border-blue-100 {
                border-color: rgba(0, 74, 173, 0.18) !important;
            }

            .topic-page .bg-blue-50\/50 {
                background: rgba(0, 74, 173, 0.06) !important;
            }

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


            /* ─── TOPIC DETAIL: RESPONSIVE OVERRIDES ───────────────────────── */
            @media (max-width: 1200px) {
                .topic-page .topic-main-layout {
                    grid-template-columns: minmax(0, 1fr) 310px;
                    gap: 28px;
                }
            }

            @media (max-width: 1024px) {
                .topic-page .topic-main-layout {
                    grid-template-columns: minmax(0, 1fr);
                }

                .topic-page .topic-sidebar {
                    min-width: 0;
                }

                .topic-page .sidebar-sticky {
                    position: static;
                }
            }

            @media (max-width: 768px) {
                .topic-page .bottom-line-card {
                    padding: 22px;
                    gap: 16px;
                }

                .topic-page .bottom-line-icon {
                    width: 48px;
                    height: 48px;
                    flex-basis: 48px;
                }
                .topic-page {
                    padding-top: 64px;
                    overflow-x: hidden;
                }

                .topic-page .topic-hero {
                    padding: 44px 0 72px;
                }

                .topic-page .topic-hero-container,
                .topic-page .topic-container {
                    padding-left: 16px;
                    padding-right: 16px;
                }

                .topic-page .topic-breadcrumb {
                    flex-wrap: wrap;
                    gap: 6px 8px;
                    margin-bottom: 24px;
                    font-size: 0.7rem;
                    line-height: 1.5;
                    overflow-wrap: anywhere;
                }

                .topic-page .bc-current {
                    max-width: 100%;
                    overflow-wrap: anywhere;
                }

                .topic-page .topic-header-flex {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 20px;
                    margin-bottom: 28px;
                }

                .topic-page .topic-title-wrap {
                    min-width: 0;
                    width: 100%;
                }

                .topic-page .topic-title {
                    font-size: clamp(2rem, 10vw, 2.8rem);
                    overflow-wrap: anywhere;
                }

                .topic-page .topic-actions {
                    align-self: flex-start;
                }

                .topic-page .topic-meta-grid {
                    grid-template-columns: 1fr;
                    gap: 12px;
                }

                .topic-page .meta-item {
                    min-width: 0;
                    padding: 14px 16px;
                    border-radius: 16px;
                }

                .topic-page .meta-info {
                    min-width: 0;
                }

                .topic-page .meta-value {
                    overflow-wrap: anywhere;
                }

                .topic-page .topic-content-section {
                    margin-top: -30px;
                    padding-bottom: 70px;
                }

                .topic-page .content-glass-card {
                    padding: 26px 20px;
                    border-radius: 26px;
                }

                .topic-page .content-section + .content-section {
                    margin-top: 42px;
                }

                .topic-page .section-heading {
                    font-size: 1.3rem;
                    gap: 10px;
                }

                .topic-page .section-heading::after {
                    min-width: 28px;
                }

                .topic-page .topic-long-desc {
                    font-size: 1rem;
                    line-height: 1.75;
                    overflow-wrap: break-word;
                    word-break: normal;
                }

                .topic-page .topic-long-desc img,
                .topic-page .topic-long-desc video,
                .topic-page .topic-long-desc iframe {
                    max-width: 100% !important;
                    height: auto;
                }

                .topic-page .topic-long-desc iframe {
                    width: 100%;
                    min-height: 220px;
                }

                .topic-page .topic-table-scroll {
                    margin: 16px 0;
                }

                .topic-page .topic-table-scroll table {
                    min-width: 560px;
                }

                .topic-page .topic-table-scroll th,
                .topic-page .topic-table-scroll td {
                    overflow-wrap: normal;
                    word-break: normal;
                }

                .topic-page .topic-long-desc pre,
                .topic-page .topic-long-desc code {
                    max-width: 100%;
                    overflow-x: auto;
                    white-space: pre-wrap;
                    overflow-wrap: break-word;
                }

                .topic-page .material-card {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 16px;
                }

                .topic-page .mc-body {
                    min-width: 0;
                    width: 100%;
                }

                .topic-page .mc-title,
                .topic-page .mc-note-preview {
                    overflow-wrap: anywhere;
                }

                .topic-page .mc-action {
                    width: 100%;
                    margin-top: 0;
                }

                .topic-page .btn-mc-primary,
                .topic-page .btn-mc-secondary {
                    width: 100%;
                    text-align: center;
                    justify-content: center;
                }

                .topic-page .sidebar-glass-card {
                    border-radius: 24px;
                }

                .topic-page .subnav-item,
                .topic-page .related-item {
                    min-width: 0;
                }

                .topic-page .subnav-item span,
                .topic-page .ri-title,
                .topic-page .ri-meta {
                    overflow-wrap: anywhere;
                }
            }

            @media (max-width: 480px) {
                .topic-page .bottom-line-card {
                    flex-direction: column;
                    padding: 20px 18px;
                    border-radius: 20px;
                }

                .topic-page .bottom-line-icon {
                    width: 44px;
                    height: 44px;
                    flex-basis: 44px;
                    border-radius: 13px;
                }

                .topic-page .bottom-line-title {
                    font-size: 1.3rem;
                }

                .topic-page .bottom-line-text {
                    font-size: 0.95rem;
                }
                .topic-page .topic-hero {
                    padding: 34px 0 62px;
                }

                .topic-page .topic-hero-container,
                .topic-page .topic-container {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .topic-page .topic-badge {
                    max-width: 100%;
                    white-space: normal;
                    line-height: 1.4;
                }

                .topic-page .topic-title {
                    font-size: clamp(1.8rem, 10vw, 2.35rem);
                }

                .topic-page .btn-share,
                .topic-page .btn-bookmark {
                    width: 42px;
                    height: 42px;
                    border-radius: 12px;
                }

                .topic-page .meta-item {
                    padding: 13px 14px;
                }

                .topic-page .content-glass-card {
                    padding: 22px 16px;
                    border-radius: 22px;
                }

                .topic-page .section-heading {
                    font-size: 1.18rem;
                }

                .topic-page .section-heading::after {
                    display: none;
                }

                .topic-page .sidebar-glass-card {
                    padding: 20px;
                    border-radius: 20px;
                }

                .topic-page .restricted-notice {
                    padding-left: 14px !important;
                    padding-right: 14px !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.topic-long-desc table').forEach(function (table) {
                    if (table.parentElement?.classList.contains('topic-table-scroll')) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.className = 'topic-table-scroll';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                });

                // Simple reveal observer
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, { threshold: 0 });

                document.querySelectorAll('.reveal-up, .reveal-right').forEach(el => observer.observe(el));

                const shareButton = document.querySelector('.btn-share');
                const shareDialog = document.getElementById('topicShareDialog');
                const shareFeedback = document.getElementById('topicShareFeedback');
                const nativeShareOption = document.querySelector('[data-share-action="native"]');
                const topicTitle = document.querySelector('.topic-title')?.textContent.trim()
                    || document.title;
                const topicUrl = window.location.href.split('#')[0];
                let lastFocusedElement;

                if (!shareButton || !shareDialog) {
                    return;
                }

                const shareText = `${topicTitle} - ${topicUrl}`;
                const encodedUrl = encodeURIComponent(topicUrl);
                const encodedText = encodeURIComponent(shareText);
                const encodedTitle = encodeURIComponent(topicTitle);

                const shareLinks = {
                    whatsapp: `https://wa.me/?text=${encodedText}`,
                    telegram: `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`,
                    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
                    x: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`,
                    email: `mailto:?subject=${encodedTitle}&body=${encodedText}`
                };

                document.querySelectorAll('[data-share-link]').forEach((link) => {
                    link.href = shareLinks[link.dataset.shareLink];
                    if (link.dataset.shareLink === 'email') {
                        link.removeAttribute('target');
                        link.removeAttribute('rel');
                    }
                });

                if (! navigator.share && nativeShareOption) {
                    nativeShareOption.hidden = true;
                }

                const showFeedback = (message) => {
                    shareFeedback.textContent = message;
                    window.setTimeout(() => {
                        shareFeedback.textContent = '';
                    }, 3000);
                };

                const copyToClipboard = async () => {
                    try {
                        if (navigator.clipboard?.writeText) {
                            await navigator.clipboard.writeText(topicUrl);
                        } else {
                            throw new Error('Clipboard API unavailable');
                        }
                    } catch {
                        const fallback = document.createElement('textarea');
                        fallback.value = topicUrl;
                        fallback.setAttribute('readonly', '');
                        fallback.style.position = 'fixed';
                        fallback.style.opacity = '0';
                        document.body.appendChild(fallback);
                        fallback.select();
                        const copied = document.execCommand('copy');
                        fallback.remove();

                        if (! copied) {
                            throw new Error('Copy command failed');
                        }
                    }

                    showFeedback('Link copied!');
                };

                const openShareDialog = () => {
                    lastFocusedElement = document.activeElement;
                    shareDialog.hidden = false;
                    shareButton.setAttribute('aria-expanded', 'true');
                    document.body.classList.add('topic-share-open');
                    shareDialog.querySelector('[data-share-close]')?.focus();
                };

                const closeShareDialog = () => {
                    shareDialog.hidden = true;
                    shareButton.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('topic-share-open');
                    lastFocusedElement?.focus();
                };

                shareButton.addEventListener('click', openShareDialog);
                shareDialog.querySelectorAll('[data-share-close]').forEach((element) => {
                    element.addEventListener('click', closeShareDialog);
                });
                shareDialog.addEventListener('click', (event) => {
                    if (event.target === shareDialog) {
                        closeShareDialog();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && ! shareDialog.hidden) {
                        closeShareDialog();
                    }
                });

                shareDialog.querySelector('[data-share-action="copy"]')?.addEventListener(
                    'click',
                    () => copyToClipboard().catch(() => showFeedback('Unable to copy link.'))
                );

                nativeShareOption?.addEventListener('click', async () => {
                    try {
                        await navigator.share({ title: topicTitle, text: topicTitle, url: topicUrl });
                        closeShareDialog();
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showFeedback('Native sharing is unavailable.');
                        }
                    }
                });
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
