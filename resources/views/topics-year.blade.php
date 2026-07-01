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
        <a href="{{ route('topics.year', ['year' => $y->slug]) }}" class="typage-card {{ ($currentYear->id == $y->id) ? 'active' : '' }}">
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
              <span class="typage-info-value">{{ $y->topics_count }}+</span>
            </div>
          </div>
        </a>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- SUBJECTS EXPLORER -->
  <section class="typage-explorer">
    <div class="typage-explorer-inner">
        <div class="typage-hero-card">
            <div class="typage-hero-copy">
                <div class="typage-chip">{{ $currentYear->name }} Curriculum</div>
                <h2 class="typage-hero-title">{{ $currentYear->name }} <br>Academic Syllabus</h2>
                <p class="typage-hero-subtitle">{{ $currentYear->description ?: 'Comprehensive coverage of core subjects including theory, practical applications, and exam preparation guides for '.$currentYear->name.' students.' }}</p>
                <div class="typage-hero-stats">
                    <div class="typage-stat"><strong>{{ $topics->count() }}</strong><span>Subjects</span></div>
                    <div class="typage-stat"><strong>{{ $topics->flatten()->count() }}</strong><span>Topics</span></div>
                </div>
            </div>
            <div class="typage-hero-visual">
                <div class="typage-body-map"></div>
                <div class="typage-label-pill one">Skeletal System</div>
                <div class="typage-label-pill two">Muscular Flow</div>
                <div class="typage-label-pill three">Nervous Supply</div>
            </div>
        </div>

        <div class="typage-syllabus-grid">
            @php
                $visibleSubjects = auth()->check() ? $topics : $topics->take(ceil($topics->count() / 2));
            @endphp

            @forelse($visibleSubjects as $subjectName => $items)
            <div class="typage-subject-panel">
                <div class="typage-subject-head">
                    <div>
                        <h3>{{ $subjectName }}</h3>
                        @php
                            $totalCount = $items->count() + $items->sum(fn($i) => $i->subtopics->count());
                        @endphp
                        <p>{{ $totalCount }} active modules in this subject</p>
                    </div>
                    <span class="typage-subject-icon">📚</span>
                </div>

                <div class="typage-topic-cloud">
                    @php
                        $visibleItems = auth()->check() ? $items : $items->take(ceil($items->count() / 2));
                    @endphp

                    @foreach($visibleItems as $item)
                    <div class="typage-topic-group">
                        <div class="typage-topic-chip-wrapper">
                            <a href="{{ route('topics.show', ['slug' => $item->slug]) }}" class="typage-topic-chip">
                                {{ $item->title }}
                            </a>
                            <button onclick="toggleBookmark({{ $item->id }}, 'Topic', this)"
                                    class="typage-mini-bookmark {{ $item->isBookmarked() ? 'active' : '' }}"
                                    title="Bookmark Topic">
                                <i class="bi {{ $item->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                            </button>
                        </div>
                        @if($item->subtopics->count() > 0)
                            <div class="typage-sub-chips">
                                @foreach($item->subtopics as $sub)
                                    <div class="typage-sub-chip-wrapper">
                                        <a href="{{ route('topics.show', ['slug' => $sub->slug]) }}" class="typage-sub-chip">
                                            {{ $sub->title }}
                                        </a>
                                        <button onclick="toggleBookmark({{ $sub->id }}, 'Topic', this)"
                                                class="typage-micro-bookmark {{ $sub->isBookmarked() ? 'active' : '' }}"
                                                title="Bookmark Subtopic">
                                            <i class="bi {{ $sub->isBookmarked() ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @endforeach

                    @guest
                        <div class="typage-topic-group">
                             <div class="typage-topic-chip px-4 py-2 border-dashed bg-light text-muted small">
                                <i class="bi bi-lock-fill me-1"></i> More topics locked...
                             </div>
                        </div>
                    @endguest
                </div>
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
        <div class="typage-subject-panel p-5 text-center mt-4" style="background: linear-gradient(135deg, #fff, #f8fbff); border: 2px dashed #cbd5e1;">
            <div class="mb-4">
                <i class="bi bi-shield-lock display-4 text-blue-600"></i>
            </div>
            <h3 class="fw-bold mb-3">Academic Access Restricted</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
                We have over <strong>{{ $topics->flatten()->count() }}</strong> modules for {{ $currentYear->name }} available.
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
</div>

