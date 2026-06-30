@extends('layouts.frontend')

@section('content')
<!-- HERO SECTION -->
<section class="hero" id="home">
  <div class="hero-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="hero-grid"></div>
    <div class="particle-field" id="particleField"></div>
  </div>

  <div class="hero-container">
    <div class="hero-content">
      <div class="hero-badge reveal-up">
        <span class="badge-dot"></span>
        <span>{{ $hero->badge ?? get_setting('hero_badge', 'New Curriculum 2024 — Fully Updated') }}</span>
      </div>

      <h1 class="hero-title reveal-up delay-1">
        @php
            $heroTitle = $hero->title ?? get_setting('hero_title', 'Your Academic Guide for Physiotherapy');
            $titleParts = explode('for', $heroTitle);
        @endphp
        @if(count($titleParts) > 1)
            {!! $titleParts[0] !!} <br/> for
            <span class="title-highlight">
              <span class="title-highlight-text">{!! trim($titleParts[1]) !!}</span>
              <svg class="title-underline" viewBox="0 0 300 12" preserveAspectRatio="none"><path d="M0,8 Q75,0 150,8 Q225,16 300,8" stroke="url(#underlineGrad)" stroke-width="3" fill="none"/><defs><linearGradient id="underlineGrad" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#2563eb"/><stop offset="100%" stop-color="#38bdf8"/></linearGradient></defs></svg>
            </span>
        @else
            {!! $heroTitle !!}
        @endif
      </h1>

      <p class="hero-subtitle reveal-up delay-2">
        {{ $hero->subtitle ?? get_setting('hero_subtitle', 'Navigate your syllabus, understand important topics, improve answer writing, and get academic support — all in one place.') }}
      </p>

      <div class="hero-actions reveal-up delay-3">
        @auth
            <a href="{{ $hero->button_url ?? route('topics.index') }}" class="btn-hero-primary">
              <span>{{ $hero->button_text ?? 'Explore Topics' }}</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="{{ $hero->secondary_button_url ?? '#ask-doubt' }}" class="btn-hero-secondary">
              <span>{{ $hero->secondary_button_text ?? 'Ask a Doubt' }}</span>
            </a>
        @else
            <a href="{{ route('register') }}" class="btn-hero-primary">
              <span>Start Learning for Free</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            </a>
            <a href="{{ route('login') }}" class="btn-hero-secondary">
              <span>Login to Account</span>
            </a>
        @endauth
      </div>

      <div class="hero-stats reveal-up delay-4">
        <div class="stat-item">
          <span class="stat-num" data-count="{{ \App\Models\Topic::count() ?: 500 }}">0</span><span class="stat-suffix">+</span>
          <span class="stat-label">Topics Covered</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <span class="stat-num" data-count="{{ \App\Models\Message::count() * 40 ?: 2400 }}">0</span><span class="stat-suffix">+</span>
          <span class="stat-label">Questions</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <span class="stat-num" data-count="{{ \App\Models\User::count() ?: 12 }}">0</span><span class="stat-suffix">K+</span>
          <span class="stat-label">Students Helped</span>
        </div>
      </div>
    </div>

    <div class="hero-visual reveal-right delay-2">
      <div class="hero-visual-image">
        <img src="{{'storage/' . $hero->image_path ? asset('storage/' .$hero->image_path) : asset('ui-physio/assets/two-colleagues-working-laptop_114579-2814.avif') }}" alt="">
      </div>

      <div class="visual-card-main glass-card">
        <div class="visual-card-header">
          <div class="vc-dots"><span></span><span></span><span></span></div>
          <span class="vc-label">Learning Dashboard</span>
        </div>
        <div class="visual-progress-list">
          <div class="vp-item">
            <span class="vp-name">Anatomy</span>
            <div class="vp-bar"><div class="vp-fill" style="--fill:78%"></div></div>
            <span class="vp-pct">78%</span>
          </div>
          <div class="vp-item">
            <span class="vp-name">Physiology</span>
            <div class="vp-bar"><div class="vp-fill" style="--fill:65%"></div></div>
            <span class="vp-pct">65%</span>
          </div>
          <div class="vp-item">
            <span class="vp-name">Biomechanics</span>
            <div class="vp-bar"><div class="vp-fill" style="--fill:52%"></div></div>
            <span class="vp-pct">52%</span>
          </div>
          <div class="vp-item">
            <span class="vp-name">Electrotherapy</span>
            <div class="vp-bar"><div class="vp-fill" style="--fill:40%"></div></div>
            <span class="vp-pct">40%</span>
          </div>
        </div>
      </div>

      <div class="float-card float-card-1 glass-card">
        <div class="fc-icon"><span class="ui-icon ui-icon-brain"></span></div>
        <div class="fc-info">
          <span class="fc-title">Brachial Plexus</span>
          <span class="fc-sub">Most Requested</span>
        </div>
        <div class="fc-badge hot"><span class="ui-icon ui-icon-flame"></span></div>
      </div>

      <div class="float-card float-card-2 glass-card">
        <div class="fc-icon"><span class="ui-icon ui-icon-trending"></span></div>
        <div class="fc-info">
          <span class="fc-title">Gait Cycle</span>
          <span class="fc-sub">Updated Guide</span>
        </div>
        <div class="fc-badge new">NEW</div>
      </div>

      <div class="float-card float-card-3 glass-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        <span>Active students right now: <strong>247</strong></span>
      </div>

      <div class="visual-ring visual-ring-1"></div>
      <div class="visual-ring visual-ring-2"></div>
    </div>
  </div>

  <div class="hero-scroll-hint">
    <div class="scroll-mouse"><div class="scroll-dot"></div></div>
    <span>Scroll to explore</span>
  </div>
