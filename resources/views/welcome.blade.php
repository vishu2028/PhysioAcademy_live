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

@auth
<!-- CURRICULUM SECTION -->
<section class="section curriculum-section" id="curriculum">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Curriculum</span>
      <h2 class="section-title">Start Learning <span class="text-gradient">by Year</span></h2>
      <p class="section-subtitle">Structured academic journey from first year through internship</p>
    </div>

    <div class="curriculum-grid reveal-stagger">
      @foreach($years as $index => $y)
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
    </div>
  </div>
</section>
@endauth

<!-- ACADEMIC SUPPORT / PLATFORM FEATURES -->
<section class="section support-section" id="support">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag">Platform Features</span>
      <h2 class="section-title">Academic <span class="text-gradient">Support System</span></h2>
      <p class="section-subtitle">Everything you need to ace your physio exams and clinical training</p>
    </div>

    <div class="support-grid reveal-stagger">
      @forelse($features as $feature)
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
        <p>{{ $feature->description }}</p>
        <div class="sc-tag">{{ $feature->button_text ?? 'Explore →' }}</div>
      </div>
      @empty
      {{-- Static Fallback if no features in DB --}}
      <div class="support-card">
        <div class="sc-border"></div>
        <div class="sc-sweep"></div>
        <div class="sc-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <h3>Topic Navigation</h3>
        <p>Browse the complete physiotherapy syllabus year-by-year, subject-by-subject with intelligent search.</p>
        <div class="sc-tag">Browse →</div>
      </div>
      @endforelse
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

@auth
<!-- TRENDING TOPICS / HOT RIGHT NOW -->
<section class="section trending-section" id="topics">
  <div class="section-container">
    <div class="section-header reveal-up">
      <span class="section-tag"><span class="ui-icon ui-icon-flame"></span> Hot Right Now</span>
      <h2 class="section-title">Most Requested <span class="text-gradient">Topics</span></h2>
      <p class="section-subtitle">These are what students are asking about most this week</p>
    </div>

    <div class="trending-grid reveal-stagger">
      @forelse($trendingTopics as $index => $topic)
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
        <p class="tc-desc">{{ Str::limit($topic->description, 100) }}</p>
        <div class="tc-tags">
            @foreach(['Physio', 'Education', 'Guide'] as $tag)
                <span>{{ $tag }}</span>
            @endforeach
        </div>
        <div class="tc-footer">
          <a href="{{ route('topics.show', ['slug' => $topic->slug]) }}" class="tc-explore-btn text-decoration-none">Explore Topic</a>
          <button class="tc-save-btn" aria-label="Save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          </button>
        </div>
      </div>
      @empty
      <div class="trending-card">
        <div class="tc-glow"></div>
        <div class="tc-header">
          <div class="tc-badge trending-badge"><span class="ui-icon ui-icon-flame"></span> Trending #1</div>
          <div class="tc-requests"><span class="request-count" data-count="847">0</span> requests</div>
        </div>
        <div class="tc-subject">Anatomy • Year 1</div>
        <h3 class="tc-title">Brachial Plexus</h3>
        <p class="tc-desc">Formation, branches, clinical correlations, and injury patterns — the most exam-heavy anatomy topic.</p>
        <div class="tc-tags"><span>Nerves</span><span>Anatomy</span><span>Clinical</span></div>
        <div class="tc-footer">
          <button class="tc-explore-btn">Explore Topic</button>
          <button class="tc-save-btn" aria-label="Save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          </button>
        </div>
      </div>
      @endforelse
    </div>

    <div class="trending-footer reveal-up">
      <a href="{{ route('topics.index') }}" class="btn-outline-glow" style="text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">View All Topics <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
    </div>
  </div>
</section>
@endauth

@auth
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
@endauth

@auth
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
@endauth

@auth
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
@endauth

@auth
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
@endauth

@auth
<!-- ABOUT SECTION -->
<section class="section about-section" id="about">
  <div class="section-container">
    <div class="about-layout">
      <div class="about-left reveal-left">
        <span class="section-tag">About {{ get_setting('site_name', 'Physio Academy') }}</span>
        <h2 class="section-title">{!! get_setting('about_title', 'Built for <span class="text-gradient">Physio Students</span><br/>By Physio Students') !!}</h2>
        <p class="about-para">{{ get_setting('about_description_1', 'Physio Academy was created to bridge the gap between textbook knowledge and real examination performance. We understand the challenges of physiotherapy education — complex topics, unclear answer formats, and limited academic guidance.') }}</p>
        <p class="about-para">{{ get_setting('about_description_2', 'Our platform is completely aligned with the latest 2024 curriculum, offering topic-by-topic academic support, viva preparation, and answer writing guidance tailored specifically for physiotherapy students.') }}</p>

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
@endauth

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
@endsection
