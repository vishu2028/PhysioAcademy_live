@extends('layouts.frontend')

@section('title', 'Search by Subjects')

@section('content')
<div class="topics-subj-page">
  <x-page-hero
    title="Syllabus by Subject"
    subtitle="Complete physiotherapy curriculum organized by core subjects, units, and clinical modules."
    breadcrumbLabel="Subjects"
  >
    <x-slot name="icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </x-slot>
  </x-page-hero>

  <!-- STICKY SEARCH BAR -->
  <div class="tspage-sticky-search" id="stickySearch">
    <div class="tspage-search-wrapper">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #64748b; flex-shrink: 0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="topicSearch" placeholder="Search any topic, subject or unit...">
{{--      <div class="tspage-search-count" id="searchCount">Browse all modules</div>--}}
    </div>
  </div>

  <!-- SUBJECTS LIST -->
  <section class="tspage-list-section">
    <div class="tspage-list-inner">
      <div class="tspage-requested-box">
        <span class="tspage-hot-badge">🔥 Most Requested</span>
        <div class="tspage-requested-topics">
            @forelse($requestedTags as $tag)
                <a href="{{ route('search', ['query' => $tag]) }}">{{ $tag }}</a>
            @empty
                <span class="text-muted small">No requested topics yet.</span>
            @endforelse
        </div>
      </div>

      <div class="tspage-accordion-list" id="subjectAccordions">
        @php
            $visibleSubjects = auth()->check() ? $subjects : $subjects->take(ceil($subjects->count() / 2));
        @endphp

        @foreach($visibleSubjects as $subject)
        <div class="tspage-panel {{ $loop->first ? 'active' : '' }}">
            <div class="tspage-panel-header" onclick="this.parentElement.classList.toggle('active')">
                <div class="tspage-panel-info">
                    <span class="tspage-panel-icon">{{ $subject->icon ?: '📖' }}</span>
                    <div>
                        <h3>{{ $subject->name }}</h3>
                        <p>{{ $subject->topics_count }} Topics Available</p>
                    </div>
                </div>
                <div class="tspage-panel-toggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
            </div>
            <div class="tspage-panel-content">
                <div class="tspage-topics-grid">
                    @php
                        $subjectTopics = $subject->topics()->active()->whereNull('parent_id')->withCount('subtopics')->get();
                        $visibleTopics = auth()->check() ? $subjectTopics : $subjectTopics->take(ceil($subjectTopics->count() / 2));
                    @endphp

                    @foreach($visibleTopics as $topic)
                    <div class="tspage-topic-card">
                        <a href="{{ route('topics.show', ['slug' => $topic->slug]) }}" class="tspage-topic-item">
                            <div class="tspage-tic-info">
                                <strong>{{ $topic->title }}</strong>
                                <span>{{ $topic->academicYear->name ?? 'Core' }} • {{ $topic->module_number ?? 'Unit I' }}</span>
                            </div>
                            <div class="tspage-tic-status">
                                <span class="tspage-status-dot"></span> Available
                            </div>
                        </a>
                        @if($topic->subtopics_count > 0)
                        <div class="tspage-subtopics-list">
                            @foreach($topic->subtopics as $sub)
                                <a href="{{ route('topics.show', ['slug' => $sub->slug]) }}" class="tspage-subtopic-link">
                                    <i class="bi bi-arrow-return-right me-1"></i> {{ $sub->title }}
                                </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach

                    @guest
                    <div class="tspage-topic-card guest-locked-topics p-4 rounded-4 text-center d-flex flex-column justify-content-center border border-dashed border-primary bg-light">
                        <i class="bi bi-lock-fill text-primary mb-2 fs-4"></i>
                        <h6 class="fw-bold small mb-1">More Topics Locked</h6>
                        <p class="x-small text-muted mb-2">Create an account to view all modules in this subject.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill">Login Now</a>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
        @endforeach

        @guest
        <div class="tspage-panel p-5 text-center mt-4 border-dashed" style="border: 2px dashed #e2e8f0; border-radius: 28px;">
            <div class="mb-3"><i class="bi bi-book-half display-5 text-muted opacity-50"></i></div>
            <h3 class="fw-bold mb-2">Unlock More Subjects</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                You are currently viewing a limited selection of subjects. Sign in to your student account to unlock the full 2024 Physiotherapy curriculum.
            </p>
            <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-5 py-2">Join the Academy</a>
        </div>
        @endguest
{{--        <div class="tspage-empty-state">--}}
{{--            <div>📂</div>--}}
{{--            <h3>Library Syncing</h3>--}}
{{--            <p>We are currently updating our subject database.</p>--}}
{{--        </div>--}}
        </div>
      </div>

      <!-- REQUEST TOPIC CTA -->
      <div class="tspage-request-cta">
        <div class="tspage-cta-glow"></div>
        <div class="tspage-cta-content">
            <h2>Missing a specific topic?</h2>
            <p>Our academic team is constantly adding new clinical and theoretical modules. Let us know what you're looking for!</p>
            <a href="{{ route('search') }}">Request Topic Now</a>
        </div>
      </div>
    </div>
  </section>