</section>

<!-- CURRICULUM SECTION -->
@php
    $visibleYears = auth()->check() ? $years : $years->take(ceil($years->count() / 2));
    $restrictedYears = auth()->check() ? collect() : $years->slice(ceil($years->count() / 2));
@endphp
<section class="section curriculum-section" id="curriculum">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Curriculum</span>
      <h2 class="section-title">Start Learning <span class="text-gradient">by Year</span></h2>
      <p class="section-subtitle">Structured academic journey from first year through internship</p>
    </div>

    <div class="restriction-container">
        <div class="curriculum-grid reveal-stagger">
          {{-- Visible Years --}}
          @foreach($visibleYears as $index => $y)
          @php
            $colors = ['#2563eb', '#3b82f6', '#2563eb', '#f59e0b', '#10b981'];
            $color = $colors[$index % count($colors)];
            $topics_list = \App\Models\Topic::where('academic_year_id', $y->id)->with('subject')->take(3)->get();
            $unique_subjects = $topics_list->pluck('subject.name')->unique();
          @endphp
          <div class="curriculum-card {{ $index == 1 ? 'featured' : '' }}" data-tilt>
            @if($index == 1) <div class="cc-featured-label">Most Active</div> @endif
            <div class="cc-glow"></div>
            <div class="cc-icon" style="--icon-color:{{ $color }}">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
              </svg>
            </div>
            <div class="cc-year-badge" style="--badge-color:{{ $color }}">{{ $y->name }}</div>
            <h3>{{ $y->name }}</h3>
            <p>
                @if($unique_subjects->count() > 0)
                    {{ $unique_subjects->implode(', ') }}
                @else
                    Core modules and clinical focus areas.
                @endif
            </p>
            <div class="cc-subjects">
              @foreach($unique_subjects as $sub) <span>{{ $sub }}</span> @endforeach
            </div>
            <a href="{{ route('topics.year', ['year' => $y->slug]) }}" class="cc-btn">Explore <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
            <div class="cc-count">{{ $y->topics_count }} Topics</div>
          </div>
          @endforeach

          {{-- Restricted Years --}}
          @if($restrictedYears->count() > 0)
            @foreach($restrictedYears as $index => $y)
              <div class="curriculum-card blurred-content">
                <h3>{{ $y->name }}</h3>
                <div class="cc-count">Locked Content</div>
              </div>
            @endforeach
          @endif
        </div>

        @guest
        <div class="login-to-unlock">
            <div class="unlock-card reveal-up">
                <div class="unlock-icon"><i class="bi bi-lock-fill"></i></div>
                <h4 class="fw-bold mb-2">Login to Unlock Full Curriculum</h4>
                <p class="text-muted small mb-4">You're viewing a partial preview. Join thousands of students to access all years and subjects.</p>
                <a href="{{ route('login') }}" class="btn-hero-primary w-100 justify-content-center">Sign In to Continue</a>
            </div>
        </div>
        @endguest
    </div>
  </div>
</section>

