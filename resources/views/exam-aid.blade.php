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
        @php $content = $section->content; @endphp

        @if($section->type === 'exam_hero')
        <section class="exam-hero">
          <div class="exam-hero-copy">
            <div class="exam-kicker reveal-up">
              <span></span>
              {{ $content['kicker'] ?? 'Exam Aid Studio' }}
            </div>
            <h1 class="exam-hero-title reveal-up delay-1">{{ $content['title'] ?? '' }}</h1>
            <p class="exam-hero-text reveal-up delay-2">{{ $content['description'] ?? '' }}</p>
            <div class="exam-hero-actions reveal-up delay-3">
              @if(!empty($content['primary_cta_text']))
              <a href="{{ $content['primary_cta_url'] ?? '#' }}" class="exam-primary-btn text-decoration-none">
                {{ $content['primary_cta_text'] }}
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </a>
              @endif
              @if(!empty($content['secondary_cta_text']))
              <a href="{{ $content['secondary_cta_url'] ?? '#' }}" class="exam-secondary-btn text-decoration-none">{{ $content['secondary_cta_text'] }}</a>
              @endif
            </div>
            <div class="exam-quick-select reveal-stagger">
              @foreach($content['quick_links'] ?? [] as $link)
              <button onclick="window.location.href='{{ $link['url'] }}'"><span>{{ $link['icon_num'] }}</span>{{ $link['label'] }}</button>
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
                  <strong>{{ $content['readiness_score'] ?? rand(80, 95) }}%</strong>
                </div>
                <div class="exam-pulse"></div>
              </div>
              <div class="exam-progress-stack">
                @foreach($content['progress_items'] ?? [] as $progress)
                <div><span>{{ $progress['label'] }}</span><b style="--w:{{ $progress['width'] }}"></b></div>
                @endforeach
              </div>
              <div class="exam-mini-metrics">
                <span><strong>{{ $content['stats']['papers'] ?? 0 }}</strong>Papers</span>
                <span><strong>{{ $content['stats']['questions'] ?? 0 }}</strong>Questions</span>
                <span><strong>{{ $content['stats']['guides'] ?? 0 }}</strong>Guides</span>
              </div>
            </div>
            @if(isset($content['floating_cards'][0]))
            <div class="exam-float-card exam-float-one shadow-sm">{{ $content['floating_cards'][0] }}</div>
            @endif
            @if(isset($content['floating_cards'][1]))
            <div class="exam-float-card exam-float-two shadow-sm">{{ $content['floating_cards'][1] }}</div>
            @endif
          </div>
        </section>
        @endif

        @if($section->type === 'exam_filters')
        <section class="exam-section exam-selector-section" id="college-selector">
          <div class="exam-section-head reveal-up">
            <span class="exam-section-eyebrow">{{ $content['eyebrow'] ?? 'Smart Filters' }}</span>
            <h2>{{ $content['title'] ?? '' }}</h2>
            <p>{{ $content['description'] ?? '' }}</p>
          </div>

          <div class="exam-filter-panel reveal-up delay-1">
            <label class="exam-field">
              <span>University</span>
              <select id="examCollegeFilter">
                <option value="all">All Universities</option>
                <option value="national">National University</option>
              </select>
            </label>
            <label class="exam-field">
              <span>Year</span>
              <select id="examYearFilter" onchange="window.location.href='{{ route('topics.index') }}?year=' + this.value">
                <option value="all">All Years</option>
                <option value="1">First Year</option>
                <option value="2">Second Year</option>
                <option value="3">Third Year</option>
                <option value="4">Final Year</option>
              </select>
            </label>
            <label class="exam-field">
              <span>Resource Type</span>
              <select id="examSemesterFilter">
                <option value="all">All Resources</option>
                <option value="papers">PYQs</option>
                <option value="viva">Viva Sets</option>
              </select>
            </label>
            <label class="exam-field exam-field-search">
              <span>Search Subject</span>
              <form action="{{ route('search') }}" method="GET" style="width: 100%;">
                <input name="query" type="search" placeholder="Search Anatomy..." autocomplete="off" style="width: 100%; border:none; background:transparent; outline:none; padding: 0;">
              </form>
            </label>
          </div>
        </section>
        @endif

        @if($section->type === 'exam_resources')
        <section class="exam-section" id="exam-resources">
          <div class="exam-section-head reveal-up">
            <span class="exam-section-eyebrow">{{ $content['eyebrow'] ?? 'Resource Library' }}</span>
            <h2>{{ $content['title'] ?? '' }}</h2>
            <p>{{ $content['description'] ?? '' }}</p>
          </div>

          <div class="exam-resource-grid reveal-stagger">
            {{-- Render custom items if they exist for this section --}}
            @foreach($section->items as $item)
            @php $meta = $item->meta; @endphp
            <article class="exam-subject-card">
              <div class="exam-card-top">
                <span class="exam-difficulty {{ $meta['difficulty'] ?? 'medium' }}">{{ $meta['label'] ?? 'Academic Note' }}</span>
                <span>{{ $meta['category'] ?? 'Resource' }}</span>
              </div>
              <h3>{{ $item->title }}</h3>
              <p>{{ $item->body }}</p>
              <div class="exam-card-meta"><span>{{ $meta['stat1'] ?? '' }}</span><span>{{ $meta['stat2'] ?? '' }}</span></div>
              <div class="exam-card-actions">
                @if(!empty($meta['action_url']))
                  @auth
                    <button onclick="window.location.href='{{ $meta['action_url'] }}'">View</button>
                  @else
                    <button onclick="window.location.href='{{ route('login') }}'"><i class="bi bi-lock-fill me-1"></i> Unlock</button>
                  @endauth
                @endif
                
                @auth
                  <button>Download</button>
                @else
                   <button onclick="window.location.href='{{ route('login') }}'"><i class="bi bi-lock-fill me-1"></i> Download</button>
                @endauth
              </div>
            </article>
            @endforeach

            {{-- Fallback / Mixed data from FAQs and Topics as per original design --}}
            @if($section->items->count() == 0)
                @foreach($faqs as $faq)
                <article class="exam-subject-card">
                  <div class="exam-card-top">
                    <span class="exam-difficulty {{ $loop->index % 2 == 0 ? 'easy' : 'medium' }}">Academic FAQ</span>
                    <span>Support</span>
                  </div>
                  <h3>{{ $faq->question }}</h3>
                  <p>{{ Str::limit($faq->answer, 120) }}</p>
                  <div class="exam-card-meta"><span>Topper Verified</span><span>Ready for Exam</span></div>
                  
                  <button class="exam-accordion-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $faq->id }}">
                    Full Answer Preview <span>+</span>
                  </button>
                  <div class="collapse" id="faq-{{ $faq->id }}">
                    <div class="p-3 text-secondary small">
                        @auth
                            {{ $faq->answer }}
                        @else
                            {{ Str::limit($faq->answer, strlen($faq->answer)/2) }}
                            <div class="mt-2">
                                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Login to view full answer →</a>
                            </div>
                        @endauth
                    </div>
                  </div>
                </article>
                @endforeach
                
                @php
                    $topics = \App\Models\Topic::active()->limit(4)->get();
                @endphp
                @foreach($topics as $topic)
                <article class="exam-subject-card">
                  <div class="exam-card-top">
                    <span class="exam-difficulty hard">Subject Notes</span>
                    <span>{{ $topic->academicYear->name ?? 'Core' }}</span>
                  </div>
                  <h3 class="text-truncate">{{ $topic->title }}</h3>
                  <p>{{ auth()->check() ? Str::limit($topic->description, 100) : Str::limit($topic->description, 50) }}</p>
                  <div class="exam-card-meta"><span>14 papers</span><span>32 viva prompts</span></div>
                  <div class="exam-card-actions">
                    <button onclick="window.location.href='{{ route('topics.show', ['slug' => $topic->slug]) }}'">
                        @auth View @else Preview @endauth
                    </button>
                    @auth
                        <button>Download</button>
                    @else
                        <button onclick="window.location.href='{{ route('login') }}'"><i class="bi bi-lock-fill"></i></button>
                    @endauth
                  </div>
                </article>
                @endforeach
            @endif
          </div>
        </section>
        @endif
    @endforeach
