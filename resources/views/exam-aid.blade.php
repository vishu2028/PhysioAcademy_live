@extends('layouts.frontend')

@section('title', 'Exam Aid')

@section('content')
    <main class="exam-shell-wrapper">
        <div class="exam-bg" aria-hidden="true">
            <span class="exam-orb exam-orb-one"></span>
            <span class="exam-orb exam-orb-two"></span>
            <span class="exam-orb exam-orb-three"></span>
            <span class="exam-grid-overlay"></span>
        </div>

        @foreach($sections as $section)
            @php
                $content = is_array($section->content)
                    ? $section->content
                    : (json_decode($section->content, true) ?: []);
            @endphp

            @if($section->type === 'exam_hero')
                <section class="exam-hero">
                    <div class="exam-hero-copy">
                        <div class="exam-kicker reveal-up">
                            <span></span>
                            {{ $content['kicker'] ?? 'Exam Aid Studio' }}
                        </div>

                        <h1 class="exam-hero-title reveal-up delay-1">
                            {{ $content['title'] ?? 'Exam Aid' }}
                        </h1>

                        <p class="exam-hero-text reveal-up delay-2">
                            {{ $content['description'] ?? 'Prepare smarter with learning materials, viva questions and exam questions.' }}
                        </p>

                        <div class="exam-hero-actions reveal-up delay-3">
                            @if(!empty($content['primary_cta_text']))
                                <a href="{{ $content['primary_cta_url'] ?? '#exam-resources' }}" class="exam-primary-btn text-decoration-none">
                                    {{ $content['primary_cta_text'] }}
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            @endif

                            @if(!empty($content['secondary_cta_text']))
                                <a href="{{ $content['secondary_cta_url'] ?? '#college-selector' }}" class="exam-secondary-btn text-decoration-none">
                                    {{ $content['secondary_cta_text'] }}
                                </a>
                            @endif
                        </div>

                        <div class="exam-quick-select reveal-stagger">
                            @foreach($content['quick_links'] ?? [] as $link)
                                <button type="button" onclick="window.location.href='{{ $link['url'] ?? '#' }}'">
                                    <span>{{ $link['icon_num'] ?? '01' }}</span>

                                    {{ $link['label'] ?? 'Quick Link' }}

                                    @if(isset($link['count']))
                                        <small>{{ $link['count'] }} available</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="exam-hero-visual reveal-right delay-2">
                        <div class="exam-visual-portrait" aria-hidden="true">
                            <div class="ui-illustration-exam"></div>
                        </div>

                        <div class="exam-dashboard-card">
                            <div class="exam-dashboard-top">
                                <div>
                                    <span>Readiness Score</span>
                                    <strong>{{ $content['readiness_score'] ?? 90 }}%</strong>
                                </div>
                                <div class="exam-pulse"></div>
                            </div>

                            <div class="exam-progress-stack">
                                @foreach($content['progress_items'] ?? [] as $progress)
                                    <div>
                                        <span>{{ $progress['label'] ?? 'Progress' }}</span>
                                        <b style="--w:{{ $progress['width'] ?? '80%' }}"></b>
                                    </div>
                                @endforeach
                            </div>

                            <div class="exam-mini-metrics">
                                <span><strong>{{ $content['stats']['papers'] ?? 0 }}</strong>Papers</span>
                                <span><strong>{{ $content['stats']['questions'] ?? 0 }}</strong>Questions</span>
                                <span><strong>{{ $content['stats']['guides'] ?? 0 }}</strong>Guides</span>
                            </div>
                        </div>

                        @if(isset($content['floating_cards'][0]))
                            <div class="exam-float-card exam-float-one shadow-sm">
                                {{ $content['floating_cards'][0] }}
                            </div>
                        @endif

                        @if(isset($content['floating_cards'][1]))
                            <div class="exam-float-card exam-float-two shadow-sm">
                                {{ $content['floating_cards'][1] }}
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if($section->type === 'exam_filters')
                <section class="exam-section exam-selector-section" id="college-selector">
                    <div class="exam-section-head reveal-up">
                    <span class="exam-section-eyebrow">
                        {{ $content['eyebrow'] ?? 'Smart Filters' }}
                    </span>

                        <h2>
                            {{ $content['title'] ?? 'Find the right study material' }}
                        </h2>

                        <p>
                            {{ $content['description'] ?? 'Filter resources by year, learning material and subject.' }}
                        </p>
                    </div>

                    <form action="{{ route('exam-aid') }}" method="GET" class="exam-filter-panel reveal-up delay-1">
                        <label class="exam-field">
                            <span>Year</span>

                            <select name="year" onchange="this.form.submit()">
                                <option value="all">All Years</option>

                                @foreach($years as $year)
                                    <option value="{{ $year->id }}" {{ request('year') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="exam-field">
                            <span>Learning Material</span>

                            <select name="resource_type" onchange="this.form.submit()">
                                <option value="all" {{ request('resource_type', 'all') === 'all' ? 'selected' : '' }}>
                                    All Resources
                                </option>

                                <option value="pdf" {{ request('resource_type') === 'pdf' ? 'selected' : '' }}>
                                    PDF Materials
                                </option>

                                <option value="video" {{ request('resource_type') === 'video' ? 'selected' : '' }}>
                                    Video Materials
                                </option>

                                <option value="link" {{ request('resource_type') === 'link' ? 'selected' : '' }}>
                                    External Links
                                </option>

                                <option value="note" {{ request('resource_type') === 'note' ? 'selected' : '' }}>
                                    Text Notes
                                </option>

                                <option value="viva" {{ request('resource_type') === 'viva' ? 'selected' : '' }}>
                                    Viva Questions
                                </option>

                                <option value="exam" {{ request('resource_type') === 'exam' ? 'selected' : '' }}>
                                    Exam Questions
                                </option>
                                <option value="guide" {{ request('resource_type') === 'guide' ? 'selected' : '' }}>
                                    Exam Guides
                                </option>

                            </select>
                        </label>

                        <label class="exam-field exam-field-search">
                            <span>Search Subject</span>

                            <input
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="Search Anatomy..."
                                autocomplete="off"
                            >
                        </label>

                        <div class="exam-filter-actions">
                            <button type="submit" class="exam-filter-btn">
                                Search
                            </button>

                            @if(request()->hasAny(['year', 'resource_type', 'q']))
                                <a href="{{ route('exam-aid') }}" class="exam-reset-btn text-decoration-none">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </section>
            @endif

            @if($section->type === 'exam_resources')
                <section class="exam-section" id="exam-resources">
                    <div class="exam-section-head reveal-up">
                    <span class="exam-section-eyebrow">
                        {{ $content['eyebrow'] ?? 'Resource Library' }}
                    </span>

                        <h2>
                            {{ $content['title'] ?? 'Exam Aid Resources' }}
                        </h2>

                        <p>
                            {{ $content['description'] ?? 'Browse learning materials, viva questions and exam questions.' }}
                        </p>
                    </div>

                    <div class="exam-resource-grid reveal-stagger">
                        @forelse($examAids as $examAid)
                            @php
                                $firstPdf = $examAid->materials->firstWhere('type', 'pdf');
                            @endphp

                            <article class="exam-subject-card">
                                <div class="exam-card-top">
                                <span class="exam-difficulty medium">
                                    {{ $examAid->subject->name ?? 'Subject' }}
                                </span>

                                    <span>
                                    {{ $examAid->academicYear->name ?? 'All Years' }}
                                </span>
                                </div>

                                <h3>{{ $examAid->title }}</h3>

                                <p>
                                    {{ \Illuminate\Support\Str::limit($examAid->description, 120) }}
                                </p>

                                <div class="exam-card-meta">
                                    <span>{{ $examAid->materials->count() }} Materials</span>

                                    @if($examAid->unit)
                                        <span>{{ $examAid->unit->name }}</span>
                                    @elseif($examAid->semester)
                                        <span>{{ $examAid->semester->name }}</span>
                                    @else
                                        <span>Exam Ready</span>
                                    @endif
                                </div>

                                <div class="exam-mini-tags">
                                    @if(!empty($examAid->viva_question))
                                        <span>Viva</span>
                                    @endif

                                    @if(!empty($examAid->exam_question))
                                        <span>Exam</span>
                                    @endif

                                    @foreach($examAid->materials->pluck('type')->unique() as $type)
                                        <span>{{ strtoupper($type) }}</span>
                                    @endforeach
                                </div>

                                <div class="exam-card-actions">
                                    @auth
                                        <button
                                            type="button"
                                            class="exam-view-toggle"
                                            data-target="examAidDetails{{ $examAid->id }}"
                                        >
                                            View
                                        </button>
                                    @else
                                        <button type="button" onclick="window.location.href='{{ route('login') }}'">
                                            <i class="bi bi-lock-fill me-1"></i> Unlock
                                        </button>
                                    @endauth

                                    @if($firstPdf && $firstPdf->file_path)
                                        @auth
                                            <button type="button" onclick="window.open('{{ asset('storage/' . $firstPdf->file_path) }}', '_blank')">
                                                Download
                                            </button>
                                        @else
                                            <button type="button" onclick="window.location.href='{{ route('login') }}'">
                                                <i class="bi bi-lock-fill me-1"></i> Download
                                            </button>
                                        @endauth
                                    @endif
                                </div>

                                @auth
                                    <div class="exam-card-details exam-details-hidden" id="examAidDetails{{ $examAid->id }}">
                                        @if($examAid->materials->count())
                                            <div class="exam-detail-block">
                                                <h4>Learning Materials</h4>

                                                @foreach($examAid->materials as $material)
                                                    <div class="exam-material-item">
                                                        <div>
                                                            <strong>{{ $material->title }}</strong>
                                                            <small>{{ strtoupper($material->type) }}</small>
                                                        </div>

                                                        <div class="exam-material-action">
                                                            @if($material->type === 'pdf' && $material->file_path)
                                                                <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank">
                                                                    Open PDF
                                                                </a>
                                                            @elseif($material->type === 'link' && $material->url)
                                                                <a href="{{ $material->url }}" target="_blank">
                                                                    Open Link
                                                                </a>
                                                            @elseif($material->type === 'video' && $material->content)
                                                                @if(filter_var($material->content, FILTER_VALIDATE_URL))
                                                                    <a href="{{ $material->content }}" target="_blank">
                                                                        Open Video
                                                                    </a>
                                                                @else
                                                                    <div class="exam-embed-content">
                                                                        {!! $material->content !!}
                                                                    </div>
                                                                @endif
                                                            @elseif($material->type === 'note' && $material->content)
                                                                <div class="exam-note-content">
                                                                    {!! nl2br(e($material->content)) !!}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($examAid->viva_question)
                                            <div class="exam-detail-block">
                                                <h4>Viva Questions</h4>
                                                <div class="exam-question-box">
                                                    {!! nl2br(e($examAid->viva_question)) !!}
                                                </div>
                                            </div>
                                        @endif

                                        @if($examAid->exam_question)
                                            <div class="exam-detail-block">
                                                <h4>Exam Questions</h4>
                                                <div class="exam-question-box">
                                                    {!! nl2br(e($examAid->exam_question)) !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endauth
                            </article>
                        @empty
                            <div class="exam-empty-state">
                                <h3>No Exam Aid found</h3>
                                <p>Please try another year, learning material or subject search.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($examAids->hasPages())
                        <div class="exam-pagination mt-4">
                            {{ $examAids->links() }}
                        </div>
                    @endif
                </section>
            @endif
        @endforeach
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.exam-view-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const detailsBox = document.getElementById(targetId);

                    if (!detailsBox) {
                        return;
                    }

                    detailsBox.classList.toggle('exam-details-hidden');

                    if (detailsBox.classList.contains('exam-details-hidden')) {
                        this.innerText = 'View';
                    } else {
                        this.innerText = 'Hide';
                    }
                });
            });
        });
    </script>
    @push('styles')
        <style>
            .exam-shell-wrapper {
                --exam-brand-blue: #004AAD;
                --exam-brand-white: #FFFFFF;
                --exam-brand-grey: #D9D9D9;
            }

            .exam-details-hidden {
                display: none;
            }
            .exam-shell-wrapper {
                position: relative;
                padding-top: 72px;
                min-height: 100vh;
                overflow-x: hidden;
                background: #FFFFFF;
            }

            .exam-bg {
                position: absolute;
                inset: 0 0 auto;
                height: 780px;
                overflow: hidden;
                pointer-events: none;
            }

            .exam-grid-overlay {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(0, 74, 173, 0.05) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(0, 74, 173, 0.05) 1px, transparent 1px);
                background-size: 50px 50px;
                mask-image: radial-gradient(circle at center, black, transparent 80%);
            }

            .exam-orb {
                position: absolute;
                filter: blur(100px);
                opacity: 0.3;
                border-radius: 50%;
            }

            .exam-orb-one {
                width: 500px;
                height: 500px;
                top: -100px;
                left: -100px;
                background: rgba(0, 74, 173, 0.2);
            }

            .exam-orb-two {
                width: 400px;
                height: 400px;
                top: 200px;
                right: -50px;
                background: rgba(0, 74, 173, 0.2);
            }

            .exam-orb-three {
                width: 300px;
                height: 300px;
                bottom: 0;
                left: 20%;
                background: rgba(0, 74, 173, 0.1);
            }

            .exam-hero {
                max-width: 1280px;
                margin: 0 auto;
                padding: 80px 24px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 60px;
                align-items: center;
                position: relative;
                z-index: 1;
            }

            .exam-kicker {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 7px 16px;
                background: rgba(0, 74, 173, 0.08);
                border: 1px solid rgba(0, 74, 173, 0.15);
                border-radius: 99px;
                font-size: 0.8rem;
                font-weight: 600;
                color: #004AAD;
                margin-bottom: 24px;
            }

            .exam-kicker span {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #004AAD;
            }

            .exam-hero-title {
                font-family: var(--font-display);
                font-size: clamp(2.5rem, 5vw, 4rem);
                line-height: 1.1;
                letter-spacing: -0.02em;
                margin-bottom: 24px;
                color: #0f172a;
            }

            .exam-hero-text {
                font-size: 1.1rem;
                color: #64748b;
                margin-bottom: 40px;
                max-width: 540px;
            }

            .exam-hero-actions {
                display: flex;
                gap: 14px;
                margin-bottom: 40px;
                flex-wrap: wrap;
            }

            .exam-primary-btn {
                background: #004AAD;
                color: #FFFFFF;
                padding: 14px 28px;
                border-radius: 12px;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .exam-primary-btn:hover {
                background: #003B8A;
                color: #FFFFFF;
            }

            .exam-secondary-btn {
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.12);
                color: #64748b;
                padding: 14px 28px;
                border-radius: 12px;
                font-weight: 700;
            }

            .exam-quick-select {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                max-width: 500px;
            }

            .exam-quick-select button {
                padding: 14px;
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                border-radius: 12px;
                text-align: left;
                font-size: 0.85rem;
                font-weight: 600;
                color: #0f172a;
            }

            .exam-quick-select button span {
                color: #004AAD;
                font-size: 0.7rem;
                margin-right: 8px;
            }

            .exam-hero-visual {
                position: relative;
            }

            .ui-illustration-exam {
                width: 100%;
                height: 400px;
                background: url('https://illustrations.popsy.co/blue/learning.svg') no-repeat center;
                background-size: contain;
            }

            .exam-dashboard-card {
                position: absolute;
                bottom: -20px;
                left: -20px;
                background: #FFFFFF;
                padding: 24px;
                border-radius: 20px;
                border: 1px solid rgba(0, 74, 173, 0.1);
                box-shadow: 0 25px 60px rgba(0,0,0,0.1);
                width: 280px;
            }

            .exam-dashboard-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .exam-dashboard-top span {
                color: #64748b;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .exam-dashboard-top strong {
                font-size: 2.5rem;
                display: block;
                color: #0f172a;
            }

            .exam-pulse {
                width: 14px;
                height: 14px;
                background: #22c55e;
                border-radius: 50%;
                box-shadow: 0 0 0 8px rgba(34,197,94,0.12);
            }

            .exam-progress-stack {
                margin: 15px 0;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .exam-progress-stack div {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .exam-progress-stack span {
                font-size: 0.75rem;
                color: #64748b;
            }

            .exam-progress-stack b {
                height: 6px;
                background: #D9D9D9;
                border-radius: 3px;
                position: relative;
                overflow: hidden;
            }

            .exam-progress-stack b::after {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                height: 100%;
                width: var(--w);
                background: #004AAD;
            }

            .exam-mini-metrics {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                margin-top: 12px;
            }

            .exam-mini-metrics span {
                font-size: 0.72rem;
                color: #64748b;
            }

            .exam-mini-metrics strong {
                display: block;
                color: #0f172a;
                font-size: 1rem;
            }

            .exam-float-card {
                position: absolute;
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                border-radius: 14px;
                padding: 12px 16px;
                color: #0f172a;
                font-weight: 700;
                font-size: 0.85rem;
            }

            .exam-float-one {
                top: 30px;
                right: 40px;
            }

            .exam-float-two {
                bottom: 70px;
                right: 0;
            }

            .exam-section {
                max-width: 1280px;
                margin: 0 auto;
                padding: 80px 24px;
                position: relative;
                z-index: 1;
            }

            .exam-section-head {
                text-align: center;
                max-width: 760px;
                margin: 0 auto 45px;
            }

            .exam-section-eyebrow {
                display: inline-flex;
                padding: 8px 18px;
                border-radius: 999px;
                background: rgba(0, 74, 173, 0.08);
                border: 1px solid rgba(0, 74, 173, 0.15);
                color: #004AAD;
                font-weight: 800;
                font-size: 0.85rem;
                margin-bottom: 18px;
            }

            .exam-section-head h2 {
                color: #0f172a;
                font-size: clamp(2rem, 4vw, 3.2rem);
                line-height: 1.1;
                margin-bottom: 14px;
                font-weight: 800;
            }

            .exam-section-head p {
                color: #64748b;
                font-size: 1.05rem;
            }

            .exam-filter-panel {
                display: grid;
                grid-template-columns: 1.1fr 1.1fr 1.1fr auto;
                gap: 15px;
                padding: 25px;
                background: #FFFFFF;
                border-radius: 20px;
                border: 1px solid rgba(0, 74, 173, 0.1);
                box-shadow: 0 15px 40px rgba(0,0,0,0.03);
            }

            .exam-field {
                min-height: 86px;
                border: 1px solid rgba(0, 74, 173, 0.12);
                border-radius: 16px;
                padding: 16px 20px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                background: #FFFFFF;
            }

            .exam-field span {
                color: #004AAD;
                text-transform: uppercase;
                font-size: 0.78rem;
                font-weight: 800;
                margin-bottom: 8px;
            }

            .exam-field select,
            .exam-field input {
                border: 0;
                outline: 0;
                background: transparent;
                color: #0f172a;
                font-weight: 800;
                font-size: 1rem;
                width: 100%;
            }

            .exam-field input::placeholder {
                color: #8b95a5;
            }

            .exam-filter-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .exam-filter-btn {
                border: 0;
                background: #004AAD;
                color: #FFFFFF;
                padding: 14px 24px;
                border-radius: 14px;
                font-weight: 700;
                min-height: 56px;
            }

            .exam-reset-btn {
                color: #64748b;
                font-weight: 700;
                padding: 14px 12px;
            }

            .exam-resource-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }

            .exam-subject-card {
                background: #FFFFFF;
                border-radius: 20px;
                border: 1px solid rgba(0, 74, 173, 0.1);
                padding: 24px;
                transition: transform 0.3s, box-shadow 0.3s;
                box-shadow: 0 15px 40px rgba(0,0,0,0.03);
            }

            .exam-subject-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 22px 50px rgba(15,23,42,0.08);
            }

            .exam-card-top {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 18px;
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 700;
            }

            .exam-difficulty {
                padding: 6px 10px;
                border-radius: 999px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                border: 1px solid rgba(0, 74, 173, 0.12);
            }

            .exam-subject-card h3 {
                color: #0f172a;
                font-size: 1.25rem;
                font-weight: 800;
                margin-bottom: 10px;
            }

            .exam-subject-card p {
                color: #64748b;
                line-height: 1.65;
                min-height: 55px;
            }

            .exam-card-meta {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                color: #64748b;
                font-size: 0.82rem;
                font-weight: 700;
                margin: 16px 0;
            }

            .exam-card-meta span {
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                padding: 7px 10px;
                border-radius: 999px;
            }

            .exam-mini-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 16px 0;
            }

            .exam-mini-tags span {
                font-size: 0.72rem;
                font-weight: 700;
                color: #004AAD;
                background: rgba(0, 74, 173, 0.08);
                border: 1px solid rgba(0, 74, 173, 0.12);
                padding: 6px 10px;
                border-radius: 999px;
            }

            .exam-card-actions {
                display: flex;
                gap: 10px;
                margin-top: 18px;
            }

            .exam-card-actions button {
                border: 0;
                background: #004AAD;
                color: #FFFFFF;
                padding: 10px 16px;
                border-radius: 12px;
                font-weight: 800;
            }

            .exam-card-actions button:nth-child(2) {
                background: #D9D9D9;
                color: #0f172a;
            }

            .exam-card-details {
                margin-top: 22px;
                padding-top: 20px;
                border-top: 1px solid rgba(0, 74, 173, 0.12);
            }

            .exam-detail-block {
                margin-top: 18px;
            }

            .exam-detail-block h4 {
                color: #0f172a;
                font-size: 1rem;
                font-weight: 800;
                margin-bottom: 12px;
            }

            .exam-material-item {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                padding: 14px;
                border-radius: 14px;
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                margin-bottom: 10px;
            }

            .exam-material-item strong {
                color: #0f172a;
                display: block;
            }

            .exam-material-item small {
                color: #64748b;
                font-weight: 800;
            }

            .exam-material-action a {
                color: #004AAD;
                font-weight: 800;
                text-decoration: none;
            }

            .exam-question-box,
            .exam-note-content {
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                border-radius: 14px;
                padding: 16px;
                color: #334155;
                line-height: 1.7;
                font-size: 0.92rem;
            }

            .exam-embed-content iframe {
                width: 100%;
                min-height: 220px;
                border-radius: 14px;
            }

            .exam-empty-state {
                grid-column: 1 / -1;
                background: #FFFFFF;
                border: 1px solid rgba(0, 74, 173, 0.1);
                border-radius: 20px;
                padding: 40px;
                text-align: center;
            }

            .exam-empty-state h3 {
                margin-bottom: 8px;
                color: #0f172a;
            }

            .exam-empty-state p {
                color: #64748b;
                margin: 0;
            }

            .exam-pagination {
                display: flex;
                justify-content: center;
            }


            /* ─── PHYSIO SOURCE EXAM AID BRAND OVERRIDES ───────────── */
            .exam-shell-wrapper .exam-secondary-btn,
            .exam-shell-wrapper .exam-quick-select button,
            .exam-shell-wrapper .exam-dashboard-card,
            .exam-shell-wrapper .exam-float-card,
            .exam-shell-wrapper .exam-filter-panel,
            .exam-shell-wrapper .exam-field,
            .exam-shell-wrapper .exam-subject-card,
            .exam-shell-wrapper .exam-empty-state {
                background: #FFFFFF;
            }

            .exam-shell-wrapper .exam-secondary-btn,
            .exam-shell-wrapper .exam-quick-select button,
            .exam-shell-wrapper .exam-dashboard-card,
            .exam-shell-wrapper .exam-float-card,
            .exam-shell-wrapper .exam-filter-panel,
            .exam-shell-wrapper .exam-field,
            .exam-shell-wrapper .exam-subject-card,
            .exam-shell-wrapper .exam-empty-state {
                border-color: rgba(0, 74, 173, 0.12);
            }

            .exam-shell-wrapper .exam-card-actions button:nth-child(2),
            .exam-shell-wrapper .exam-progress-stack b {
                background: rgba(217, 217, 217, 0.45);
            }

            .exam-shell-wrapper .exam-card-meta span,
            .exam-shell-wrapper .exam-material-item,
            .exam-shell-wrapper .exam-question-box,
            .exam-shell-wrapper .exam-note-content {
                background: rgba(217, 217, 217, 0.18);
            }

            @media (max-width: 1024px) {
                .exam-hero {
                    grid-template-columns: 1fr;
                }

                .exam-resource-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .exam-filter-panel {
                    grid-template-columns: 1fr 1fr;
                }

                .exam-filter-actions {
                    grid-column: 1 / -1;
                }
            }

            @media (max-width: 768px) {
                .exam-resource-grid,
                .exam-filter-panel {
                    grid-template-columns: 1fr;
                }

                .exam-hero {
                    padding: 50px 18px;
                }

                .exam-section {
                    padding: 55px 18px;
                }

                .exam-quick-select {
                    grid-template-columns: 1fr;
                }

                .exam-dashboard-card {
                    position: relative;
                    bottom: auto;
                    left: auto;
                    width: 100%;
                    margin-top: 20px;
                }

                .exam-material-item {
                    flex-direction: column;
                }
            }


            /* ─── EXAM AID PAGE: RESPONSIVE OVERRIDES ─────────────────── */
            @media (max-width: 1200px) {
                .exam-shell-wrapper .exam-hero { gap: 40px; }
                .exam-shell-wrapper .exam-resource-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 1024px) {
                .exam-shell-wrapper .exam-hero {
                    grid-template-columns: 1fr;
                }

                .exam-shell-wrapper .exam-hero-copy {
                    max-width: 760px;
                }

                .exam-shell-wrapper .exam-hero-visual {
                    min-height: 470px;
                    max-width: 760px;
                    width: 100%;
                    margin: 0 auto;
                }

                .exam-shell-wrapper .exam-filter-panel {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .exam-shell-wrapper .exam-filter-actions {
                    grid-column: 1 / -1;
                }
            }

            @media (max-width: 768px) {
                .exam-shell-wrapper {
                    padding-top: 64px;
                    overflow-x: hidden;
                }

                .exam-shell-wrapper .exam-bg {
                    height: 620px;
                }

                .exam-shell-wrapper .exam-orb-one {
                    width: 320px;
                    height: 320px;
                    left: -140px;
                }

                .exam-shell-wrapper .exam-orb-two {
                    width: 280px;
                    height: 280px;
                    right: -120px;
                }

                .exam-shell-wrapper .exam-hero {
                    padding: 42px 16px 54px;
                    gap: 32px;
                }

                .exam-shell-wrapper .exam-kicker {
                    max-width: 100%;
                    white-space: normal;
                    line-height: 1.4;
                }

                .exam-shell-wrapper .exam-hero-title {
                    font-size: clamp(2.2rem, 11vw, 3rem);
                    overflow-wrap: anywhere;
                    margin-bottom: 18px;
                }

                .exam-shell-wrapper .exam-hero-text {
                    font-size: 1rem;
                    line-height: 1.7;
                    margin-bottom: 28px;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-hero-actions {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 10px;
                    margin-bottom: 28px;
                }

                .exam-shell-wrapper .exam-primary-btn,
                .exam-shell-wrapper .exam-secondary-btn {
                    width: 100%;
                    justify-content: center;
                    text-align: center;
                    padding: 13px 20px;
                }

                .exam-shell-wrapper .exam-quick-select {
                    grid-template-columns: 1fr;
                    max-width: none;
                }

                .exam-shell-wrapper .exam-quick-select button {
                    width: 100%;
                    min-width: 0;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-hero-visual {
                    min-height: auto;
                    display: flex;
                    flex-direction: column;
                    gap: 16px;
                }

                .exam-shell-wrapper .exam-visual-portrait {
                    width: 100%;
                }

                .exam-shell-wrapper .ui-illustration-exam {
                    height: 290px;
                }

                .exam-shell-wrapper .exam-dashboard-card {
                    position: relative;
                    inset: auto;
                    width: 100%;
                    margin-top: 0;
                    padding: 20px;
                    border-radius: 18px;
                }

                .exam-shell-wrapper .exam-float-card {
                    position: relative;
                    inset: auto;
                    width: fit-content;
                    max-width: 100%;
                    margin: 0;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-float-one {
                    align-self: flex-start;
                }

                .exam-shell-wrapper .exam-float-two {
                    align-self: flex-end;
                }

                .exam-shell-wrapper .exam-section {
                    padding: 52px 16px;
                }

                .exam-shell-wrapper .exam-section-head {
                    margin-bottom: 30px;
                }

                .exam-shell-wrapper .exam-section-eyebrow {
                    max-width: 100%;
                    white-space: normal;
                    line-height: 1.4;
                }

                .exam-shell-wrapper .exam-section-head h2 {
                    font-size: clamp(1.85rem, 8vw, 2.5rem);
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-section-head p {
                    font-size: 1rem;
                    line-height: 1.65;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-filter-panel {
                    grid-template-columns: 1fr;
                    padding: 18px;
                    gap: 12px;
                    border-radius: 18px;
                }

                .exam-shell-wrapper .exam-field {
                    min-height: auto;
                    padding: 14px 16px;
                    border-radius: 14px;
                }

                .exam-shell-wrapper .exam-field select,
                .exam-shell-wrapper .exam-field input {
                    min-width: 0;
                    font-size: 16px;
                }

                .exam-shell-wrapper .exam-filter-actions {
                    grid-column: auto;
                    width: 100%;
                    flex-direction: column;
                    align-items: stretch;
                }

                .exam-shell-wrapper .exam-filter-btn,
                .exam-shell-wrapper .exam-reset-btn {
                    width: 100%;
                    text-align: center;
                    justify-content: center;
                }

                .exam-shell-wrapper .exam-resource-grid {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }

                .exam-shell-wrapper .exam-subject-card {
                    min-width: 0;
                    padding: 20px;
                    border-radius: 18px;
                }

                .exam-shell-wrapper .exam-card-top {
                    flex-wrap: wrap;
                    align-items: flex-start;
                }

                .exam-shell-wrapper .exam-card-top > span,
                .exam-shell-wrapper .exam-difficulty {
                    max-width: 100%;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-subject-card h3,
                .exam-shell-wrapper .exam-subject-card p {
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-subject-card p {
                    min-height: 0;
                }

                .exam-shell-wrapper .exam-card-meta,
                .exam-shell-wrapper .exam-mini-tags {
                    gap: 7px;
                }

                .exam-shell-wrapper .exam-card-actions {
                    flex-direction: column;
                    align-items: stretch;
                }

                .exam-shell-wrapper .exam-card-actions button {
                    width: 100%;
                }

                .exam-shell-wrapper .exam-material-item {
                    flex-direction: column;
                    gap: 10px;
                    min-width: 0;
                }

                .exam-shell-wrapper .exam-material-item > div {
                    min-width: 0;
                }

                .exam-shell-wrapper .exam-material-item strong,
                .exam-shell-wrapper .exam-material-action,
                .exam-shell-wrapper .exam-question-box,
                .exam-shell-wrapper .exam-note-content {
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-material-action a {
                    display: inline-block;
                    max-width: 100%;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-embed-content,
                .exam-shell-wrapper .exam-embed-content iframe,
                .exam-shell-wrapper .exam-embed-content video,
                .exam-shell-wrapper .exam-embed-content img {
                    max-width: 100% !important;
                    width: 100%;
                    height: auto;
                }

                .exam-shell-wrapper .exam-embed-content iframe {
                    min-height: 220px;
                }

                .exam-shell-wrapper .exam-empty-state {
                    padding: 32px 20px;
                }

                .exam-shell-wrapper .exam-pagination {
                    overflow-x: auto;
                    justify-content: flex-start;
                    padding-bottom: 6px;
                    -webkit-overflow-scrolling: touch;
                }
            }

            @media (max-width: 480px) {
                .exam-shell-wrapper .exam-hero {
                    padding: 34px 12px 46px;
                }

                .exam-shell-wrapper .exam-kicker {
                    padding: 7px 12px;
                    font-size: 0.72rem;
                }

                .exam-shell-wrapper .exam-hero-title {
                    font-size: clamp(2rem, 12vw, 2.55rem);
                }

                .exam-shell-wrapper .ui-illustration-exam {
                    height: 240px;
                }

                .exam-shell-wrapper .exam-dashboard-card {
                    padding: 16px;
                }

                .exam-shell-wrapper .exam-dashboard-top strong {
                    font-size: 2rem;
                }

                .exam-shell-wrapper .exam-mini-metrics {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 6px;
                }

                .exam-shell-wrapper .exam-mini-metrics span {
                    font-size: 0.66rem;
                }

                .exam-shell-wrapper .exam-section {
                    padding: 44px 12px;
                }

                .exam-shell-wrapper .exam-filter-panel,
                .exam-shell-wrapper .exam-subject-card {
                    padding: 16px;
                }

                .exam-shell-wrapper .exam-field {
                    padding: 13px 14px;
                }

                .exam-shell-wrapper .exam-card-top {
                    flex-direction: column;
                }

                .exam-shell-wrapper .exam-card-meta span,
                .exam-shell-wrapper .exam-mini-tags span {
                    max-width: 100%;
                    overflow-wrap: anywhere;
                }

                .exam-shell-wrapper .exam-question-box,
                .exam-shell-wrapper .exam-note-content {
                    padding: 14px;
                }

                .exam-shell-wrapper .exam-empty-state {
                    padding: 28px 16px;
                }
            }
        </style>
    @endpush
@endsection