<!-- ACADEMIC SUPPORT / PLATFORM FEATURES -->
<section class="section support-section" id="support">
  <div class="section-container">
    @php
        $visibleFeatures = auth()->check() ? $features : $features->take(ceil($features->count() / 2));
    @endphp
    <div class="restriction-container">
        <div class="support-grid reveal-stagger">
          @forelse($visibleFeatures as $feature)
          <div class="support-card">
            <div class="sc-border"></div>
            <div class="sc-sweep"></div>
            <div class="sc-icon-wrap" style="{{ $feature->color ? '--sc-color:'.$feature->color : '' }}">
              @if(str_contains($feature->icon, '<svg'))
                {!! $feature->icon !!}
              @else
                <i class="{{ $feature->icon }}"></i>
              @endif
            </div>
            <h3>{{ $feature->title }}</h3>
            <p>{{ auth()->check() ? $feature->description : Str::limit($feature->description, strlen($feature->description)/2) }}</p>
            <div class="sc-tag">{{ $feature->button_text ?? 'Explore →' }}</div>
          </div>
          @empty
          {{-- Fallback logic --}}
          @endforelse
        </div>
        @guest
        <div class="login-to-unlock" style="background: linear-gradient(to bottom, transparent 0%, #f8fbff 90%);">
            <a href="{{ route('login') }}" class="btn-hero-primary mb-4">Login to view all features</a>
        </div>
        @endguest
    </div>

    @guest
    <div class="text-center mt-5 reveal-up">
        <div class="guest-cta-card mx-auto glass-card">
            <div class="cta-content-inner">
                <h3>Unlock Full Academic Potential</h3>
                <p>Join thousands of students accessing structured notes, viva questions, and academic support.</p>
                <div class="guest-cta-actions">
                    <a href="{{ route('register') }}" class="btn-cta-primary">Join the Academy</a>
                    <a href="{{ route('login') }}" class="btn-cta-secondary">Login</a>
                </div>
            </div>
        </div>
    </div>
    @endguest
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="section testimonials-section" id="testimonials">
  <div class="section-container">
    <div class="section-header reveal-up text-center">
      <span class="section-tag">Testimonials</span>
      <h2 class="section-title">What Our <span class="text-gradient">Students Say</span></h2>
      <p class="section-subtitle">Join thousands of physiotherapy students who have transformed their learning experience</p>
    </div>

    <div class="restriction-container">
        <div class="testimonials-grid reveal-stagger mt-5">
          @php
            $visibleTestimonials = auth()->check() ? $testimonials : $testimonials->take(ceil($testimonials->count() / 2));
          @endphp
          @forelse($visibleTestimonials as $testimonial)
          <div class="testimonial-card glass-card">
            <div class="tc-quote"><i class="bi bi-quote"></i></div>
            <div class="tc-rating mb-3">
              @for($i = 0; $i < 5; $i++)
                <i class="bi bi-star-fill {{ $i < $testimonial->rating ? 'text-warning' : 'text-secondary opacity-25' }}"></i>
              @endfor
            </div>
            <p class="tc-content mb-4 text-white-50">"{{ auth()->check() ? $testimonial->content : Str::limit($testimonial->content, strlen($testimonial->content)/2) }}"</p>
            <div class="tc-user d-flex align-items-center gap-3">
              <div class="tcu-avatar">
                <img src="{{ $testimonial->client_image ? asset('storage/'.$testimonial->client_image) : 'https://ui-avatars.com/api/?name='.urlencode($testimonial->client_name).'&background=3b82f6&color=fff' }}" alt="{{ $testimonial->client_name }}" class="rounded-circle" width="48" height="48">
              </div>
              <div class="tcu-info">
                <h5 class="mb-0 fw-bold text-white">{{ $testimonial->client_name }}</h5>
                <small class="text-white-50">{{ $testimonial->client_designation }}</small>
              </div>
            </div>
          </div>
          @empty
          @endforelse
        </div>
        @guest
        <div class="login-to-unlock" style="background: linear-gradient(to bottom, transparent 0%, #f8fbff 95%);">
             <div class="unlock-card reveal-up py-4">
                <h4 class="fw-bold mb-3">Community Insights</h4>
                <p class="text-muted small mb-3">Join our community of students to read full stories and academic success reviews.</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-4">See More Reviews</a>
            </div>
        </div>
        @endguest
    </div>
  </div>
</section>