@push('styles')
<style>
  /* ─── BY YEAR PAGE — FULLY SCOPED ───────────────────── */
  .topics-year-page {
      background: #fff;
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
      border: 1px solid #f1f5f9;
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
      border-color: rgba(37,99,235,0.3);
      box-shadow: 0 20px 40px rgba(37,99,235,0.08);
  }
  .typage-card.active {
      border-color: #2563eb;
      background: rgba(37,99,235,0.02);
      box-shadow: 0 20px 40px rgba(37,99,235,0.1);
  }

  .typage-year-number {
      font-family: 'Sora', sans-serif;
      font-size: 3.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #2563eb, #38bdf8);
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
      border-top: 1px solid #f1f5f9;
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
      color: #2563eb;
      font-family: 'Sora', sans-serif;
  }

  /* Explorer */
  .typage-explorer { padding: 40px 24px 100px; }
  .typage-explorer-inner { max-width: 1200px; margin: 0 auto; }

  .typage-hero-card {
      padding: 50px;
      border-radius: 40px;
      border: 1px solid rgba(37,99,235,0.1);
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
      background: rgba(37,99,235,0.08);
      color: #2563eb;
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
      border: 1px solid #f1f5f9;
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
      background: rgba(37,99,235,0.02);
      border-radius: 32px;
      border: 1px dashed rgba(37,99,235,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
  }
  .typage-body-map {
      width: 140px;
      height: 260px;
      background: rgba(37,99,235,0.08);
      border-radius: 70px 70px 60px 60px;
      border: 2px solid rgba(37,99,235,0.2);
  }
  .typage-label-pill {
      position: absolute;
      background: white;
      padding: 8px 14px;
      border-radius: 999px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      border: 1px solid #f1f5f9;
      color: #2563eb;
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
      border: 1px solid #f1f5f9;
      border-radius: 32px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.03);
      padding: 30px;
      transition: all 0.3s;
  }
  .typage-subject-panel:hover {
      border-color: rgba(37,99,235,0.2);
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
      background: rgba(37,99,235,0.06);
      display: grid;
      place-items: center;
      font-size: 1.5rem;
      flex-shrink: 0;
  }

  .typage-topic-cloud { display: flex; flex-wrap: wrap; gap: 12px; }

  .typage-topic-chip {
      padding: 14px 22px;
      border-radius: 18px;
      color: #1e40af;
      background: rgba(37,99,235,0.07);
      border: 1px solid rgba(37,99,235,0.1);
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s;
      font-size: 0.95rem;
  }
  .typage-topic-chip:hover {
      background: #2563eb;
      color: white;
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(37,99,235,0.2);
      border-color: #2563eb;
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
      border-left: 2px solid #f1f5f9;
      margin-bottom: 10px;
  }
  .typage-sub-chip {
      text-decoration: none;
      padding: 4px 10px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.7rem;
      font-weight: 700;
      color: #64748b;
      transition: all 0.2s;
  }
  .typage-sub-chip:hover {
      background: white;
      border-color: #2563eb;
      color: #2563eb;
  }

  .typage-empty-panel {
      text-align: center;
      padding: 100px 40px;
      background: white;
      border: 1px solid #f1f5f9;
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
      background: rgba(37,99,235,0.08);
      color: #2563eb;
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
  .typage-mini-bookmark:hover, .typage-micro-bookmark:hover { color: #2563eb; transform: scale(1.2); }
  .typage-mini-bookmark.active, .typage-micro-bookmark.active { color: #2563eb; }
  .typage-mini-bookmark i { font-size: 1.1rem; }
  .typage-micro-bookmark i { font-size: 0.9rem; }
</style>

<script>
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
