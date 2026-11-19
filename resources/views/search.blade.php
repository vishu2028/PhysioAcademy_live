@extends('layouts.frontend')

@section('title', 'Search')

@section('content')
    <main class="site-search-page">
        <section class="site-search-hero">
            <div class="site-search-copy">
                <span class="site-search-kicker"><span class="site-search-dot"></span> Site Search</span>
                <h1>Search</h1>
                <p>Find syllabus topics, subjects, units, and Exam Aid resources across PhysioSource.</p>
            </div>

            <div class="site-search-panel">
                <form action="{{ route('search') }}" method="GET" class="site-search-input-wrap">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="search" name="query" value="{{ $query }}" placeholder="Search topics, Exam Aid, subjects, units..." aria-label="Search topics, Exam Aid, subjects, and units" autocomplete="off" autofocus id="siteSearchInput">
                    @if($query)
                        <a href="{{ route('search') }}" class="site-search-clear">Clear</a>
                    @endif
                </form>

                <div class="site-search-filters" aria-label="Search filters">
                    @foreach([
                        'all' => 'All',
                        'topic' => 'Topics',
                        'learning-material' => 'Learning Material',
                        'exam-aid' => 'Exam Aid',
                        'faq' => 'FAQs',
                    ] as $filterKey => $filterLabel)
                        <a class="site-search-filter {{ $filter === $filterKey ? 'active' : '' }}"
                           href="{{ route('search', array_merge(request()->except('page'), ['filter' => $filterKey])) }}"
                           aria-current="{{ $filter === $filterKey ? 'page' : 'false' }}">
                            {{ $filterLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="site-search-content">
            <div class="site-search-toolbar">
                <p id="searchResultCount">
                    @if($query)
                        Results for "{{ $query }}"
                    @else
                        Enter a search term to find content
                    @endif
                </p>
                <select id="yearFilter" aria-label="Filter by year">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>All years</option>
                    @foreach(\App\Models\AcademicYear::active()->orderBy('order')->get() as $yr)
                        <option value="{{ $yr->name }}" {{ $selectedYear === $yr->name ? 'selected' : '' }}>{{ $yr->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="site-search-results" id="siteSearchResults">
                @if($searchResults->isEmpty())
                    @if($query)
                        <div class="site-search-empty" id="siteSearchEmpty">
                            <h2>No matching results found</h2>
                            <p>Try a topic, learning resource, subject, unit, Exam Aid, FAQ, or a shorter keyword.</p>
                            <a href="{{ route('topics.index') }}">Browse all topics</a>
                        </div>
                    @else
                        <div class="site-search-empty" id="siteSearchEmpty">
                            <h2>Search all PhysioSource content</h2>
                            <p>Enter a topic, learning resource, subject, unit, Exam Aid, or FAQ above.</p>
                        </div>
                    @endif
                @else
                    @foreach($searchResults as $result)
                        <a class="site-search-result-card"
                           href="{{ $result['url'] }}"
                           data-year="{{ $result['year'] ?? '' }}"
                           data-subject="{{ $result['subject']['name'] ?? '' }}"
                           data-category="{{ \Illuminate\Support\Str::slug($result['category']) }}">
                            <div>
                                <span class="site-search-result-subject">
                                    {{ collect([
                                        $result['category'],
                                        $result['subject']['name'] ?? null,
                                        $result['unit']['name'] ?? null,
                                        $result['year'] ?? null,
                                    ])->filter()->implode(' · ') }}
                                </span>
                                <h2>{{ $result['title'] }}</h2>
                                <p>{{ $result['description'] ?: ($result['year'] ?? 'Open result') }}</p>
                            </div>
                            <span class="site-search-status available">✓ Available</span>
                        </a>
                    @endforeach
                @endif
            </div>

            @if($searchResults->hasPages())
                <div class="site-search-pagination">
                    {{ $searchResults->links('vendor.pagination.search') }}
                </div>
            @endif

            <section class="site-search-trending" aria-labelledby="trendingTopicsTitle">
                <h2 id="trendingTopicsTitle">Trending Topics</h2>
                @forelse($trendingTopics as $topic)
                    <a class="site-search-result-card"
                       href="{{ $topic['url'] }}">
                        <div>
                            <span class="site-search-result-subject">
                                Topic{{ !empty($topic['subject']['name']) ? ' · ' . $topic['subject']['name'] : '' }}
                            </span>
                            <h3>{{ $topic['title'] }}</h3>
                            <p>{{ $topic['description'] ?: 'Explore this trending topic.' }}</p>
                        </div>
                        <span class="site-search-status available">✓ Available</span>
                    </a>
                @empty
                    <p>No trending topics are available right now.</p>
                @endforelse
            </section>
        </section>
    </main>

    @push('styles')
        <style>
            /* ─── SEARCH PAGE — FULLY SCOPED ──────────────────────── */
            .site-search-page {
                min-height: auto;
                background: #FFFFFF;
                font-family: 'Outfit', sans-serif;
                padding: 72px 24px 24px;
            }

            .site-search-hero {
                padding: 80px 24px 60px;
                text-align: center;
                background: radial-gradient(circle at 50% 0%, rgba(0, 74, 173, 0.05), transparent 70%);
            }

            .site-search-kicker {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #64748b;
                margin-bottom: 20px;
            }

            .site-search-dot {
                width: 6px;
                height: 6px;
                background: #004AAD;
                border-radius: 50%;
                display: inline-block;
            }

            .site-search-copy h1 {
                font-family: 'Sora', sans-serif;
                font-size: clamp(2.5rem, 6vw, 3.5rem);
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 16px;
                letter-spacing: -0.02em;
            }

            .site-search-copy p {
                color: #64748b;
                font-size: 1.15rem;
                max-width: 600px;
                margin: 0 auto 40px;
            }

            .site-search-panel {
                max-width: 800px;
                margin: 0 auto;
                padding: 24px;
                border-radius: 28px;
                background: rgba(255,255,255,0.7);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(0, 74, 173, 0.1);
                box-shadow: 0 30px 60px rgba(15,23,42,0.05);
            }

            .site-search-input-wrap {
                display: flex;
                align-items: center;
                gap: 16px;
                background: white;
                padding: 14px 24px;
                border-radius: 20px;
                border: 1px solid rgba(0, 74, 173, 0.15);
                transition: all 0.3s;
            }

            .site-search-input-wrap:focus-within {
                border-color: #004AAD;
                box-shadow: 0 0 0 4px rgba(0, 74, 173, 0.1);
            }

            .site-search-input-wrap svg { color: #94a3b8; flex-shrink: 0; }

            .site-search-input-wrap input {
                flex: 1;
                border: none;
                outline: none;
                font-size: 1.1rem;
                color: #0f172a;
                font-family: 'Outfit', sans-serif;
                background: transparent;
                min-width: 0;
            }

            .site-search-clear {
                font-size: 0.8rem;
                font-weight: 700;
                color: #94a3b8;
                text-decoration: none;
                white-space: nowrap;
                padding: 4px 10px;
                border-radius: 6px;
                border: 1px solid #D9D9D9;
            }

            .site-search-clear:hover { color: #ef4444; border-color: #ef4444; }

            .site-search-filters {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-top: 24px;
                overflow-x: auto;
                padding-bottom: 5px;
            }

            .site-search-filter {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 10px 22px;
                border-radius: 999px;
                border: 1px solid rgba(15,23,42,0.08);
                background: white;
                color: #64748b;
                font-weight: 700;
                font-size: 0.85rem;
                cursor: pointer;
                transition: all 0.2s;
                white-space: nowrap;
                font-family: 'Sora', sans-serif;
                text-decoration: none;
            }

            .site-search-filter.active {
                background: #004AAD;
                color: #FFFFFF;
                border-color: #004AAD;
            }

            .site-search-content {
                max-width: 1100px;
                margin: 0 auto;
                padding: 44px 24px 48px;
            }

            .site-search-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                border-bottom: 1px solid #D9D9D9;
                padding-bottom: 20px;
            }

            .site-search-toolbar p {
                font-weight: 800;
                color: #0f172a;
                text-transform: uppercase;
                font-size: 0.8rem;
                letter-spacing: 0.1em;
                font-family: 'Sora', sans-serif;
                margin: 0;
            }

            .site-search-toolbar select {
                padding: 10px 18px;
                border-radius: 14px;
                border: 1px solid #D9D9D9;
                background: white;
                font-weight: 700;
                color: #475569;
                font-family: 'Outfit', sans-serif;
                outline: none;
                cursor: pointer;
            }

            .site-search-page .site-search-results {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                align-items: stretch;
                gap: 18px;
            }

            .site-search-page .site-search-results .site-search-result-card {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                min-width: 0;
                min-height: 220px;
                height: 100%;
                padding: 22px;
                border: 1px solid #D9D9D9;
                border-radius: 20px;
                background: #fff;
            }

            .site-search-page .site-search-results .site-search-result-card > div:first-child {
                width: 100%;
                min-width: 0;
            }

            .site-search-page .site-search-results .site-search-result-card h2,
            .site-search-page .site-search-results .site-search-result-card p,
            .site-search-page .site-search-results .site-search-result-subject {
                overflow-wrap: anywhere;
            }

            .site-search-page .site-search-results .site-search-status {
                margin-top: 0;
                align-self: flex-start;
            }

            @media (max-width: 1024px) {
                .site-search-page .site-search-results {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            .site-search-pagination {
                margin-top: 24px !important;
            }

            .site-search-pagination-nav,
            .site-search-page-numbers {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                min-width: 0;
            }

            .site-search-pagination-nav {
                flex-wrap: wrap;
                width: 100%;
            }

            .site-search-page-numbers {
                flex-wrap: wrap;
            }

            .site-search-page-control,
            .site-search-page-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 40px;
                width: 40px;
                min-width: 40px;
                height: 40px;
                padding: 0;
                border: 1px solid #dbe3ee;
                border-radius: 10px;
                background: #fff;
                color: #334155;
                font: 600 0.9rem/1 'Outfit', sans-serif;
                text-decoration: none;
                transition: background-color 0.2s, border-color 0.2s, color 0.2s;
            }

            .site-search-page-control:hover,
            .site-search-page-number:not(.is-current):hover {
                border-color: #004AAD;
                color: #004AAD;
                background: #f3f8ff;
            }

            .site-search-page-control svg {
                display: block;
                flex: 0 0 18px;
                width: 18px !important;
                min-width: 18px;
                max-width: 18px;
                height: 18px !important;
                min-height: 18px;
                max-height: 18px;
                fill: none;
                stroke: currentColor;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .site-search-page-control.is-disabled {
                color: #a0aec0;
                background: #f8fafc;
                cursor: default;
            }

            .site-search-page-number.is-current {
                border-color: #004AAD;
                background: #004AAD;
                color: #fff;
                cursor: default;
            }

            .site-search-page-ellipsis {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 24px;
                width: 24px;
                color: #64748b;
            }

            .site-search-trending {
                display: grid;
                gap: 18px;
                margin-top: 36px;
            }

            .site-search-trending h2 {
                margin: 0;
                color: #0f172a;
                font-family: 'Sora', sans-serif;
            }

            .site-search-result-card {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 28px;
                border-radius: 24px;
                border: 1px solid rgba(15,23,42,0.06);
                text-decoration: none;
                transition: all 0.3s;
                background: white;
            }

            .site-search-result-card:hover {
                transform: translateX(8px);
                border-color: #004AAD;
                box-shadow: 0 20px 40px rgba(0, 74, 173, 0.08);
            }

            .site-search-result-subject {
                font-size: 0.7rem;
                font-weight: 800;
                color: #004AAD;
                text-transform: uppercase;
                letter-spacing: 0.15em;
                font-family: 'Sora', sans-serif;
            }

            .site-search-result-card h2 {
                font-size: 1.4rem;
                font-weight: 800;
                color: #0f172a;
                margin: 8px 0;
                font-family: 'Sora', sans-serif;
                letter-spacing: -0.01em;
            }

            .site-search-result-card p {
                color: #64748b;
                font-size: 0.95rem;
                margin: 0;
            }

            .site-search-status {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.75rem;
                font-weight: 800;
                padding: 8px 16px;
                border-radius: 12px;
                font-family: 'Sora', sans-serif;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .site-search-status.available {
                background: rgba(16,185,129,0.1);
                color: #059669;
            }

            .site-search-empty {
                text-align: center;
                padding: 100px 20px;
            }

            .site-search-empty h2 {
                font-size: 2.2rem;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 12px;
                font-family: 'Sora', sans-serif;
            }

            .site-search-empty p {
                color: #64748b;
                margin: 0 0 30px;
                font-size: 1.1rem;
            }

            .site-search-empty a {
                display: inline-block;
                padding: 14px 36px;
                background: #004AAD;
                color: white;
                border-radius: 16px;
                text-decoration: none;
                font-weight: 800;
                font-family: 'Sora', sans-serif;
                box-shadow: 0 10px 20px rgba(0, 74, 173, 0.2);
            }

            /* ─── RESPONSIVE ─── */
            @media (max-width: 768px) {
                .site-search-hero { padding: 40px 20px; }
                .site-search-copy h1 { font-size: 2rem; }
                .site-search-panel { padding: 16px; }
                .site-search-input-wrap { padding: 12px 16px; }
                .site-search-toolbar { flex-direction: column; gap: 16px; align-items: flex-start; }
                .site-search-result-card { flex-direction: column; align-items: flex-start; padding: 22px; }
                .site-search-status { margin-top: 12px; }
                .site-search-content { padding: 30px 16px; }
            }


            /* ─── SEARCH PAGE: RESPONSIVE OVERRIDES ─────────────────── */
            @media (max-width: 992px) {
                .site-search-page .site-search-hero {
                    padding: 64px 20px 48px;
                }

                .site-search-page .site-search-panel {
                    max-width: 720px;
                }
            }

            @media (max-width: 768px) {
                .site-search-page {
                    padding: 64px 18px 24px;
                    overflow-x: hidden;
                }

                .site-search-page .site-search-hero {
                    padding: 40px 16px 34px;
                }

                .site-search-page .site-search-copy h1 {
                    font-size: clamp(2rem, 10vw, 2.6rem);
                    overflow-wrap: anywhere;
                }

                .site-search-page .site-search-copy p {
                    font-size: 1rem;
                    line-height: 1.65;
                    margin-bottom: 28px;
                }

                .site-search-page .site-search-panel {
                    padding: 14px;
                    border-radius: 22px;
                }

                .site-search-page .site-search-input-wrap {
                    gap: 10px;
                    padding: 12px 14px;
                    border-radius: 16px;
                }

                .site-search-page .site-search-input-wrap input {
                    min-width: 0;
                    width: 100%;
                    font-size: 16px;
                }

                .site-search-page .site-search-clear {
                    flex-shrink: 0;
                }

                .site-search-page .site-search-filters {
                    justify-content: flex-start;
                    gap: 8px;
                    margin-top: 16px;
                    padding-bottom: 8px;
                    scrollbar-width: none;
                    -webkit-overflow-scrolling: touch;
                }

                .site-search-page .site-search-filters::-webkit-scrollbar {
                    display: none;
                }

                .site-search-page .site-search-filter {
                    padding: 9px 16px;
                    font-size: 0.8rem;
                    flex: 0 0 auto;
                }

                .site-search-page .site-search-content {
                    padding: 30px 16px 40px;
                }

                .site-search-page .site-search-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 14px;
                    margin-bottom: 24px;
                }

                .site-search-page .site-search-toolbar p {
                    line-height: 1.6;
                    overflow-wrap: anywhere;
                }

                .site-search-page .site-search-toolbar select {
                    width: 100%;
                    font-size: 16px;
                }

                .site-search-page .site-search-results {
                    gap: 14px;
                }

                .site-search-page .site-search-pagination {
                    margin-top: 20px !important;
                }

                .site-search-page .site-search-pagination-nav,
                .site-search-page .site-search-page-numbers {
                    gap: 4px;
                }

                .site-search-page .site-search-page-control,
                .site-search-page .site-search-page-number {
                    flex-basis: 36px;
                    width: 36px;
                    min-width: 36px;
                    height: 36px;
                }

                .site-search-page .site-search-page-ellipsis {
                    flex-basis: 18px;
                    width: 18px;
                }

                .site-search-page .site-search-result-card {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 14px;
                    padding: 22px;
                    border-radius: 20px;
                    min-width: 0;
                }

                .site-search-page .site-search-result-card > div:first-child {
                    min-width: 0;
                    width: 100%;
                }

                .site-search-page .site-search-result-card h2,
                .site-search-page .site-search-result-card p,
                .site-search-page .site-search-result-subject {
                    overflow-wrap: anywhere;
                }

                .site-search-page .site-search-result-card h2 {
                    font-size: 1.2rem;
                    line-height: 1.35;
                }

                .site-search-page .site-search-status {
                    margin-top: 0;
                    align-self: flex-start;
                }

                .site-search-page .restricted-search-item {
                    padding: 28px 18px !important;
                }

                .site-search-page .restricted-search-item .d-flex.justify-content-center.gap-2 {
                    width: 100%;
                    flex-direction: column;
                }

                .site-search-page .restricted-search-item .d-flex.justify-content-center.gap-2 > a {
                    width: 100%;
                }

                .site-search-page .site-search-empty {
                    padding: 70px 16px;
                }

                .site-search-page .site-search-empty h2 {
                    font-size: clamp(1.7rem, 8vw, 2.1rem);
                    overflow-wrap: anywhere;
                }

                .site-search-page .site-search-empty p {
                    font-size: 1rem;
                    line-height: 1.6;
                }
            }

            @media (max-width: 480px) {
                .site-search-page .site-search-hero {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .site-search-page .site-search-kicker {
                    font-size: 0.68rem;
                    letter-spacing: 0.09em;
                    white-space: normal;
                    line-height: 1.4;
                }

                .site-search-page .site-search-panel {
                    padding: 10px;
                    border-radius: 18px;
                }

                .site-search-page .site-search-input-wrap {
                    padding: 11px 12px;
                    gap: 8px;
                }

                .site-search-page .site-search-input-wrap svg {
                    width: 19px;
                    height: 19px;
                }

                .site-search-page .site-search-clear {
                    padding: 4px 7px;
                    font-size: 0.74rem;
                }

                .site-search-page .site-search-filter {
                    padding: 8px 14px;
                }

                .site-search-page .site-search-content {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .site-search-page .site-search-pagination-nav,
                .site-search-page .site-search-page-numbers {
                    gap: 2px;
                }

                .site-search-page .site-search-page-control,
                .site-search-page .site-search-page-number {
                    flex-basis: 32px;
                    width: 32px;
                    min-width: 32px;
                    height: 32px;
                }

                .site-search-page .site-search-result-card {
                    padding: 20px 18px;
                    border-radius: 18px;
                }

                .site-search-page .site-search-result-subject {
                    letter-spacing: 0.1em;
                }

                .site-search-page .site-search-empty {
                    padding: 56px 12px;
                }

                .site-search-page .site-search-empty a {
                    width: 100%;
                    padding: 13px 18px;
                }
            }

            @media (max-width: 767px) {
                .site-search-page .site-search-results {
                    grid-template-columns: minmax(0, 1fr);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                'use strict';

                /* ── Academic year filter ───────────────────────────────── */
                const yearSelect  = document.getElementById('yearFilter');

                if (yearSelect) {
                    yearSelect.addEventListener('change', () => {
                        const url = new URL(window.location.href);
                        if (yearSelect.value === 'all') {
                            url.searchParams.delete('year');
                        } else {
                            url.searchParams.set('year', yearSelect.value);
                        }
                        url.searchParams.delete('page');
                        window.location.assign(url);
                    });
                }
            })();
        </script>
    @endpush
@endsection