<!-- TRENDING TOPICS / HOT RIGHT NOW -->
<section class="section trending-section" id="topics">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag"><span class="ui-icon ui-icon-flame"></span> Hot Right Now</span>
      <h2 class="section-title">Most Requested <span class="text-gradient">Topics</span></h2>
      <p class="section-subtitle">These are what students are asking about most this week</p>
    </div>

    @php
        $visibleTrending = auth()->check() ? $trendingTopics : $trendingTopics->take(ceil($trendingTopics->count() / 2));
    @endphp
    <div class="restriction-container">
        <div class="trending-grid reveal-stagger">
          @forelse($visibleTrending as $index => $topic)
          <div class="trending-card">
            <div class="tc-glow" style="{{ $index == 1 ? '--tc-glow:#3b82f6' : ($index == 2 ? '--tc-glow:#f59e0b' : ($index == 3 ? '--tc-glow:#10b981' : '')) }}"></div>
            <div class="tc-header">
              <div class="tc-badge {{ $index == 0 ? 'trending-badge' : '' }}" style="{{ $index > 0 ? 'background:rgba(37,99,235,0.12);border-color:rgba(37,99,235,0.25);color:#2563eb' : '' }}">
                <span class="ui-icon ui-icon-{{ $index == 0 ? 'flame' : ($index == 1 ? 'trending' : ($index == 2 ? 'zap' : 'heart-pulse')) }}"></span>
                Trending #{{ $index + 1 }}
              </div>
              <div class="tc-requests"><span class="request-count" data-count="{{ 847 - ($index * 128) }}">0</span> requests</div>
            </div>
            <div class="tc-subject">{{ $topic->subject->name ?? 'General' }} • {{ $topic->academicYear->name ?? 'All Years' }}</div>
            <h3 class="tc-title">{{ $topic->title }}</h3>
            <p class="tc-desc">{{ auth()->check() ? Str::limit($topic->description, 100) : Str::limit($topic->description, 50) }}</p>
            <div class="tc-tags">
                @foreach(['Physio', 'Education', 'Guide'] as $tag)
                    <span>{{ $tag }}</span>
                @endforeach
            </div>
            <div class="tc-footer">
              <a href="{{ route('topics.show', ['slug' => $topic->slug]) }}" class="tc-explore-btn text-decoration-none">Explore Topic</a>
              <button class="tc-save-btn {{ $topic->isBookmarked() ? 'active' : '' }}"
                      onclick="toggleBookmark({{ $topic->id }}, 'Topic', this)"
                      aria-label="Save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" {!! $topic->isBookmarked() ? 'fill="currentColor"' : '' !!}/>
                </svg>
              </button>
            </div>
          </div>
          @empty
          {{-- Static Fallback --}}
          @endforelse
        </div>
        @guest
        <div class="login-to-unlock" style="background: linear-gradient(to bottom, transparent 0%, #f8fbff 90%);">
            <p class="mb-3 text-muted fw-bold">Explore over 500+ academic topics...</p>
            <a href="{{ route('login') }}" class="btn-hero-primary">Login to see all trending topics</a>
        </div>
        @endguest
    </div>

    <div class="trending-footer reveal-up">
      <a href="{{ route('topics.index') }}" class="btn-outline-glow" style="text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">View All Topics <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
    </div>
  </div>
</section>