</div>

@push('styles')
<style>
  /* ─── BY SUBJECT PAGE — FULLY SCOPED ────────────────── */
  .topics-subj-page {
      background: #fff;
      min-height: 100vh;
      font-family: 'Outfit', sans-serif;
      padding-top: 72px;
  }

  /* Sticky Search */
  .tspage-sticky-search {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(20px);
      padding: 15px 24px;
      border-bottom: 1px solid #f1f5f9;
  }

  .tspage-search-wrapper {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 15px;
      background: white;
      border: 1px solid #e2e8f0;
      padding: 12px 24px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.03);
      transition: all 0.3s;
  }
  .tspage-search-wrapper:focus-within {
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
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
  .tspage-list-section { padding: 40px 24px 100px; }
  .tspage-list-inner { max-width: 1100px; margin: 0 auto; }

  .tspage-requested-box {
      background: rgba(236,72,153,0.04);
      border: 1px solid rgba(236,72,153,0.1);
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
      color: #ec4899;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-family: 'Sora', sans-serif;
      white-space: nowrap;
  }

  .tspage-requested-topics { display: flex; flex-wrap: wrap; gap: 12px; }

  .tspage-requested-topics a {
      text-decoration: none;
      background: white;
      border: 1px solid rgba(236,72,153,0.15);
      color: #ec4899;
      padding: 6px 14px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.85rem;
      transition: all 0.2s;
  }
  .tspage-requested-topics a:hover {
      background: #ec4899;
      color: white;
      transform: translateY(-2px);
  }

  /* Accordion */
  .tspage-accordion-list { display: grid; gap: 20px; margin-bottom: 60px; }

  .tspage-panel {
      background: white;
      border: 1px solid #f1f5f9;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0,0,0,0.02);
      transition: all 0.3s;
  }
  .tspage-panel:hover {
      border-color: rgba(37,99,235,0.15);
      box-shadow: 0 20px 50px rgba(15,23,42,0.05);
  }

  .tspage-panel-header {
      padding: 24px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      user-select: none;
  }

  .tspage-panel-info { display: flex; align-items: center; gap: 20px; }

  .tspage-panel-icon {
      width: 54px;
      height: 54px;
      border-radius: 18px;
      background: rgba(37,99,235,0.08);
      display: grid;
      place-items: center;
      font-size: 1.8rem;
      flex-shrink: 0;
  }

  .tspage-panel-info h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.4rem;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
  }
  .tspage-panel-info p {
      margin: 4px 0 0;
      color: #94a3b8;
      font-weight: 700;
      font-size: 0.85rem;
  }

  .tspage-panel-toggle {
      color: #cbd5e1;
      transition: transform 0.3s;
  }
  .tspage-panel.active .tspage-panel-toggle {
      transform: rotate(180deg);
      color: #2563eb;
  }

  .tspage-panel-content {
      padding: 0 30px 30px;
      display: none;
  }
  .tspage-panel.active .tspage-panel-content { display: block; }

  .tspage-topics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 12px;
  }

  .tspage-topic-item {
      text-decoration: none;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 24px;
      border-radius: 18px;
      background: #f8fbff;
      border: 1px solid #edf2f7;
      transition: all 0.2s;
  }
  .tspage-topic-item:hover {
      background: #fff;
      border-color: #2563eb;
      transform: translateX(6px);
      box-shadow: 0 10px 20px rgba(37,99,235,0.05);
  }

  .tspage-tic-info strong {
      display: block;
      font-size: 1rem;
      font-weight: 800;
      color: #1e293b;
      margin-bottom: 2px;
      font-family: 'Sora', sans-serif;
  }
  .tspage-tic-info span {
      font-size: 0.75rem;
      color: #94a3b8;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
  }

  .tspage-tic-status {
      font-size: 0.75rem;
      font-weight: 800;
      color: #059669;
      display: flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
      flex-shrink: 0;
  }

  .tspage-status-dot {
      width: 6px;
      height: 6px;
      background: #10b981;
      border-radius: 50%;
      box-shadow: 0 0 8px #10b981;
      display: inline-block;
  }

  /* Subtopics */
  .tspage-subtopics-list {
      padding: 5px 0 10px 25px;
      display: flex;
      flex-direction: column;
      gap: 5px;
  }
  .tspage-subtopic-link {
      font-size: 0.8rem;
      color: #64748b;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
  }
  .tspage-subtopic-link:hover {
      color: #2563eb;
  }

  /* CTA */
  .tspage-request-cta {
      padding: 60px 40px;
      border-radius: 40px;
      background: #0f172a;
      color: white;
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
      background: #2563eb;
      border-radius: 50%;
      opacity: 0.2;
      filter: blur(60px);
  }

  .tspage-cta-content { position: relative; z-index: 1; }

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
      background: linear-gradient(135deg, #2563eb, #38bdf8);
      color: white;
      border-radius: 16px;
      text-decoration: none;
      font-weight: 800;
      font-family: 'Sora', sans-serif;
      box-shadow: 0 10px 30px rgba(37,99,235,0.3);
      transition: all 0.2s;
  }
  .tspage-cta-content a:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 40px rgba(37,99,235,0.4);
  }

  /* Empty */
  .tspage-empty-state {
      text-align: center;
      padding: 100px 40px;
      background: white;
      border: 1px solid #f1f5f9;
      border-radius: 28px;
  }
  .tspage-empty-state div:first-child { font-size: 4rem; margin-bottom: 20px; }
  .tspage-empty-state h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.8rem;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 12px;
  }
  .tspage-empty-state p { color: #64748b; }

  /* Responsive */
  @media (max-width: 768px) {
    .tspage-panel-header { padding: 20px; }
    .tspage-panel-info h3 { font-size: 1.1rem; }
    .tspage-panel-icon { width: 44px; height: 44px; font-size: 1.4rem; }
    .tspage-topics-grid { grid-template-columns: 1fr; }
    .tspage-requested-box { flex-direction: column; align-items: flex-start; }
    .tspage-panel-content { padding: 0 20px 20px; }
    .tspage-request-cta { padding: 40px 24px; }
    .tspage-cta-content h2 { font-size: 1.6rem; }
  }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('topicSearch')?.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        let found = 0;
        document.querySelectorAll('.tspage-topic-item').forEach(card => {
            const text = card.textContent.toLowerCase();
            const visible = text.includes(query);
            card.style.display = visible ? 'flex' : 'none';
            if (visible) found++;
        });
        document.getElementById('searchCount').textContent = query ? `${found} results found` : 'Browse all modules';
    });
</script>
@endpush
@endsection
