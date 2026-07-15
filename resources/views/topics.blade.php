@extends('layouts.frontend')

@section('title', 'Search by Subjects')

@section('content')
    <div class="topics-subj-page">
        <x-page-hero
            title="Syllabus by Subject"
            subtitle="Complete physiotherapy curriculum organized by core subjects, units, and clinical units."
            breadcrumbLabel="Subjects"
        >
            <x-slot name="icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </x-slot>
        </x-page-hero>

        <!-- STICKY SEARCH BAR -->
        <div class="tspage-sticky-search" id="stickySearch">
            <div class="tspage-search-wrapper">
                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    style="color: #64748b; flex-shrink: 0;"
                >
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>

                <input type="text" id="topicSearch" placeholder="Search any topic, subject or unit...">
                {{--                <div class="tspage-search-count" id="searchCount">Browse all units</div>--}}
            </div>
        </div>

        <!-- SUBJECTS LIST -->
        <section class="tspage-list-section">
            <div class="tspage-list-inner">
                <div class="tspage-requested-box">
                    <span class="tspage-hot-badge">🔥 Most Requested</span>

                    <div class="tspage-requested-topics">
                        @forelse($requestedTags as $tag)
                            <a href="{{ route('search', ['query' => $tag]) }}">
                                {{ $tag }}
                            </a>
                        @empty
                            <span class="text-muted small">No requested topics yet.</span>
                        @endforelse
                    </div>
                </div>

                <div class="typage-syllabus-grid" id="subjectAccordions">
                    @php
                        $visibleSubjects = auth()->check()
                            ? $subjects
                            : $subjects->take(ceil($subjects->count() / 2));
                    @endphp

                    @foreach($visibleSubjects as $subject)
                        @php
                            $subjectModulesCount = $subject->units->sum(function ($unit) {
                                return $unit->unitTopics->sum(function ($unitTopic) {
                                    return $unitTopic->lmsTopics->count();
                                });
                            });
                        @endphp

                        <div class="typage-subject-panel">
                            <div class="typage-subject-head">
                                <div>
                                    <h3 class="typage-subject-title">
                                        {{ $subject->name }}
                                    </h3>

                                    @if(!empty($subject->code))
                                        <div class="typage-subject-code-wrap">
                                            <span class="typage-subject-code-label">Subject Code</span>
                                            <span class="typage-subject-code-value">{{ $subject->code }}</span>
                                        </div>
                                    @endif

                                    <p>
                                        {{ $subject->units->count() }} active units
                                        •
                                        {{ $subjectModulesCount }} active units in this subject
                                    </p>
                                </div>

                                <div class="typage-subject-icon">
                                    {{ $subject->icon ?: '📚' }}
                                </div>
                            </div>

                            <div class="typage-unit-select-wrap">
                                <label class="typage-unit-label">
                                    Select Unit
                                </label>

                                <select class="typage-unit-select" data-subject-id="{{ $subject->id }}">
                                    @forelse($subject->units as $unit)
                                        @php
                                            $unitTopicCount = $unit->unitTopics->sum(function ($unitTopic) {
                                                return $unitTopic->lmsTopics->count();
                                            });
                                        @endphp

                                        <option value="unit-panel-{{ $subject->id }}-{{ $unit->id }}">
                                            {{ $unit->name }} ({{ $unitTopicCount }} topics)
                                        </option>
                                    @empty
                                        <option value="">
                                            No units available
                                        </option>
                                    @endforelse
                                </select>
                            </div>

                            @forelse($subject->units as $unit)
                                @php
                                    $unitItems = $unit->unitTopics->flatMap(function ($unitTopic) use ($unit) {
                                        return $unitTopic->lmsTopics->map(function ($lmsTopic) use ($unitTopic, $unit) {
                                            $topicTitle = trim($lmsTopic->title ?? '');

                                            if ($topicTitle === '') {
                                                $topicTitle = trim($unitTopic->title ?? '');
                                            }

                                            if ($topicTitle === '') {
                                                $topicTitle = 'Untitled Topic';
                                            }

                                            $lmsTopic->frontend_topic_title = $topicTitle;
                                            $lmsTopic->frontend_unit_name = $unit->name ?? 'Unit';

                                            return $lmsTopic;
                                        });
                                    });

                                    $visibleItems = $unitItems;
                                @endphp

                                <div
                                    id="unit-panel-{{ $subject->id }}-{{ $unit->id }}"
                                    class="typage-unit-topic-panel {{ !$loop->first ? 'd-none' : '' }}"
                                    data-subject-id="{{ $subject->id }}"
                                    data-unit-name="{{ $unit->name }}"
                                >
                                    <div class="typage-directory-list">
                                        @forelse($visibleItems as $item)
                                            <div class="typage-directory-row">
                                                <a href="{{ route('topics.show', ['slug' => $item->slug]) }}" class="typage-directory-item">
                                                <span class="typage-topic-title">
                                                    {{ $item->frontend_topic_title }}
                                                </span>

                                                    <i class="bi bi-chevron-right"></i>
                                                </a>

                                                <button
                                                    type="button"
                                                    onclick="toggleBookmark({{ $item->id }}, 'Topic', this)"
                                                    class="typage-list-bookmark {{ $item->isBookmarked() ? 'active' : '' }}"
                                                    title="Bookmark Topic"
                                                >
                                                    <i class="bi {{ $item->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                                </button>
                                            </div>

                                            @if($item->subtopics->count() > 0)
                                                <div class="typage-subtopic-directory">
                                                    @foreach($item->subtopics as $sub)
                                                        <div class="typage-directory-row typage-subtopic-row">
                                                            <a href="{{ route('topics.show', ['slug' => $sub->slug]) }}" class="typage-directory-item">
                                                            <span class="typage-topic-title">
                                                                {{ $sub->title }}
                                                            </span>

                                                                <i class="bi bi-chevron-right"></i>
                                                            </a>

                                                            <button
                                                                type="button"
                                                                onclick="toggleBookmark({{ $sub->id }}, 'Topic', this)"
                                                                class="typage-list-bookmark {{ $sub->isBookmarked() ? 'active' : '' }}"
                                                                title="Bookmark Subtopic"
                                                            >
                                                                <i class="bi {{ $sub->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @empty
                                            <div class="typage-empty-item">
                                                No topics available in this unit.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="typage-empty-item">
                                    No units available in this subject.
                                </div>
                            @endforelse
                        </div>
                    @endforeach

                    @guest
                        <div class="typage-subject-panel text-center">
                            <div class="mb-3">
                                <i class="bi bi-book-half display-5 text-muted opacity-50"></i>
                            </div>

                            <h3 class="fw-bold mb-2">Unlock More Subjects</h3>

                            <p class="text-muted mb-4">
                                You are currently viewing a limited selection of subjects.
                                Sign in to unlock the full curriculum.
                            </p>

                            <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-5 py-2">
                                Join the Academy
                            </a>
                        </div>
                    @endguest
                </div>

                <!-- REQUEST TOPIC CTA -->
                <div class="tspage-request-cta">
                    <div class="tspage-cta-glow"></div>

                    <div class="tspage-cta-content">
                        <h2>Missing a specific topic?</h2>

                        <p>
                            Our academic team is constantly adding new clinical and theoretical units.
                            Let us know what you're looking for!
                        </p>

                        <a href="{{ route('search') }}">
                            Request Topic Now
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            .typage-subject-title {
                margin-bottom: 8px;
            }

            .typage-subject-code-wrap {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 10px;
                padding: 5px 10px;
                background: rgba(217,217,217,0.20);
                border: 1px solid #D9D9D9;
                border-radius: 999px;
            }

            .typage-subject-code-label {
                font-size: 11px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.4px;
            }

            .typage-subject-code-value {
                font-size: 13px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1;
            }
            /* ─── BY SUBJECT PAGE — FULLY SCOPED ────────────────── */
            .topics-subj-page {
                background: #FFFFFF;
                min-height: 100vh;
                font-family: 'Outfit', sans-serif;
                padding-top: 72px;
            }

            /* Sticky Search */
            .tspage-sticky-search {
                position: sticky;
                top: 0;
                z-index: 100;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                padding: 15px 24px;
                border-bottom: 1px solid #D9D9D9;
            }

            .tspage-search-wrapper {
                max-width: 1100px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                gap: 15px;
                background: white;
                border: 1px solid #D9D9D9;
                padding: 12px 24px;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
                transition: all 0.3s;
            }

            .tspage-search-wrapper:focus-within {
                border-color: #004AAD;
                box-shadow: 0 0 0 4px rgba(0, 74, 173, 0.1);
            }

            .tspage-search-wrapper input {
                flex: 1;
                border: none;
                outline: none;
                font-size: 1.1rem;
                color: #0f172a;
                font-family: 'Outfit', sans-serif;
                background: transparent;
                min-width: 0;
            }

            .tspage-search-count {
                font-size: 0.75rem;
                font-weight: 800;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-family: 'Sora', sans-serif;
                white-space: nowrap;
            }

            /* Content */
            .tspage-list-section {
                padding: 40px 24px 100px;
            }

            .tspage-list-inner {
                max-width: 1200px;
                margin: 0 auto;
            }

            .tspage-requested-box {
                background: rgba(0, 74, 173, 0.04);
                border: 1px solid rgba(0, 74, 173, 0.1);
                border-radius: 20px;
                padding: 20px;
                margin-bottom: 40px;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .tspage-hot-badge {
                font-size: 0.75rem;
                font-weight: 800;
                color: #004AAD;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-family: 'Sora', sans-serif;
                white-space: nowrap;
            }

            .tspage-requested-topics {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .tspage-requested-topics a {
                text-decoration: none;
                background: white;
                border: 1px solid rgba(0, 74, 173, 0.15);
                color: #004AAD;
                padding: 6px 14px;
                border-radius: 10px;
                font-weight: 700;
                font-size: 0.85rem;
                transition: all 0.2s;
            }

            .tspage-requested-topics a:hover {
                background: #004AAD;
                color: white;
                transform: translateY(-2px);
            }

            /* Subject Cards */
            .typage-syllabus-grid {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 24px !important;
                align-items: start !important;
                margin-bottom: 60px;
            }

            .typage-subject-panel {
                background: white;
                border: 1px solid #D9D9D9;
                border-radius: 28px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.03);
                padding: 24px;
                transition: all 0.3s;
                min-width: 0;
            }

            .typage-subject-panel:hover {
                border-color: rgba(0, 74, 173, 0.2);
                transform: translateY(-4px);
            }

            .typage-subject-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 22px;
            }

            .typage-subject-head h3 {
                font-family: 'Sora', sans-serif;
                font-size: 1.35rem;
                font-weight: 800;
                color: #0f172a;
                margin: 0;
            }

            .typage-subject-head p {
                color: #64748b;
                font-size: 0.9rem;
                margin: 6px 0 0;
                line-height: 1.5;
            }

            .typage-subject-icon {
                width: 48px;
                height: 48px;
                border-radius: 16px;
                background: rgba(0, 74, 173, 0.06);
                display: grid;
                place-items: center;
                font-size: 1.4rem;
                flex-shrink: 0;
            }

            /* Unit Dropdown */
            .typage-unit-select-wrap {
                margin-bottom: 20px;
            }

            .typage-unit-label {
                display: block;
                font-size: 0.75rem;
                font-weight: 900;
                color: #0f172a;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }

            .typage-unit-select {
                width: 100%;
                border: 1px solid #D9D9D9;
                background: rgba(217,217,217,0.22);
                color: #004AAD;
                padding: 14px 16px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 800;
                outline: none;
                cursor: pointer;
            }

            .typage-unit-select:focus {
                border-color: #004AAD;
                box-shadow: 0 0 0 3px rgba(0, 74, 173, 0.12);
            }

            /* Topic Cards */
            .typage-unit-topic-panel {
                margin-top: 12px;
            }

            .typage-directory-list {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }

            .typage-directory-row {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 44px !important;
                gap: 8px !important;
                align-items: stretch !important;
                min-width: 0 !important;
            }

            .typage-directory-item {
                min-width: 0 !important;
                width: 100% !important;
                border: 0 !important;
                background: rgba(217,217,217,0.22) !important;
                color: #004AAD !important;
                padding: 15px 16px !important;
                border-radius: 4px !important;
                font-size: 14px !important;
                font-weight: 800 !important;
                text-decoration: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 10px !important;
            }

            .typage-topic-title {
                display: inline-block !important;
                color: #004AAD !important;
                font-size: 14px !important;
                font-weight: 800 !important;
                line-height: 1.3 !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            .typage-directory-item:hover .typage-topic-title {
                color: #004AAD !important;
            }

            .typage-directory-item i {
                color: #004AAD !important;
                flex-shrink: 0 !important;
            }

            .typage-list-bookmark {
                width: 44px !important;
                min-width: 44px !important;
                border: 0 !important;
                border-radius: 8px !important;
                background: rgba(217,217,217,0.30) !important;
                color: #64748b !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
            }

            .typage-list-bookmark.active {
                background: rgba(0,74,173,0.10) !important;
                color: #004AAD !important;
            }

            .typage-subtopic-directory {
                margin-left: 12px;
                padding-left: 12px;
                border-left: 2px dashed #D9D9D9;
                display: flex;
                flex-direction: column;
                gap: 10px;
                display:none;
            }
            .typage-unit-topic-panel {
                margin-top: 12px;
                height: 151px;
                overflow: auto;
            }
            .typage-subtopic-row .typage-directory-item {
                background: rgba(217,217,217,0.16) !important;
                font-size: 13px !important;
                padding: 12px 14px !important;
            }

            .typage-empty-item {
                background: rgba(217,217,217,0.16);
                color: #64748b;
                padding: 15px 16px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 700;
            }

            .d-none {
                display: none !important;
            }

            .tspage-search-hidden {
                display: none !important;
            }

            .tspage-searching .typage-unit-select-wrap {
                opacity: 0.55;
            }

            /* CTA */
            .tspage-request-cta {
                padding: 60px 40px;
                border-radius: 40px;
                background: #004AAD;
                color: #FFFFFF;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .tspage-cta-glow {
                position: absolute;
                top: -50px;
                right: -50px;
                width: 200px;
                height: 200px;
                background: #FFFFFF;
                border-radius: 50%;
                opacity: 0.14;
                filter: blur(60px);
            }

            .tspage-cta-content {
                position: relative;
                z-index: 1;
            }

            .tspage-cta-content h2 {
                font-family: 'Sora', sans-serif;
                font-size: 2.2rem;
                font-weight: 900;
                margin: 0 0 12px;
                letter-spacing: -0.02em;
            }

            .tspage-cta-content p {
                color: #94a3b8;
                font-size: 1.1rem;
                max-width: 600px;
                margin: 0 auto 32px;
                line-height: 1.6;
            }

            .tspage-cta-content a {
                display: inline-block;
                padding: 14px 36px;
                background: #FFFFFF;
                color: #004AAD;
                border-radius: 16px;
                text-decoration: none;
                font-weight: 800;
                font-family: 'Sora', sans-serif;
                box-shadow: 0 10px 30px rgba(0, 74, 173, 0.3);
                transition: all 0.2s;
            }

            .tspage-cta-content a:hover {
                transform: translateY(-2px);
                box-shadow: 0 14px 40px rgba(0, 74, 173, 0.4);
            }
            body .typage-directory-list {
                display: flex !important;
                /* grid-template-columns: repeat(2, minmax(0, 1fr)) !important; */
                gap: 12px !important;
                flex-wrap: wrap;
            }
            .typage-directory-row {
                display: flex;
                align-items: stretch;
                gap: 8px;
                min-width: 0;
                width:100%;
            }

            /* ─── PHYSIO SOURCE SUBJECT TOPICS BRAND OVERRIDES ────── */
            .topics-subj-page .btn-primary {
                background-color: #004AAD !important;
                border-color: #004AAD !important;
            }

            .topics-subj-page .btn-outline-primary {
                color: #004AAD !important;
                border-color: #004AAD !important;
            }

            .topics-subj-page .btn-outline-primary:hover {
                background-color: #004AAD !important;
                border-color: #004AAD !important;
                color: #FFFFFF !important;
            }

            /* Responsive */
            @media (max-width: 1400px) {
                .typage-syllabus-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }

            @media (max-width: 768px) {
                .tspage-search-wrapper {
                    padding: 10px 16px;
                }

                .tspage-search-count {
                    display: none;
                }

                .tspage-requested-box {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .typage-syllabus-grid {
                    grid-template-columns: 1fr !important;
                }

                .typage-directory-list {
                    grid-template-columns: 1fr !important;
                }

                .typage-subject-panel {
                    padding: 20px;
                }

                .typage-subject-head h3 {
                    font-size: 1.2rem;
                }

                .tspage-request-cta {
                    padding: 40px 24px;
                }

                .tspage-cta-content h2 {
                    font-size: 1.6rem;
                }
            }


            /* ─── SUBJECT TOPICS PAGE: RESPONSIVE OVERRIDES ─────────────── */
            @media (max-width: 1200px) {
                .topics-subj-page .typage-syllabus-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    gap: 20px !important;
                }
            }

            @media (max-width: 768px) {
                .topics-subj-page {
                    padding-top: 64px;
                    overflow-x: hidden;
                }

                .topics-subj-page .tspage-sticky-search {
                    top: 64px;
                    padding: 10px 14px;
                }

                .topics-subj-page .tspage-search-wrapper {
                    gap: 10px;
                    padding: 10px 14px;
                    border-radius: 16px;
                }

                .topics-subj-page .tspage-search-wrapper input {
                    width: 100%;
                    min-width: 0;
                    font-size: 16px;
                }

                .topics-subj-page .tspage-list-section {
                    padding: 28px 16px 72px;
                }

                .topics-subj-page .tspage-requested-box {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 14px;
                    padding: 18px;
                    margin-bottom: 28px;
                    border-radius: 18px;
                }

                .topics-subj-page .tspage-requested-topics {
                    width: 100%;
                    gap: 8px;
                }

                .topics-subj-page .tspage-requested-topics a {
                    max-width: 100%;
                    overflow-wrap: anywhere;
                }

                .topics-subj-page .typage-syllabus-grid {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                    margin-bottom: 42px;
                }

                .topics-subj-page .typage-subject-panel {
                    min-width: 0;
                    padding: 20px;
                    border-radius: 22px;
                }

                .topics-subj-page .typage-subject-head {
                    gap: 12px;
                    margin-bottom: 18px;
                }

                .topics-subj-page .typage-subject-head > div:first-child {
                    min-width: 0;
                }

                .topics-subj-page .typage-subject-title,
                .topics-subj-page .typage-subject-head p {
                    overflow-wrap: anywhere;
                }

                .topics-subj-page .typage-subject-icon {
                    width: 44px;
                    height: 44px;
                    border-radius: 14px;
                    flex: 0 0 44px;
                }

                .topics-subj-page .typage-subject-code-wrap {
                    max-width: 100%;
                    flex-wrap: wrap;
                }

                .topics-subj-page .typage-unit-select {
                    min-width: 0;
                    font-size: 16px;
                    padding: 13px 14px;
                }

                .topics-subj-page .typage-unit-topic-panel {
                    height: auto;
                    max-height: 300px;
                    overflow-y: auto;
                    overflow-x: hidden;
                    -webkit-overflow-scrolling: touch;
                }

                .topics-subj-page .typage-directory-list {
                    grid-template-columns: 1fr !important;
                    width: 100%;
                }

                .topics-subj-page .typage-directory-row {
                    width: 100%;
                    min-width: 0;
                }

                .topics-subj-page .typage-directory-item {
                    min-width: 0 !important;
                    padding: 13px 14px !important;
                }

                .topics-subj-page .typage-topic-title {
                    white-space: normal !important;
                    overflow: visible !important;
                    text-overflow: clip !important;
                    overflow-wrap: anywhere !important;
                }

                .topics-subj-page .typage-subtopic-directory {
                    margin-left: 6px;
                    padding-left: 8px;
                }

                .topics-subj-page .tspage-request-cta {
                    padding: 38px 22px;
                    border-radius: 26px;
                }

                .topics-subj-page .tspage-cta-content h2 {
                    font-size: clamp(1.55rem, 8vw, 2rem);
                    overflow-wrap: anywhere;
                }

                .topics-subj-page .tspage-cta-content p {
                    font-size: 1rem;
                }
            }

            @media (max-width: 480px) {
                .topics-subj-page .tspage-sticky-search {
                    padding-left: 10px;
                    padding-right: 10px;
                }

                .topics-subj-page .tspage-list-section {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .topics-subj-page .typage-subject-panel {
                    padding: 16px;
                    border-radius: 18px;
                }

                .topics-subj-page .typage-subject-head h3 {
                    font-size: 1.1rem;
                }

                .topics-subj-page .typage-subject-icon {
                    width: 40px;
                    height: 40px;
                    flex-basis: 40px;
                }

                .topics-subj-page .typage-directory-row {
                    gap: 6px !important;
                }

                .topics-subj-page .typage-list-bookmark {
                    width: 40px !important;
                    min-width: 40px !important;
                }

                .topics-subj-page .tspage-request-cta {
                    padding: 32px 18px;
                }

                .topics-subj-page .tspage-cta-content a {
                    width: 100%;
                    padding: 13px 18px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const topicSearch = document.getElementById('topicSearch');
                const searchCount = document.getElementById('searchCount');

                function normalizeText(value) {
                    return (value || '').toString().trim().toLowerCase();
                }

                function getSubjectSearchText(subjectPanel) {
                    const subjectTitle = subjectPanel.querySelector('.typage-subject-title')?.textContent || '';
                    const subjectCode = subjectPanel.querySelector('.typage-subject-code-value')?.textContent || '';

                    return normalizeText(subjectTitle + ' ' + subjectCode);
                }

                function showSelectedUnitOnly(subjectPanel) {
                    const select = subjectPanel.querySelector('.typage-unit-select');
                    const unitPanels = subjectPanel.querySelectorAll('.typage-unit-topic-panel');

                    if (!unitPanels.length) {
                        return;
                    }

                    unitPanels.forEach(function (unitPanel, index) {
                        const shouldShow = select && select.value
                            ? unitPanel.id === select.value
                            : index === 0;

                        unitPanel.classList.toggle('d-none', !shouldShow);
                        unitPanel.classList.remove('tspage-search-hidden');
                    });
                }

                function resetRows(subjectPanel) {
                    subjectPanel.querySelectorAll('.typage-directory-row').forEach(function (row) {
                        row.classList.remove('tspage-search-hidden');
                    });

                    subjectPanel.querySelectorAll('.typage-subtopic-directory').forEach(function (subtopicDirectory) {
                        subtopicDirectory.classList.remove('tspage-search-hidden');
                    });

                    subjectPanel.querySelectorAll('.typage-empty-item').forEach(function (emptyItem) {
                        emptyItem.classList.remove('tspage-search-hidden');
                    });
                }

                function resetSearch() {
                    document.querySelectorAll('.topics-subj-page').forEach(function (page) {
                        page.classList.remove('tspage-searching');
                    });

                    document.querySelectorAll('.typage-subject-panel').forEach(function (subjectPanel) {
                        subjectPanel.classList.remove('tspage-search-hidden');
                        resetRows(subjectPanel);
                        showSelectedUnitOnly(subjectPanel);
                    });

                    if (searchCount) {
                        searchCount.textContent = 'Browse all units';
                    }
                }

                function handleUnitChange(select) {
                    const subjectPanel = select.closest('.typage-subject-panel');

                    if (!subjectPanel) {
                        return;
                    }

                    if (topicSearch && topicSearch.value.trim() !== '') {
                        topicSearch.dispatchEvent(new Event('input'));
                        return;
                    }

                    showSelectedUnitOnly(subjectPanel);
                }

                document.querySelectorAll('.typage-unit-select').forEach(function (select) {
                    select.addEventListener('change', function () {
                        handleUnitChange(this);
                    });
                });

                if (!topicSearch) {
                    resetSearch();
                    return;
                }

                topicSearch.addEventListener('input', function (event) {
                    const query = normalizeText(event.target.value);
                    let found = 0;

                    if (query === '') {
                        resetSearch();
                        return;
                    }

                    document.querySelectorAll('.topics-subj-page').forEach(function (page) {
                        page.classList.add('tspage-searching');
                    });

                    document.querySelectorAll('.typage-subject-panel').forEach(function (subjectPanel) {
                        let subjectHasMatch = false;
                        const subjectMatches = getSubjectSearchText(subjectPanel).includes(query);
                        const unitPanels = subjectPanel.querySelectorAll('.typage-unit-topic-panel');

                        resetRows(subjectPanel);

                        if (!unitPanels.length) {
                            const panelText = normalizeText(subjectPanel.textContent);
                            const panelMatches = panelText.includes(query);

                            subjectPanel.classList.toggle('tspage-search-hidden', !panelMatches);

                            if (panelMatches) {
                                found++;
                            }

                            return;
                        }

                        unitPanels.forEach(function (unitPanel) {
                            let unitHasMatch = false;
                            const unitName = normalizeText(unitPanel.dataset.unitName);
                            const unitMatches = unitName.includes(query);

                            unitPanel.querySelectorAll('.typage-directory-row').forEach(function (row) {
                                const rowMatches = normalizeText(row.textContent).includes(query);
                                const shouldShow = subjectMatches || unitMatches || rowMatches;

                                row.classList.toggle('tspage-search-hidden', !shouldShow);

                                if (shouldShow) {
                                    unitHasMatch = true;
                                    subjectHasMatch = true;
                                    found++;
                                }
                            });

                            unitPanel.querySelectorAll('.typage-subtopic-directory').forEach(function (subtopicDirectory) {
                                const hasVisibleSubtopic = subtopicDirectory.querySelector(
                                    '.typage-directory-row:not(.tspage-search-hidden)'
                                );

                                subtopicDirectory.classList.toggle('tspage-search-hidden', !hasVisibleSubtopic);
                            });

                            unitPanel.querySelectorAll('.typage-empty-item').forEach(function (emptyItem) {
                                emptyItem.classList.toggle('tspage-search-hidden', true);
                            });

                            unitPanel.classList.toggle('d-none', !unitHasMatch);
                        });

                        subjectPanel.classList.toggle('tspage-search-hidden', !subjectHasMatch);
                    });

                    if (searchCount) {
                        searchCount.textContent = found > 0
                            ? `${found} results found`
                            : 'No results found';
                    }
                });

                resetSearch();
            });
        </script>
    @endpush
@endsection