<!-- LEARNING RESOURCES / MATERIALS -->
<section class="section resources-section">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Resources</span>
      <h2 class="section-title">Learning <span class="text-gradient">Materials</span></h2>
      <p class="section-subtitle">Structured learning aids to help you master every topic</p>
    </div>

    <div class="resources-grid reveal-stagger">
      <div class="resource-card rc-wide" data-tilt>
        <div class="rc-glow"></div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-zap"></span></div>
        <h3>Quick Guides</h3>
        <p>Concise, exam-focused summaries of every topic — built for last-minute revision and fast recall.</p>
        <div class="rc-stats"><span>240+ guides</span> <span class="rc-dot">•</span> <span>Updated weekly</span></div>
        <button class="rc-btn">Access Guides</button>
      </div>

      <div class="resource-card" data-tilt>
        <div class="rc-glow" style="--rc-glow:#3b82f6"></div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-book"></span></div>
        <h3>Theory Notes</h3>
        <p>In-depth academic notes with diagrams, mechanisms, and reference links.</p>
        <div class="rc-stats"><span>180+ notes</span></div>
        <button class="rc-btn">Read Notes</button>
      </div>

      <div class="resource-card" data-tilt>
        <div class="rc-glow" style="--rc-glow:#f59e0b"></div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-mic"></span></div>
        <h3>Viva Questions</h3>
        <p>Most asked viva questions with structured answers and examiner tips.</p>
        <div class="rc-stats"><span>600+ questions</span></div>
        <button class="rc-btn">Practice Viva</button>
      </div>

      <div class="resource-card" data-tilt>
        <div class="rc-glow" style="--rc-glow:#10b981"></div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-map"></span></div>
        <h3>Diagrams & Flowcharts</h3>
        <p>Visual learning aids, annotated diagrams, and memory maps for complex topics.</p>
        <div class="rc-stats"><span>320+ visuals</span></div>
        <button class="rc-btn">View Visuals</button>
      </div>

      <div class="resource-card coming-soon" data-tilt>
        <div class="rc-coming-badge">Coming Soon</div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-puzzle"></span></div>
        <h3>Interactive Quizzes</h3>
        <p>Test your knowledge with adaptive quiz engine with instant feedback.</p>
        <div class="rc-stats"><span>In development</span></div>
        <button class="rc-btn" disabled>Notify Me</button>
      </div>

      <div class="resource-card coming-soon" data-tilt>
        <div class="rc-coming-badge">Coming Soon</div>
        <div class="rc-icon-big"><span class="ui-icon ui-icon-hospital"></span></div>
        <h3>Clinical Cases</h3>
        <p>Real-world case studies with physiotherapy assessment and management approaches.</p>
        <div class="rc-stats"><span>In development</span></div>
        <button class="rc-btn" disabled>Notify Me</button>
      </div>
    </div>
  </div>
</section>

<!-- ASK DOUBT / ACADEMIC SUPPORT -->
<section class="section ask-doubt-section" id="ask-doubt">
  <div class="section-container">
    <div class="ask-doubt-wrapper reveal-up">
      <div class="ask-doubt-info">
        <span class="section-tag">Academic Support</span>
        <h2 class="section-title">Have a <span class="text-gradient">Doubt?</span></h2>
        <p class="section-subtitle">Submit your physiotherapy doubts and get structured academic explanations with clinical connections.</p>

        <div class="ask-doubt-features">
          <div class="adf-item">
            <div class="adf-dot"></div>
            <span>Response within academic timeline</span>
          </div>
          <div class="adf-item">
            <div class="adf-dot" style="--dot-color:#3b82f6"></div>
            <span>Structured explanations with references</span>
          </div>
          <div class="adf-item">
            <div class="adf-dot" style="--dot-color:#10b981"></div>
            <span>Clinical relevance always included</span>
          </div>
        </div>
      </div>

      <div class="ask-doubt-form glass-card">
        <div class="form-glow"></div>
        <h3 class="form-title">Submit Your Doubt</h3>

        <form @auth action="{{ route('contact.submit') }}" @else action="{{ route('register') }}" @endauth method="POST">
          @csrf
          <div class="form-group">
            <label class="float-label">Academic Year</label>
            <select class="form-select" id="doubtYear" name="year">
              <option value="">Select Year</option>
              <option>First Year</option>
              <option>Second Year</option>
              <option>Third Year</option>
              <option>Fourth Year</option>
              <option>Internship</option>
            </select>
          </div>

          <div class="form-group">
            <label class="float-label">Subject</label>
            <select class="form-select" id="doubtSubject" name="subject">
              <option value="">Select Subject</option>
              <option>Anatomy</option>
              <option>Physiology</option>
              <option>Biomechanics</option>
              <option>Electrotherapy</option>
              <option>Neurology</option>
              <option>Orthopaedics</option>
            </select>
          </div>

          <div class="form-group">
            <label class="float-label">Topic</label>
            <input type="text" class="form-input" id="doubtTopic" name="topic" placeholder="e.g. Brachial Plexus" />
          </div>

          <div class="form-group">
            <label class="float-label">Your Doubt</label>
            <textarea class="form-textarea" id="doubtMessage" name="message" placeholder="Describe your doubt in detail..."></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-form-primary" id="submitDoubt">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Ask Doubt
            </button>
            <button type="button" class="btn-form-secondary" onclick="document.getElementById('authOverlay').classList.add('active')">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Request Topic
            </button>
          </div>
        </form>

        <div class="form-success" id="formSuccess" style="display:none">
          <div class="success-icon"><span class="ui-icon ui-icon-check"></span></div>
          <h4>Doubt Submitted!</h4>
          <p>We'll get back to you with a structured academic response.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- EXAM AID / EXAM PREP -->
