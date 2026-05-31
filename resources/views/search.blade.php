@extends('layouts.frontend')

@section('title', 'Search Topics')

@section('content')
<main class="site-search-page">
  <section class="site-search-hero">
    <div class="site-search-copy">
      <span class="site-search-kicker"><span class="site-search-dot"></span> Topic Finder</span>
      <h1>Search Topics</h1>
      <p>Find syllabus topics, subjects, units, and study pages across Physio Academy.</p>
    </div>

    <div class="site-search-panel">
      <form action="{{ route('search') }}" method="GET" class="site-search-input-wrap">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" name="query" value="{{ $query }}" placeholder="Search topics, subjects, units..." autocomplete="off" autofocus id="siteSearchInput">
        @if($query)
            <a href="{{ route('search') }}" class="site-search-clear">Clear</a>
        @endif
      </form>

    <div class="site-search-filters" aria-label="Search filters">
        <button class="site-search-filter active" type="button" data-filter="all">All</button>
        <button class="site-search-filter" type="button" data-filter="available">Available</button>
        <button class="site-search-filter" type="button" data-filter="progress">In Progress</button>
        <button class="site-search-filter" type="button" data-filter="developing">Developing</button>
      </div>
    </div>
  </section>

  <section class="site-search-content">
    <div class="site-search-toolbar">
      <p id="searchResultCount">
        @if($query)
            <span id="resultCountText">{{ $results->count() }}</span> result{{ $results->count() === 1 ? '' : 's' }} found for "{{ $query }}"
        @else
            Showing trending topics
        @endif
      </p>
      <select id="yearFilter" aria-label="Filter by year">
        <option value="all">All years</option>
        @foreach(\App\Models\AcademicYear::active()->orderBy('order')->get() as $yr)
          <option value="{{ $yr->name }}">{{ $yr->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="popular-searches" aria-label="Popular searches">
        @foreach(['Brachial Plexus', 'Gait Cycle', 'Wound Healing', 'Patient Management', 'Hospital Rotations'] as $tag)
            <a href="{{ route('search', ['query' => $tag]) }}">
                <button type="button">{{ $tag }}</button>
            </a>
        @endforeach
    </div>

    <div class="site-search-results" id="siteSearchResults">
@php
    $visibleResults = auth()->check() ? $results : $results->take(ceil($results->count() / 2) ?: 5);
@endphp

@forelse($visibleResults as $topic)
<a class="site-search-result-card"
   href="{{ route('topics.show', ['slug' => $topic->slug]) }}"
   data-year="{{ $topic->academicYear->name ?? '' }}"
   data-subject="{{ $topic->subject->name ?? '' }}">
  <div>
    <span class="site-search-result-subject">{{ $topic->subject->name ?? 'General' }}</span>
    <h2>{{ $topic->title }}</h2>
    <p>{{ $topic->academicYear->name ?? 'All Years' }}</p>
  </div>
  <span class="site-search-status available">✓ Available</span>
</a>
@empty
    @if($query)
    <div class="site-search-empty" id="siteSearchEmpty">
        <h2>No matching topics found</h2>
        <p>Try a subject name, unit name, or a shorter keyword.</p>
        <a href="{{ route('topics.index') }}">Browse all topics</a>
    </div>
    @else
    <div class="site-search-empty" id="siteSearchEmpty">
        <h2>No topics available yet</h2>
        <p>Check back soon as we add more content.</p>
        <a href="{{ route('home') }}">Go to Homepage</a>
    </div>
    @endif
@endforelse
    </div>

@guest
    @if($results->count() > $visibleResults->count())
    <div class="site-search-result-card restricted-search-item mt-3 p-5 text-center d-flex flex-column" style="border: 2px dashed #e2e8f0; background: #f8fbff;">
        <div class="mb-3"><i class="bi bi-lock-fill display-6 text-primary"></i></div>
        <h3 class="fw-bold fs-5 mb-2">More results are locked</h3>
        <p class="text-muted small mb-4 mx-auto" style="max-width: 400px;">We found more relevant topics for your search, but full access is reserved for registered students.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-4">Login</a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">Register</a>
        </div>
    </div>
    @endif
@endguest
  </section>
</main>

@push('styles')
<style>
  /* ─── SEARCH PAGE — FULLY SCOPED ──────────────────────── */
  .site-search-page {
      min-height: 100vh;
      background: #fff;
      font-family: 'Outfit', sans-serif;
      padding-top: 72px;
  }

  .site-search-hero {
      padding: 80px 24px 60px;
      text-align: center;
      background: radial-gradient(circle at 50% 0%, rgba(37,99,235,0.05), transparent 70%);
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
      background: #2563eb;
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
      border: 1px solid rgba(37,99,235,0.1);
      box-shadow: 0 30px 60px rgba(15,23,42,0.05);
  }

  .site-search-input-wrap {
      display: flex;
      align-items: center;
      gap: 16px;
      background: white;
      padding: 14px 24px;
      border-radius: 20px;
      border: 1px solid rgba(37,99,235,0.15);
      transition: all 0.3s;
  }

  .site-search-input-wrap:focus-within {
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
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
      border: 1px solid #e2e8f0;
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
  }

  .site-search-filter.active {
      background: #0f172a;
      color: white;
      border-color: #0f172a;
  }
  
  .site-search-content {
      max-width: 1100px;
      margin: 0 auto;
      padding: 60px 24px;
  }

  .site-search-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      border-bottom: 1px solid #f1f5f9;
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
      border: 1px solid #e2e8f0;
      background: white;
      font-weight: 700;
      color: #475569;
      font-family: 'Outfit', sans-serif;
      outline: none;
      cursor: pointer;
  }
  
  .popular-searches {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 40px;
  }

  .popular-searches a { text-decoration: none; }

  .popular-searches button {
      padding: 10px 18px;
      border-radius: 12px;
      border: 1px solid rgba(37,99,235,0.1);
      background: rgba(37,99,235,0.03);
      color: #2563eb;
      font-weight: 700;
      font-size: 0.85rem;
      transition: all 0.2s;
      font-family: 'Sora', sans-serif;
      cursor: pointer;
  }

  .popular-searches button:hover {
      background: #2563eb;
      color: white;
      transform: translateY(-2px);
  }
  
  .site-search-results { display: grid; gap: 18px; }

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
      border-color: #2563eb;
      box-shadow: 0 20px 40px rgba(37,99,235,0.08);
  }

  .site-search-result-subject {
      font-size: 0.7rem;
      font-weight: 800;
      color: #2563eb;
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
      background: #2563eb;
      color: white;
      border-radius: 16px;
      text-decoration: none;
      font-weight: 800;
      font-family: 'Sora', sans-serif;
      box-shadow: 0 10px 20px rgba(37,99,235,0.2);
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
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  /* ── Year Filter ────────────────────────────────────────── */
  const yearSelect  = document.getElementById('yearFilter');
  const resultsGrid = document.getElementById('siteSearchResults');
  const countText   = document.getElementById('resultCountText');

  function applyYearFilter() {
    if (!yearSelect || !resultsGrid) return;
    const selected = yearSelect.value;
    const cards    = resultsGrid.querySelectorAll('.site-search-result-card');
    let visible    = 0;

    cards.forEach(card => {
      const cardYear = (card.dataset.year || '').trim();
      const show     = selected === 'all' || cardYear === selected;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    // Update count text
    if (countText) {
      countText.textContent = visible;
    }

    // Toggle empty state
    let emptyEl = document.getElementById('dynamicEmptyState');
    if (visible === 0) {
      if (!emptyEl) {
        emptyEl = document.createElement('div');
        emptyEl.id = 'dynamicEmptyState';
        emptyEl.className = 'site-search-empty';
        emptyEl.innerHTML = '<h2>No topics for this year</h2><p>Try selecting a different year or clear the filter.</p>';
        resultsGrid.after(emptyEl);
      }
      emptyEl.style.display = 'block';
    } else {
      if (emptyEl) emptyEl.style.display = 'none';
    }
  }

  if (yearSelect) {
    yearSelect.addEventListener('change', applyYearFilter);
  }

  /* ── Search Input Live Redirect ─────────────────────────── */
  const searchInput  = document.getElementById('siteSearchInput');
  const searchPageUrl = document.querySelector('meta[name="search-url"]')?.content || '/search';

  if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const q = searchInput.value.trim();
        if (q.length >= 2) {
          window.location.href = searchPageUrl + '?query=' + encodeURIComponent(q);
        }
      }, 600);
    });

    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        clearTimeout(timer);
        const q = searchInput.value.trim();
        if (q) {
          window.location.href = searchPageUrl + '?query=' + encodeURIComponent(q);
        }
      }
    });
  }

  /* ── Filter Tabs (visual only) ──────────────────────────── */
  document.querySelectorAll('.site-search-filter').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.site-search-filter').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

})();
</script>
@endpush
@endsection
