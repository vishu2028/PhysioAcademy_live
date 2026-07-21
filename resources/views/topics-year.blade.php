@extends('layouts.frontend')

@section('title', 'Search by Year')

@section('content')
    <div class="topics-year-page">
        <x-page-hero
            title="Syllabus by Year"
            subtitle="Navigate the physiotherapy curriculum year-by-year with core modules and clinical focus areas."
            breadcrumbLabel="Syllabus"
        >
            <x-slot name="icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </x-slot>
        </x-page-hero>

        <!-- YEAR CARDS -->
        @if($years->count() > 0)
            <section class="typage-cards-section">
                <div class="typage-cards-inner">
                    <div class="typage-section-label">Academic Foundations</div>
                    <div class="typage-cards-grid">
                        @foreach($years as $y)
                            <a href="{{ route('topics.year', ['year' => $y->slug]) }}" class="typage-card {{ ($currentYear && $currentYear->id == $y->id) ? 'active' : '' }}">
                                {{--          <div class="typage-year-number text-uppercase">{{ Str::limit($y->name, 2, '') }}</div>--}}
                                <div class="typage-year-label">{{ $y->name }}</div>
                                <p class="typage-year-desc">{{ $y->description }}</p>
                                <div class="typage-year-info">
                                    <div class="typage-info-item">
                                        <span class="typage-info-label">Units</span>
                                        <span class="typage-info-value">{{ $y->units_count }}</span>
                                    </div>
                                    <div class="typage-info-item">
                                        <span class="typage-info-label">Topics</span>
                                        <span class="typage-info-value">{{ $y->topics_count }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- SUBJECTS EXPLORER -->
        @if($currentYear)

            <section class="typage-explorer">
                <div class="typage-explorer-inner">
                    <div class="typage-hero-card">
                        <div class="typage-hero-copy">
                            <div class="typage-chip">{{ $currentYear->name }} Curriculum</div>

                            <h2 class="typage-hero-title">
                                {{ $currentYear->name }} <br>Academic Syllabus
                            </h2>

                            <p class="typage-hero-subtitle">
                                {{ $currentYear->description ?: 'Comprehensive coverage of core subjects including theory, practical applications, and exam preparation guides for '.$currentYear->name.' students.' }}
                            </p>

                            <div class="typage-hero-stats">
                                <div class="typage-stat">
                                    <strong>{{ $subjectCount ?? 0 }}</strong>
                                    <span>Subjects</span>
                                </div>

                                <div class="typage-stat">
                                    <strong>{{ $topicCount ?? 0 }}</strong>
                                    <span>Topics</span>
                                </div>
                            </div>
                        </div>

                        <div class="typage-hero-visual">
                            <div class="typage-body-map"></div>
                            <div class="typage-label-pill one">Skeletal System</div>
                            <div class="typage-label-pill two">Muscular Flow</div>
                            <div class="typage-label-pill three">Nervous Supply</div>
                        </div>
                    </div>

                    @php
                        $curriculumSubjects = $curriculumSubjects ?? collect();
                    @endphp

                    <div class="typage-syllabus-grid">
                        @php
                            $visibleSubjects = auth()->check()
                                ? $curriculumSubjects
                                : $curriculumSubjects->take(ceil($curriculumSubjects->count() / 2));
                        @endphp

                        @forelse($visibleSubjects as $subject)
                            @php
                                $unitCount = $subject->units->count();

                                $totalCount = $subject->units->sum(function ($unit) {
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
                                        <p>{{ $unitCount }} active units • {{ $totalCount }} active topics</p>
                                    </div>


                                    <span class="typage-subject-icon">📚</span>
                                </div>

                                @if($subject->units->count() > 0)
                                    <div class="typage-unit-select-wrap">
                                        <label class="typage-unit-label">Select Unit</label>

                                        <select class="typage-unit-select" data-subject-id="{{ $subject->id }}">
                                            @foreach($subject->units as $unit)
                                                @php
                                                    $unitTopicCount = $unit->unitTopics->sum(function ($unitTopic) {
                                                        return $unitTopic->lmsTopics->count();
                                                    });
                                                @endphp

                                                <option value="unit-panel-{{ $subject->id }}-{{ $unit->id }}">
                                                    {{ $unit->name }} ({{ $unitTopicCount }} topics)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @foreach($subject->units as $unit)
                                        @php
                                            $unitItems = $unit->unitTopics->flatMap(function ($unitTopic) {
                                                return $unitTopic->lmsTopics->map(function ($lmsTopic) use ($unitTopic) {
                                                    $topicTitle = trim($lmsTopic->title ?? '');

                                                    if ($topicTitle === '') {
                                                        $topicTitle = trim($unitTopic->title ?? '');
                                                    }

                                                    if ($topicTitle === '') {
                                                        $topicTitle = 'Untitled Topic';
                                                    }

                                                    $lmsTopic->frontend_topic_title = $topicTitle;

                                                    return $lmsTopic;
                                                });
                                            });

                                            /*
                                                Important:
                                                Ab hum guest/user dono ke liye selected unit ke tamam topics show kar rahe hain.
                                                Agar guest lock wapas chahiye ho to baad me add kar denge.
                                            */
                                            $visibleItems = $unitItems;
                                        @endphp

                                        <div
                                            id="unit-panel-{{ $subject->id }}-{{ $unit->id }}"
                                            class="typage-unit-topic-panel {{ !$loop->first ? 'd-none' : '' }}"
                                            data-subject-id="{{ $subject->id }}"
                                        >
                                            <div class="typage-directory-list">
                                                @forelse($visibleItems as $item)
                                                    <div class="typage-directory-row">
                                                        <a href="{{ route('topics.show', ['slug' => $item->slug]) }}" class="typage-directory-item">
                                                            <span class="typage-topic-title">{{ $item->frontend_topic_title }}</span>
                                                            <i class="bi bi-chevron-right"></i>
                                                        </a>

                                                        <button
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
                                                                        <span class="typage-topic-title">{{ $sub->title }}</span>
                                                                        <i class="bi bi-chevron-right"></i>
                                                                    </a>

                                                                    <button
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
                                    @endforeach
                                @else
                                    <div class="typage-empty-item">
                                        No units available in this subject.
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="typage-empty-panel">
                                <div class="typage-empty-icon">📦</div>
                                <h3>Work in Progress</h3>
                                <p>We're currently cataloging topics for this year. Check back soon or request a specific topic if you need it faster.</p>
                                <a href="{{ route('search') }}">Request a Topic</a>
                            </div>
                        @endforelse
                    </div>

                    @guest
                        <div class="typage-subject-panel p-5 text-center mt-4" style="background: linear-gradient(135deg, #FFFFFF, #FFFFFF); border: 2px dashed #D9D9D9;">
                            <div class="mb-4">
                                <i class="bi bi-shield-lock display-4 text-blue-600"></i>
                            </div>

                            <h3 class="fw-bold mb-3">Academic Access Restricted</h3>

                            <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
                                We have <strong>{{ $topicCount ?? 0 }}</strong> topics for {{ $currentYear->name }} available.
                                Login now to unlock the full syllabus, download clinical notes, and access viva prep materials.
                            </p>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">Student Login</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary rounded-pill px-5 py-2 fw-bold">Register Free</a>
                            </div>
                        </div>
                    @endguest
                </div>
            </section>

        @else

            <section class="typage-explorer">
                <div class="typage-explorer-inner">
                    <div class="typage-empty-panel text-center">
                        <div class="typage-empty-icon">📦</div>
                        <h3>No Academic Years Found</h3>
                        <p>No academic years are available at the moment. Please add an academic year from the admin panel.</p>
                    </div>
                </div>
            </section>

        @endif
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
            /* FINAL OVERRIDE: subject cards 3 per row */
            .typage-syllabus-grid {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 24px !important;
                align-items: start !important;
            }

            /* Subject card */
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

            /* Unit dropdown */
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

            /* Topic cards: 3 per line inside subject */
            .typage-unit-topic-panel {
                margin-top: 12px;
            }

            .typage-unit-topic-panel {
                margin-top: 12px;
                height: 151px;
                overflow: auto;
            }

            /*.typage-directory-list {*/
            /*    display: grid !important;*/
            /*    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;*/
            /*    gap: 12px !important;*/
            /*}*/
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

            .typage-directory-item {
                flex: 1;
                min-width: 0;
                border: 0;
                background: rgba(217,217,217,0.22);
                color: #004AAD;
                padding: 15px 16px;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 800;
                text-decoration: none;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                transition: 0.2s ease;
            }

            .typage-directory-item span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .typage-directory-item i {
                flex-shrink: 0;
                font-size: 14px;
            }

            .typage-directory-item:hover {
                background: rgba(0,74,173,0.08);
                color: #004AAD;
            }

            .typage-list-bookmark {
                width: 44px;
                min-width: 44px;
                height: auto;
                border: 0;
                border-radius: 8px;
                background: rgba(217,217,217,0.30);
                color: #64748b;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                cursor: pointer;
                transition: 0.2s ease;
            }

            .typage-list-bookmark:hover {
                background: #D9D9D9;
                color: #004AAD;
            }

            .typage-list-bookmark.active {
                background: rgba(0,74,173,0.10);
                color: #004AAD;
            }

            .typage-subtopic-directory {
                margin-left: 12px;
                padding-left: 12px;
                border-left: 2px dashed #D9D9D9;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .typage-subtopic-row .typage-directory-item {
                background: rgba(217,217,217,0.16);
                font-size: 13px;
                padding: 12px 14px;
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


            /* ─── PHYSIO SOURCE YEAR TOPICS BRAND OVERRIDES ───────── */
            .topics-year-page .btn-primary {
                background-color: #004AAD !important;
                border-color: #004AAD !important;
            }

            .topics-year-page .btn-outline-primary {
                color: #004AAD !important;
                border-color: #004AAD !important;
            }

            .topics-year-page .btn-outline-primary:hover {
                background-color: #004AAD !important;
                border-color: #004AAD !important;
                color: #FFFFFF !important;
            }

            .topics-year-page .text-blue-600 {
                color: #004AAD !important;
            }

            /* Responsive */
            @media (max-width: 1400px) {
                .typage-syllabus-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }

                /*.typage-directory-list {*/
                /*    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;*/
                /*}*/
            }

            @media (max-width: 768px) {
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
            }
            /* ─── BY YEAR PAGE — FULLY SCOPED ───────────────────── */
            .topics-year-page {
                background: #FFFFFF;
                min-height: 100vh;
                font-family: 'Outfit', sans-serif;
                padding-top: 72px;
            }

            /* Year Cards */
            .typage-cards-section { padding: 60px 24px; }
            .typage-cards-inner { max-width: 1200px; margin: 0 auto; }

            .typage-section-label {
                font-size: 0.8rem;
                font-weight: 800;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.15em;
                margin-bottom: 24px;
            }

            .typage-cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 20px;
            }

            .typage-card {
                background: white;
                border: 1px solid #D9D9D9;
                border-radius: 24px;
                padding: 40px 24px;
                text-align: center;
                transition: all 0.3s;
                box-shadow: 0 10px 25px rgba(0,0,0,0.02);
                text-decoration: none;
                display: block;
            }
            .typage-card:hover {
                transform: translateY(-8px);
                border-color: rgba(0, 74, 173, 0.3);
                box-shadow: 0 20px 40px rgba(0, 74, 173, 0.08);
            }
            .typage-card.active {
                border-color: #004AAD;
                background: rgba(0, 74, 173, 0.02);
                box-shadow: 0 20px 40px rgba(0, 74, 173, 0.1);
            }

            .typage-year-number {
                font-family: 'Sora', sans-serif;
                font-size: 3.5rem;
                font-weight: 900;
                background: linear-gradient(135deg, #004AAD, #004AAD);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1;
                margin-bottom: 10px;
            }

            .typage-year-label {
                font-size: 1.25rem;
                font-weight: 800;
                color: #0f172a;
                margin-bottom: 15px;
                font-family: 'Sora', sans-serif;
            }

            .typage-year-desc {
                font-size: 0.85rem;
                color: #64748b;
                margin: 0 0 20px;
            }

            .typage-year-info {
                display: flex;
                justify-content: space-around;
                border-top: 1px solid #D9D9D9;
                padding-top: 20px;
            }
            .typage-info-item { display: flex; flex-direction: column; align-items: center; gap: 4px; }
            .typage-info-label {
                font-size: 0.7rem;
                font-weight: 800;
                color: #94a3b8;
                text-transform: uppercase;
            }
            .typage-info-value {
                font-size: 1.2rem;
                font-weight: 800;
                color: #004AAD;
                font-family: 'Sora', sans-serif;
            }

            /* Explorer */
            .typage-explorer { padding: 40px 24px 100px; }
            .typage-explorer-inner { max-width: 1200px; margin: 0 auto; }

            .typage-hero-card {
                padding: 50px;
                border-radius: 40px;
                border: 1px solid rgba(0, 74, 173, 0.1);
                background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(239,246,255,0.9));
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 40px 100px rgba(15,23,42,0.06);
                margin-bottom: 40px;
                position: relative;
                overflow: hidden;
            }

            .typage-chip {
                display: inline-block;
                padding: 8px 18px;
                border-radius: 999px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                font-weight: 800;
                font-size: 0.8rem;
                text-transform: uppercase;
                margin-bottom: 24px;
                letter-spacing: 0.05em;
                font-family: 'Sora', sans-serif;
            }

            .typage-hero-title {
                font-family: 'Sora', sans-serif;
                font-size: clamp(2rem, 5vw, 3.5rem);
                font-weight: 900;
                line-height: 1.1;
                color: #0f172a;
                margin: 0 0 20px;
                letter-spacing: -0.03em;
            }

            .typage-hero-subtitle {
                font-size: 1.15rem;
                color: #475569;
                max-width: 600px;
                line-height: 1.7;
                margin: 0 0 32px;
            }

            .typage-hero-stats { display: flex; gap: 20px; }

            .typage-stat {
                background: white;
                padding: 20px 24px;
                border-radius: 20px;
                border: 1px solid #D9D9D9;
                box-shadow: 0 10px 30px rgba(0,0,0,0.03);
                min-width: 140px;
            }
            .typage-stat strong {
                display: block;
                font-size: 2rem;
                color: #0f172a;
                font-family: 'Sora', sans-serif;
            }
            .typage-stat span {
                color: #94a3b8;
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .typage-hero-visual {
                width: 400px;
                height: 350px;
                flex-shrink: 0;
                background: rgba(0, 74, 173, 0.02);
                border-radius: 32px;
                border: 1px dashed rgba(0, 74, 173, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }
            .typage-body-map {
                width: 140px;
                height: 260px;
                background: rgba(0, 74, 173, 0.08);
                border-radius: 70px 70px 60px 60px;
                border: 2px solid rgba(0, 74, 173, 0.2);
            }
            .typage-label-pill {
                position: absolute;
                background: white;
                padding: 8px 14px;
                border-radius: 999px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                border: 1px solid #D9D9D9;
                color: #004AAD;
                font-weight: 800;
                font-size: 0.75rem;
                white-space: nowrap;
            }
            .typage-label-pill.one { top: 40px; left: -20px; }
            .typage-label-pill.two { top: 110px; right: -30px; }
            .typage-label-pill.three { bottom: 60px; left: -10px; }

            /* Subject panels */
            .typage-syllabus-grid { display: grid; gap: 24px; }

            .typage-subject-panel {
                background: white;
                border: 1px solid #D9D9D9;
                border-radius: 32px;
                box-shadow: 0 20px 50px rgba(0,0,0,0.03);
                padding: 30px;
                transition: all 0.3s;
            }
            .typage-subject-panel:hover {
                border-color: rgba(0, 74, 173, 0.2);
                transform: translateY(-4px);
            }

            .typage-subject-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 25px;
            }
            .typage-subject-head h3 {
                font-family: 'Sora', sans-serif;
                font-size: 1.5rem;
                font-weight: 800;
                color: #0f172a;
                margin: 0;
            }
            .typage-subject-head p {
                color: #64748b;
                font-size: 0.95rem;
                margin: 5px 0 0;
            }
            .typage-subject-icon {
                width: 50px;
                height: 50px;
                border-radius: 16px;
                background: rgba(0, 74, 173, 0.06);
                display: grid;
                place-items: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }

            .typage-topic-cloud { display: flex; flex-wrap: wrap; gap: 12px; }

            .typage-topic-chip {
                padding: 14px 22px;
                border-radius: 18px;
                color: #004AAD;
                background: rgba(0, 74, 173, 0.07);
                border: 1px solid rgba(0, 74, 173, 0.1);
                font-weight: 700;
                text-decoration: none;
                transition: all 0.2s;
                font-size: 0.95rem;
            }
            .typage-topic-chip:hover {
                background: #004AAD;
                color: white;
                transform: scale(1.05);
                box-shadow: 0 10px 20px rgba(0, 74, 173, 0.2);
                border-color: #004AAD;
            }

            .typage-topic-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .typage-sub-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                padding-left: 10px;
                border-left: 2px solid #D9D9D9;
                margin-bottom: 10px;
            }
            .typage-sub-chip {
                text-decoration: none;
                padding: 4px 10px;
                background: rgba(217,217,217,0.16);
                border: 1px solid #D9D9D9;
                border-radius: 8px;
                font-size: 0.7rem;
                font-weight: 700;
                color: #64748b;
                transition: all 0.2s;
            }
            .typage-sub-chip:hover {
                background: white;
                border-color: #004AAD;
                color: #004AAD;
            }

            .typage-empty-panel {
                text-align: center;
                padding: 100px 40px;
                background: white;
                border: 1px solid #D9D9D9;
                border-radius: 32px;
            }
            .typage-empty-icon { font-size: 4rem; margin-bottom: 24px; }
            .typage-empty-panel h3 {
                font-family: 'Sora', sans-serif;
                font-size: 1.8rem;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 12px;
            }
            .typage-empty-panel p {
                color: #64748b;
                max-width: 500px;
                margin: 0 auto 32px;
            }
            .typage-empty-panel a {
                padding: 14px 32px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                border-radius: 16px;
                text-decoration: none;
                font-weight: 800;
                display: inline-block;
            }

            /* Responsive */
            @media (max-width: 992px) {
                .typage-hero-card { flex-direction: column; padding: 40px; text-align: center; }
                .typage-hero-subtitle { margin: 0 auto 32px; }
                .typage-hero-stats { justify-content: center; }
                .typage-hero-visual { display: none; }
            }
            @media (max-width: 600px) {
                .typage-cards-grid { grid-template-columns: 1fr; }
                .typage-hero-title { font-size: 2.2rem; }
                .typage-hero-card { padding: 30px 20px; }
                .typage-subject-panel { padding: 20px; }
            }
            .typage-topic-chip-wrapper, .typage-sub-chip-wrapper {
                position: relative;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .typage-mini-bookmark, .typage-micro-bookmark {
                background: none;
                border: none;
                padding: 0;
                color: #94a3b8;
                cursor: pointer;
                transition: all 0.2s;
            }
            .typage-mini-bookmark:hover, .typage-micro-bookmark:hover { color: #004AAD; transform: scale(1.2); }
            .typage-mini-bookmark.active, .typage-micro-bookmark.active { color: #004AAD; }
            .typage-mini-bookmark i { font-size: 1.1rem; }
            .typage-micro-bookmark i { font-size: 0.9rem; }
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

            @media (max-width: 600px) {
                .typage-directory-list {
                    grid-template-columns: 1fr !important;
                }
            }


            /* ─── YEAR TOPICS PAGE: RESPONSIVE OVERRIDES ───────────────────── */
            @media (max-width: 1200px) {
                .topics-year-page .typage-syllabus-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }

            @media (max-width: 992px) {
                .topics-year-page .typage-cards-section {
                    padding: 44px 20px;
                }

                .topics-year-page .typage-explorer {
                    padding: 32px 20px 80px;
                }

                .topics-year-page .typage-hero-card {
                    flex-direction: column;
                    padding: 38px;
                    text-align: center;
                }

                .topics-year-page .typage-hero-copy {
                    width: 100%;
                    min-width: 0;
                }

                .topics-year-page .typage-hero-subtitle {
                    margin-left: auto;
                    margin-right: auto;
                }

                .topics-year-page .typage-hero-stats {
                    justify-content: center;
                    flex-wrap: wrap;
                }

                .topics-year-page .typage-hero-visual {
                    display: none;
                }
            }

            @media (max-width: 768px) {
                .topics-year-page {
                    padding-top: 64px;
                    overflow-x: hidden;
                }

                .topics-year-page .typage-cards-section {
                    padding: 34px 16px;
                }

                .topics-year-page .typage-section-label {
                    margin-bottom: 18px;
                    font-size: 0.72rem;
                    letter-spacing: 0.11em;
                }

                .topics-year-page .typage-cards-grid {
                    grid-template-columns: 1fr;
                    gap: 14px;
                }

                .topics-year-page .typage-card {
                    padding: 26px 20px;
                    border-radius: 20px;
                }

                .topics-year-page .typage-year-desc {
                    overflow-wrap: anywhere;
                }

                .topics-year-page .typage-explorer {
                    padding: 24px 16px 70px;
                }

                .topics-year-page .typage-hero-card {
                    padding: 30px 22px;
                    border-radius: 26px;
                    margin-bottom: 28px;
                }

                .topics-year-page .typage-chip {
                    max-width: 100%;
                    white-space: normal;
                    line-height: 1.4;
                }

                .topics-year-page .typage-hero-title {
                    font-size: clamp(2rem, 10vw, 2.65rem);
                    overflow-wrap: anywhere;
                }

                .topics-year-page .typage-hero-title br {
                    display: none;
                }

                .topics-year-page .typage-hero-subtitle {
                    font-size: 1rem;
                    line-height: 1.65;
                    overflow-wrap: anywhere;
                }

                .topics-year-page .typage-hero-stats {
                    gap: 12px;
                }

                .topics-year-page .typage-stat {
                    flex: 1 1 130px;
                    min-width: 0;
                    padding: 17px 14px;
                }

                .topics-year-page .typage-syllabus-grid {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                }

                .topics-year-page .typage-subject-panel {
                    min-width: 0;
                    padding: 20px;
                    border-radius: 22px;
                }

                .topics-year-page .typage-subject-head {
                    align-items: flex-start;
                    gap: 12px;
                    margin-bottom: 20px;
                }

                .topics-year-page .typage-subject-head > div:first-child {
                    min-width: 0;
                }

                .topics-year-page .typage-subject-title,
                .topics-year-page .typage-subject-head p {
                    overflow-wrap: anywhere;
                }

                .topics-year-page .typage-subject-head h3 {
                    font-size: 1.2rem;
                }

                .topics-year-page .typage-subject-icon {
                    width: 44px;
                    height: 44px;
                    flex: 0 0 44px;
                }

                .topics-year-page .typage-subject-code-wrap {
                    max-width: 100%;
                    flex-wrap: wrap;
                }

                .topics-year-page .typage-unit-select {
                    min-width: 0;
                    font-size: 16px;
                }

                .topics-year-page .typage-unit-topic-panel {
                    height: auto;
                    max-height: 300px;
                    overflow-y: auto;
                    overflow-x: hidden;
                    -webkit-overflow-scrolling: touch;
                }

                .topics-year-page .typage-directory-list {
                    grid-template-columns: 1fr !important;
                }

                .topics-year-page .typage-directory-row {
                    min-width: 0 !important;
                }

                .topics-year-page .typage-directory-item {
                    min-width: 0 !important;
                    padding: 13px 14px !important;
                }

                .topics-year-page .typage-topic-title {
                    white-space: normal !important;
                    overflow: visible !important;
                    text-overflow: clip !important;
                    overflow-wrap: anywhere !important;
                }

                .topics-year-page .typage-subtopic-directory {
                    margin-left: 6px;
                    padding-left: 8px;
                }

                .topics-year-page .typage-empty-panel {
                    padding: 56px 22px;
                    border-radius: 24px;
                }

                .topics-year-page .typage-empty-panel h3,
                .topics-year-page .typage-empty-panel p {
                    overflow-wrap: anywhere;
                }

                .topics-year-page .d-flex.justify-content-center.gap-3 {
                    flex-direction: column;
                    align-items: stretch;
                }

                .topics-year-page .d-flex.justify-content-center.gap-3 > a {
                    width: 100%;
                }
            }

            @media (max-width: 480px) {
                .topics-year-page .typage-cards-section,
                .topics-year-page .typage-explorer {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .topics-year-page .typage-card {
                    padding: 22px 16px;
                }

                .topics-year-page .typage-year-info {
                    gap: 12px;
                }

                .topics-year-page .typage-hero-card {
                    padding: 26px 18px;
                    text-align: left;
                }

                .topics-year-page .typage-hero-subtitle {
                    margin-left: 0;
                    margin-right: 0;
                }

                .topics-year-page .typage-hero-stats {
                    justify-content: stretch;
                }

                .topics-year-page .typage-stat {
                    text-align: center;
                }

                .topics-year-page .typage-subject-panel {
                    padding: 16px;
                    border-radius: 18px;
                }

                .topics-year-page .typage-list-bookmark {
                    width: 40px !important;
                    min-width: 40px !important;
                }

                .topics-year-page .typage-empty-panel {
                    padding: 44px 18px;
                }

                .topics-year-page .typage-empty-icon {
                    font-size: 3rem;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.typage-unit-select').forEach(function (select) {
                    select.addEventListener('change', function () {
                        const subjectId = this.dataset.subjectId;
                        const selectedPanelId = this.value;

                        document
                            .querySelectorAll('.typage-unit-topic-panel[data-subject-id="' + subjectId + '"]')
                            .forEach(function (panel) {
                                panel.classList.add('d-none');
                            });

                        const selectedPanel = document.getElementById(selectedPanelId);

                        if (selectedPanel) {
                            selectedPanel.classList.remove('d-none');
                        }
                    });
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
                        } else {
                            icon.classList.remove('bi-bookmark-fill');
                            icon.classList.add('bi-bookmark');
                            btn.classList.remove('active');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        </script>
    @endpush
@endsection