<section class="section exam-section" id="exam-aid">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Exam Prep</span>
      <h2 class="section-title">Exam <span class="text-gradient">Aid Center</span></h2>
      <p class="section-subtitle">Comprehensive exam preparation toolkit for all physio exams</p>
    </div>

    <div class="exam-grid reveal-stagger">
      <div class="exam-card exam-card-large">
        <div class="exam-card-bg"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-edit"></span></div>
        <h3>Question Bank</h3>
        <p>Year-wise, subject-wise organized questions with pattern analysis and frequency mapping.</p>
        <div class="exam-stats">
          <div><span class="es-num">2400+</span><span class="es-label">Questions</span></div>
          <div><span class="es-num">98%</span><span class="es-label">Coverage</span></div>
        </div>
        <a href="{{ route('exam-aid') }}" class="exam-btn" style="text-decoration: none; display: inline-block; text-align: center;">Open Question Bank</a>
      </div>

      <div class="exam-card">
        <div class="exam-card-bg" style="--ec-color:#3b82f6"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-mic"></span></div>
        <h3>Viva Mastery</h3>
        <p>Most asked viva questions by examiners across universities with model answers.</p>
        <button class="exam-btn">Practice Viva</button>
      </div>

      <div class="exam-card">
        <div class="exam-card-bg" style="--ec-color:#f59e0b"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-trending"></span></div>
        <h3>Expected Questions</h3>
        <p>AI-assisted prediction of most likely exam questions based on trend analysis.</p>
        <button class="exam-btn">View Expected</button>
      </div>

      <div class="exam-card">
        <div class="exam-card-bg" style="--ec-color:#10b981"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-check"></span></div>
        <h3>Important Topics</h3>
        <p>Curated list of must-prepare topics that consistently appear in university exams.</p>
        <button class="exam-btn">See Topics</button>
      </div>

      <div class="exam-card">
        <div class="exam-card-bg" style="--ec-color:#ec4899"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-lightbulb"></span></div>
        <h3>Exam Tips</h3>
        <p>Proven exam strategies, time management, answer presentation, and scoring techniques.</p>
        <button class="exam-btn">Get Tips</button>
      </div>

      <div class="exam-card">
        <div class="exam-card-bg" style="--ec-color:#6366f1"></div>
        <div class="exam-icon"><span class="ui-icon ui-icon-calendar"></span></div>
        <h3>Study Strategies</h3>
        <p>Subject-wise study plans, spaced repetition schedules, and revision timetables.</p>
        <button class="exam-btn">Plan Study</button>
      </div>
    </div>
  </div>
</section>