</main>

@push('styles')
<style>
    /* EXAM AID SCOPED STYLES */
    .exam-shell-wrapper { position: relative; padding-top: 72px; min-height: 100vh; overflow-x: hidden; background: #f8fbff; }
    .exam-bg { position: absolute; inset: 0 0 auto; height: 780px; overflow: hidden; pointer-events: none; }
    .exam-grid-overlay { position: absolute; inset: 0; background-image: linear-gradient(rgba(37,99,235,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(37,99,235,0.05) 1px, transparent 1px); background-size: 50px 50px; mask-image: radial-gradient(circle at center, black, transparent 80%); }
    .exam-orb { position: absolute; filter: blur(100px); opacity: 0.3; border-radius: 50%; }
    .exam-orb-one { width: 500px; height: 500px; top: -100px; left: -100px; background: rgba(37,99,235,0.2); }
    .exam-orb-two { width: 400px; height: 400px; top: 200px; right: -50px; background: rgba(56,189,248,0.2); }
    .exam-orb-three { width: 300px; height: 300px; bottom: 0; left: 20%; background: rgba(37,99,235,0.1); }
    
    .exam-hero { max-width: 1280px; margin: 0 auto; padding: 80px 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .exam-kicker { display: inline-flex; align-items: center; gap: 10px; padding: 7px 16px; background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.15); border-radius: 99px; font-size: 0.8rem; font-weight: 600; color: #2563eb; margin-bottom: 24px; }
    .exam-hero-title { font-family: var(--font-display); font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 24px; }
    .exam-hero-text { font-size: 1.1rem; color: #64748b; margin-bottom: 40px; max-width: 540px; }
    .exam-hero-actions { display: flex; gap: 14px; margin-bottom: 40px; }
    .exam-primary-btn { background: #2563eb; color: #fff; padding: 14px 28px; border-radius: 12px; font-weight: 700; }
    .exam-primary-btn:hover { background: #1d4ed8; color: #fff; }
    .exam-secondary-btn { background: #fff; border: 1px solid rgba(59,130,246,0.12); color: #64748b; padding: 14px 28px; border-radius: 12px; font-weight: 700; }
    .exam-quick-select { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; max-width: 500px; }
    .exam-quick-select button { padding: 14px; background: #fff; border: 1px solid rgba(59,130,246,0.1); border-radius: 12px; text-align: left; font-size: 0.85rem; font-weight: 600; }
    .exam-quick-select button span { color: #2563eb; font-size: 0.7rem; margin-right: 8px; }

    .exam-hero-visual { position: relative; }
    .ui-illustration-exam { width: 100%; height: 400px; background: url('https://illustrations.popsy.co/blue/learning.svg') no-repeat center; background-size: contain; }
    .exam-dashboard-card { position: absolute; bottom: -20px; left: -20px; background: #fff; padding: 24px; border-radius: 20px; border: 1px solid rgba(59,130,246,0.1); box-shadow: 0 25px 60px rgba(0,0,0,0.1); width: 280px; }
    .exam-dashboard-top strong { font-size: 2.5rem; display: block; color: #0f172a; }
    .exam-progress-stack { margin: 15px 0; display: flex; flex-direction: column; gap: 8px; }
    .exam-progress-stack b { height: 6px; background: #f1f5f9; border-radius: 3px; position: relative; overflow: hidden; }
    .exam-progress-stack b::after { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: var(--w); background: #2563eb; }

    .exam-section { max-width: 1280px; margin: 0 auto; padding: 80px 24px; }
    .exam-filter-panel { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; padding: 25px; background: #fff; border-radius: 20px; border: 1px solid rgba(59,130,246,0.1); box-shadow: 0 15px 40px rgba(0,0,0,0.03); }
    .exam-resource-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .exam-subject-card { background: #fff; border-radius: 20px; border: 1px solid rgba(59,130,246,0.1); padding: 24px; transition: transform 0.3s; }
    .exam-subject-card:hover { transform: translateY(-5px); }

    @media (max-width: 1024px) {
        .exam-hero { grid-template-columns: 1fr; }
        .exam-resource-grid { grid-template-columns: 1fr 1fr; }
        .exam-status-bar { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .exam-resource-grid, .exam-filter-panel { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