<!-- COMMUNITY ACTIVITY / LIVE ACTIVITY -->
<section class="section community-section" id="community">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Live Activity</span>
      <h2 class="section-title">Community & <span class="text-gradient">Announcements</span></h2>
    </div>

    <div class="community-layout reveal-up">
      <div class="community-feed">
        <div class="feed-header">
          <span class="feed-title">Recent Activity</span>
          <span class="live-indicator"><span class="live-dot"></span>Live</span>
        </div>
        <div class="feed-items">
          <div class="feed-item">
            <div class="feed-item-icon new-upload"><span class="ui-icon ui-icon-folder"></span></div>
            <div class="feed-item-content">
              <span class="feed-item-title">New Notes Added: Electrotherapy Module 3</span>
              <span class="feed-item-meta">2 hours ago • Year 3</span>
            </div>
          </div>
          <div class="feed-item">
            <div class="feed-item-icon trending-up"><span class="ui-icon ui-icon-flame"></span></div>
            <div class="feed-item-content">
              <span class="feed-item-title">Brachial Plexus guide updated with new diagrams</span>
              <span class="feed-item-meta">5 hours ago • Anatomy</span>
            </div>
          </div>
          <div class="feed-item">
            <div class="feed-item-icon question"><span class="ui-icon ui-icon-help"></span></div>
            <div class="feed-item-content">
              <span class="feed-item-title">32 new doubts answered this week</span>
              <span class="feed-item-meta">Today • All Subjects</span>
            </div>
          </div>
          <div class="feed-item">
            <div class="feed-item-icon update"><span class="ui-icon ui-icon-refresh"></span></div>
            <div class="feed-item-content">
              <span class="feed-item-title">Curriculum update: New competency topics added</span>
              <span class="feed-item-meta">Yesterday • Curriculum</span>
            </div>
          </div>
          <div class="feed-item">
            <div class="feed-item-icon announcement"><span class="ui-icon ui-icon-megaphone"></span></div>
            <div class="feed-item-content">
              <span class="feed-item-title">Gait Cycle flowchart now available in Resources</span>
              <span class="feed-item-meta">2 days ago • Biomechanics</span>
            </div>
          </div>
        </div>
      </div>

      <div class="community-right">
        <div class="announcements-card glass-card">
          <div class="ann-header">
            <span><span class="ui-icon ui-icon-megaphone"></span> Announcements</span>
            <span class="ann-count">3 new</span>
          </div>
          <div class="ann-items">
            <div class="ann-item">
              <div class="ann-dot" style="--dot:#2563eb"></div>
              <div>
                <span class="ann-title">New 2024 Syllabus Fully Mapped</span>
                <span class="ann-date">Jan 15, 2025</span>
              </div>
            </div>
            <div class="ann-item">
              <div class="ann-dot" style="--dot:#3b82f6"></div>
              <div>
                <span class="ann-title">University Exam Pattern Analysis Released</span>
                <span class="ann-date">Jan 10, 2025</span>
              </div>
            </div>
            <div class="ann-item">
              <div class="ann-dot" style="--dot:#10b981"></div>
              <div>
                <span class="ann-title">Clinical Cases Section Coming Soon</span>
                <span class="ann-date">Jan 5, 2025</span>
              </div>
            </div>
          </div>
        </div>

        <div class="trending-ticker glass-card">
          <div class="ticker-header"><span class="ui-icon ui-icon-flame"></span> Trending</div>
          <div class="ticker-track" id="ticker">
            <span>Brachial Plexus</span>
            <span>Gait Cycle</span>
            <span>UMN vs LMN</span>
            <span>Muscle Spindle</span>
            <span>Reflex Arc</span>
            <span>Spinal Cord Tracts</span>
            <span>Muscle Contraction</span>
            <span>Cerebellum Functions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT SECTION -->
<section class="section about-section" id="about">
  <div class="section-container">
    <div class="about-layout">
      <div class="about-left reveal-left">
        <span class="section-tag">About {{ get_setting('site_name', 'Physio Academy') }}</span>
        <h2 class="section-title">{!! get_setting('about_title', 'Built for <span class="text-gradient">Physio Students</span><br/>By Physio Students') !!}</h2>
        <p class="about-para">{{ auth()->check() ? get_setting('about_description_1', 'Physio Academy was created to bridge the gap between textbook knowledge and real examination performance. We understand the challenges of physiotherapy education — complex topics, unclear answer formats, and limited academic guidance.') : Str::limit(get_setting('about_description_1', 'Physio Academy was created to bridge the gap between textbook knowledge and real examination performance.'), 80) }}</p>
        <p class="about-para">{{ auth()->check() ? get_setting('about_description_2', 'Our platform is completely aligned with the latest 2024 curriculum, offering topic-by-topic academic support, viva preparation, and answer writing guidance tailored specifically for physiotherapy students.') : Str::limit(get_setting('about_description_2', 'Our platform is completely aligned with the latest 2024 curriculum, offering topic-by-topic academic support...'), 80) }}</p>

        @guest
        <a href="javascript:void(0)" onclick="document.getElementById('authOverlay').classList.add('active')" class="text-primary small fw-bold d-block mb-4">Login to read full mission details →</a>
        @endguest

        <div class="about-counters">
          <div class="ac-item">
            <span class="ac-num" data-count="{{ \App\Models\Topic::count() ?: 500 }}">0</span><span>+</span>
            <span class="ac-label">Topics</span>
          </div>
          <div class="ac-item">
            <span class="ac-num" data-count="{{ \App\Models\Message::count() * 40 ?: 2400 }}">0</span><span>+</span>
            <span class="ac-label">Questions</span>
          </div>
          <div class="ac-item">
            <span class="ac-num" data-count="{{ \App\Models\User::count() ?: 12 }}">0</span><span>K+</span>
            <span class="ac-label">Students</span>
          </div>
        </div>

        <a href="{{ route('about') }}" class="btn-about-cta" style="text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">{{ get_setting('about_cta_text', 'Learn Our Mission') }} <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>

      <div class="about-right reveal-right">
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-content glass-card">
              <span class="tl-year">2024</span>
              <h4>New Curriculum Coverage</h4>
              <p>Complete mapping of revised BPT curriculum with subject-wise topic organization.</p>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot" style="--dot:#3b82f6"></div>
            <div class="tl-content glass-card">
              <span class="tl-year">Q2 2024</span>
              <h4>Exam Aid Launch</h4>
              <p>Question bank, viva preparation, and answer writing guides went live.</p>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot" style="--dot:#10b981"></div>
            <div class="tl-content glass-card">
              <span class="tl-year">Q3 2024</span>
              <h4>12,000+ Students</h4>
              <p>Platform crossed 12K active physiotherapy students across India.</p>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot" style="--dot:#f59e0b"></div>
            <div class="tl-content glass-card">
              <span class="tl-year">2025</span>
              <h4>Clinical Cases & Quizzes</h4>
              <p>Interactive learning modules and clinical case studies coming soon.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@guest
<section class="section guest-cta-final py-5 mt-5">
    <div class="section-container text-center">
        <div class="guest-cta-card mx-auto glass-card">
            <div class="cta-content-inner">
                <h2>Ready to Master Physiotherapy?</h2>
                <p>Create your free student account today and get instant access to the complete topic repository, academic notes, and examiner-vetted viva prep.</p>
                <div class="guest-cta-actions">
                    <a href="{{ route('register') }}" class="btn-cta-primary">Join the Academy now</a>
                    <a href="{{ route('login') }}" class="btn-cta-secondary">Student Login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endguest

@push('styles')
<style>
    /* TESTIMONIALS STYLING */
    .testimonials-section {
        position: relative;
        padding: 100px 0;
        background: radial-gradient(circle at 10% 50%, rgba(37,99,235,0.03), transparent 30%),
                    radial-gradient(circle at 90% 80%, rgba(56,189,248,0.03), transparent 30%);
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        padding-top: 20px;
    }

    .testimonial-card {
        padding: 40px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03)) !important;
        border: 1px solid rgba(255,255,255,0.08) !important;
        position: relative;
        overflow: hidden;
    }

    .testimonial-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: linear-gradient(90deg, #2563eb, #38bdf8);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .testimonial-card:hover::before {
        opacity: 1;
    }

    .tc-quote {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 3rem;
        color: rgba(37,99,235,0.05);
        transform: rotate(10deg);
    }

    .tc-rating i {
        font-size: 0.9rem;
    }

    .tc-content {
        font-size: 1.05rem;
        font-style: italic;
        line-height: 1.7;
        color: var(--text-secondary);
    }

    .tcu-avatar img {
        border: 2px solid rgba(37,99,235,0.2);
        padding: 2px;
        background: #fff;
    }

    .tcu-info h5 {
        font-family: var(--font-display);
        letter-spacing: -0.01em;
        color: var(--text-primary) !important;
    }

    .tcu-info small {
        color: var(--text-muted) !important;
    }

    @media (max-width: 768px) {
        .testimonials-grid {
            grid-template-columns: 1fr;
        }
    }

    /* CONTENT RESTRICTION */
    .restriction-container {
        position: relative;
        overflow: hidden;
    }

    .blurred-content {
        filter: blur(8px);
        pointer-events: none;
        user-select: none;
        opacity: 0.6;
        mask-image: linear-gradient(to bottom, black 0%, transparent 80%);
    }

    .login-to-unlock {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding-bottom: 40px;
        background: linear-gradient(to bottom, transparent 0%, rgba(248, 251, 255, 0.95) 80%);
        z-index: 10;
        text-align: center;
    }

    .unlock-card {
        background: white;
        padding: 30px 40px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
        border: 1px solid rgba(37, 99, 235, 0.1);
        max-width: 400px;
    }

    .unlock-icon {
        width: 60px;
        height: 60px;
        background: rgba(37, 99, 235, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #2563eb;
        font-size: 1.5rem;
    }
</style>
@endpush

<style>
    .tc-save-btn.active { color: #2563eb; }
</style>

<script>
    function toggleBookmark(id, type, btn) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif

        const path = btn.querySelector('path');
        const isActive = btn.classList.contains('active');

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
                btn.classList.add('active');
                path.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('active');
                path.removeAttribute('fill');
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endsection
