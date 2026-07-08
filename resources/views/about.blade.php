@extends('layouts.frontend')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description)

@section('content')
<div class="about-page">
    <div id="particleField" class="particle-field"></div>
    <div class="about-shell">
        {{-- MISSION SECTION --}}
        <section class="about-section">
            <div class="about-container section-block reveal-up" id="mission-section">
                <p class="section-kicker"><span class="dot"></span> Our Mission</p>
                <div class="section-divider"></div>
                <div class="mission-layout">
                    <div class="mission-media reveal-left">
                        <div class="media-main"></div>
                        <div class="media-thumb thumb-1"></div>
                        <div class="media-thumb thumb-2"></div>
                        <div class="media-thumb thumb-3 d-flex align-items-center justify-content-center">
                            <span class="fs-2 text-primary"><i class="bi bi-play-fill"></i></span>
                        </div>
                    </div>

                    <div class="mission-copy glass-card reveal-right">
                        <h2 class="section-heading">Our Mission</h2>

                        <div class="divider-line"></div>

                        <div class="cms-dynamic-content">
                            {!! $page->content !!}
                        </div>

                        <div class="mission-pills">
                            <span class="mission-pill">Structured Learning</span>
                            <span class="mission-pill">Exam Guidance</span>
                            <span class="mission-pill">Topic Navigation</span>
                            <span class="mission-pill">Student Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- WHY SECTION --}}
        @php
            $whySection = $sections->get('why-we-built-this');
            $whySubtext = $whySection ? ($whySection->content['subtext'] ?? 'A modern learning platform should make the syllabus feel navigable, reduce exam anxiety, and present content with the same clarity expected from a polished startup product.') : 'A modern learning platform should make the syllabus feel navigable, reduce exam anxiety, and present content with the same clarity expected from a polished startup product.';
            $whyItems = $whySection ? $whySection->items->where('enabled', true) : collect();
            $defaultWhyItems = [
                ['icon' => 'bi-check-lg', 'title' => 'Simplify the syllabus', 'body' => 'Bring structure to dense academic content with a cleaner, more deliberate learning flow.'],
                ['icon' => 'bi-check-lg', 'title' => 'Support the new curriculum', 'body' => 'Align the platform around the latest needs of physiotherapy students and evolving subjects.'],
                ['icon' => 'bi-check-lg', 'title' => 'Improve answer writing', 'body' => 'Make exam preparation more actionable with focused support for structured responses.'],
                ['icon' => 'bi-check-lg', 'title' => 'Provide structured learning', 'body' => 'Organize knowledge into approachable, modern pathways instead of scattered notes.'],
                ['icon' => 'bi-check-lg', 'title' => 'Reduce confusion during exams', 'body' => 'Offer a calmer, more navigable experience for revision, recall, and topic discovery.'],
                ['icon' => 'bi-check-lg', 'title' => 'Create a student-driven platform', 'body' => 'Let student demand shape the roadmap so the product stays relevant to real learning needs.'],
            ];
        @endphp
        <section class="about-section">
            <div class="about-container section-block reveal-up delay-1" id="why-section">
                <p class="section-kicker">
                    <span class="dot"></span> Why We Built This
                </p>

                <h2 class="section-heading">
                    Why We Built <span class="accent">This</span>
                </h2>

                <div class="section-divider"></div>

                <p class="section-subtext">
                    {!! $whySubtext !!}
                </p>

                <div class="restriction-container">
                    <div class="feature-grid">
                        @php
                            // Guest aur logged-in dono users ke liye full list public
                            $displayWhyItems = $whyItems->count() > 0
                                ? $whyItems
                                : collect($defaultWhyItems);
                        @endphp

                        @foreach($displayWhyItems as $index => $item)
                            @php
                                $delays = ['delay-1', 'delay-2', 'delay-3'];
                                $d = $delays[$index % 3];

                                $item = (object) $item;

                                $icon = $item->meta['icon'] ?? ($item->icon ?? 'bi-check-lg');
                                $title = $item->title ?? '';
                                $body = $item->body ?? '';
                            @endphp

                            <article class="feature-card glass-card reveal-up {{ $d }}">
                                <div class="feature-icon">
                                    <i class="bi {{ $icon }}" aria-hidden="true"></i>
                                </div>

                                <h3>{!! $title !!}</h3>

                                <p>{!! $body !!}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- EXPLORE SECTION --}}
        @php
            $exploreSection = $sections->get('what-you-can-explore');
            $exploreSubtext = $exploreSection ? ($exploreSection->content['subtext'] ?? 'Navigate by syllabus, search topics quickly, practice from exam resources, and move beyond theory with clear clinical relevance.') : 'Navigate by syllabus, search topics quickly, practice from exam resources, and move beyond theory with clear clinical relevance.';
            $exploreItems = $exploreSection ? $exploreSection->items->where('enabled', true) : collect();
            $defaultExploreItems = [
                ['icon' => 'bi-folder2-open', 'title' => 'Navigate by Syllabus', 'body' => 'Follow a structured path tailored to your university curriculum.'],
                ['icon' => 'bi-search', 'title' => 'Search Topics Easily', 'body' => 'Find specific physiotherapy concepts in seconds with a focused search experience.'],
                ['icon' => 'bi-book', 'title' => 'Access Question Bank', 'body' => 'Practice from a comprehensive repository of previous exam questions.'],
                ['icon' => 'bi-pencil-square', 'title' => 'Learn Answer Writing', 'body' => 'Master the art of presenting clinical answers to score higher in exams.'],
                ['icon' => 'bi-chat-dots', 'title' => 'Ask Academic Doubts', 'body' => 'Get help from the community and simplify tricky concepts faster.'],
                ['icon' => 'bi-megaphone', 'title' => 'Request Important Topics', 'body' => "Tell us what's difficult, and we'll prioritize the content you need most."],
            ];
        @endphp
        <section class="about-section">
            <div class="about-container section-block reveal-up delay-2" id="explore-section">
                <p class="section-kicker">
                    <span class="dot"></span> What You Can Explore
                </p>

                <h2 class="section-heading">
                    What You Can <span class="accent">Explore</span>
                </h2>

                <div class="section-divider"></div>

                <p class="section-subtext">
                    {!! $exploreSubtext !!}
                </p>

                <div class="restriction-container">
                    <div class="explore-grid">
                        @php
                            // Guest aur logged-in dono users ke liye full explore items public
                            $displayExploreItems = $exploreItems->count() > 0
                                ? $exploreItems
                                : collect($defaultExploreItems);
                        @endphp

                        @foreach($displayExploreItems as $index => $item)
                            @php
                                $delays = ['delay-1', 'delay-2', 'delay-3'];
                                $d = $delays[$index % 3];

                                $icon = data_get($item, 'meta.icon', data_get($item, 'icon', 'bi-folder2-open'));
                                $title = data_get($item, 'title', '');
                                $body = data_get($item, 'body', '');
                            @endphp

                            <article class="explore-card glass-card reveal-up {{ $d }}">
                                <div class="explore-card-icon">
                                    <i class="bi {{ $icon }}" aria-hidden="true"></i>
                                </div>

                                <div>
                                    <h3>{!! $title !!}</h3>
                                    <p>{!! $body !!}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- STUDENT DRIVEN SECTION --}}
        @php
            $studentSection = $sections->get('built-around-student-needs');
            $studentHeading = $studentSection ? ($studentSection->content['heading'] ?? 'Built Around Student Needs') : 'Built Around Student Needs';
            $studentBody = $studentSection ? ($studentSection->content['body'] ?? "We believe the best learning platform is one built by the people using it. Our roadmap isn't static; topics are prioritized based on real-time student requests.") : "We believe the best learning platform is one built by the people using it. Our roadmap isn't static; topics are prioritized based on real-time student requests.";
            $studentItems = $studentSection ? $studentSection->items->where('enabled', true) : collect();
            $defaultStudentItems = [
                ['title' => 'Brachial Plexus', 'body' => 'High student demand', 'meta' => ['count' => '42']],
                ['title' => 'Gait Cycle', 'body' => 'Revision priority', 'meta' => ['count' => '36']],
                ['title' => 'UMN vs LMN Lesions', 'body' => 'Exam focus topic', 'meta' => ['count' => '31']],
                ["title" => "Erb's Palsy", 'body' => 'Clinical relevance', 'meta' => ['count' => '27']],
            ];
        @endphp
        <section class="about-section">
            <div class="about-container section-block reveal-up delay-2" id="student-driven-section">
                <p class="section-kicker"><span class="dot"></span> Student-Driven Learning</p>
                <div class="section-divider"></div>
                <div class="split-section">
                    <div class="requests-panel glass-card reveal-left">
                        <p class="section-kicker" style="margin-bottom: 8px;"><span class="dot"></span> Trending Requests</p>
                        <div class="request-list">
                            @if($studentItems->count() > 0)
                                @foreach($studentItems as $item)
                                    <div class="request-item"><div><strong>{!! $item->title !!}</strong><span>{!! $item->body !!}</span></div><div class="request-count">{{ $item->meta['count'] ?? '' }}</div></div>
                                @endforeach
                            @else
                                @foreach($defaultStudentItems as $item)
                                    <div class="request-item"><div><strong>{{ $item['title'] }}</strong><span>{{ $item['body'] }}</span></div><div class="request-count">{{ $item['meta']['count'] }}</div></div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="split-copy glass-card reveal-right">
                        <p class="section-kicker"><span class="dot"></span> Built Around Student Needs</p>
                        <h2 class="section-heading">Built Around <span class="accent">Student Needs</span></h2>
                        <div class="divider-line"></div>
                        <p>{!! $studentBody !!}</p>
                        <a href="{{ route('topics.index') }}" class="cta-button">Request a Topic</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- VISION SECTION --}}
        @php
            $visionSection = $sections->get('our-vision');
            $visionHeading = $visionSection ? ($visionSection->content['heading'] ?? 'Our Vision') : 'Our Vision';
            $visionBody = $visionSection ? ($visionSection->content['body'] ?? 'To create a modern academic ecosystem for physiotherapy students that seamlessly combines structured learning, expert academic guidance, and competency-focused education in one accessible place.') : 'To create a modern academic ecosystem for physiotherapy students that seamlessly combines structured learning, expert academic guidance, and competency-focused education in one accessible place.';
        @endphp
        <section class="about-section">
            <div class="about-container section-block reveal-up delay-3" id="vision-section">
                <p class="section-kicker"><span class="dot"></span> Our Vision</p>
                <div class="section-divider"></div>
                <div class="vision-banner">
                    <h2 class="section-heading" style="text-align:center; margin-bottom: 16px;">{!! $visionHeading !!}</h2>
                    <p>{!! $visionBody !!}</p>
                </div>
            </div>
        </section>

        {{-- CLOSING SECTION --}}
        @php
            $closingSection = $sections->get('learn-smarter-study-with-clarity');
            $closingHeading = $closingSection ? ($closingSection->content['heading'] ?? 'Learn Smarter. Study with Clarity.') : 'Learn Smarter. Study with Clarity.';
            $closingKicker = $closingSection ? ($closingSection->content['kicker'] ?? 'Closing Banner') : 'Closing Banner';
        @endphp
        <section class="about-section mb-5" id="closing-banner-section">
            <div class="about-container reveal-up delay-2">
                <div class="closing-minimal glass-card">
                    <div>
                        <p class="section-kicker" style="margin-bottom: 10px;"><span class="dot"></span> {!! $closingKicker !!}</p>
                        <h2>{!! $closingHeading !!}</h2>
                    </div>
                    <div class="closing-actions">
                        <a href="{{ route('topics.index') }}" class="cta-button text-decoration-none">Explore Topics</a>
                        <a href="{{ route('register') }}" class="cta-button-secondary text-decoration-none">Join the Academy</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@push('styles')
<style>
    /* DESIGN SYSTEM & ABOUT STYLES */
    :root {
        --font-display: 'Outfit', sans-serif;
        --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        --transition-fast: all 0.2s ease;
    }

    .about-page {
        position: relative;
        overflow: hidden;
        background: radial-gradient(circle at 12% 12%, rgba(37, 99, 235, 0.08), transparent 22%),
                    radial-gradient(circle at 86% 18%, rgba(56, 189, 248, 0.08), transparent 24%),
                    linear-gradient(180deg, #f8fbff 0%, #f4f9ff 100%);
    }

    .about-page::before, .about-page::after {
        content: '';
        position: fixed;
        width: 380px;
        height: 380px;
        border-radius: 50%;
        filter: blur(58px);
        opacity: 0.2;
        pointer-events: none;
        z-index: 0;
        animation: ambientDrift 18s ease-in-out infinite alternate;
    }

    .about-page::before { top: 110px; left: -120px; background: rgba(37, 99, 235, 0.2); }
    .about-page::after { right: -120px; bottom: 80px; background: rgba(56, 189, 248, 0.18); animation-delay: -6s; }

    @keyframes ambientDrift {
        0% { transform: translate3d(0, 0, 0); }
        100% { transform: translate3d(18px, -14px, 0); }
    }

    .about-shell { position: relative; z-index: 1; padding-top: 40px; }
    .about-container { max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 24px; }
    .about-section { position: relative; z-index: 1; padding: 40px 0; }

    .section-divider {
        width: 100%;
        height: 1px;
        margin: 26px 0 36px;
        background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.2), rgba(56, 189, 248, 0.2), transparent);
    }

    /* KICKER / EYEBROW */
    .section-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 9px 16px;
        border-radius: 999px;
        border: 1px solid rgba(37, 99, 235, 0.18);
        background: rgba(37, 99, 235, 0.08);
        backdrop-filter: blur(20px);
        color: #2563eb;
    }

    .section-kicker .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        box-shadow: 0 0 18px rgba(37, 99, 235, 0.4);
    }

    /* MISSION LAYOUT */
    .mission-layout { display: grid; grid-template-columns: 0.95fr 1.05fr; gap: 40px; align-items: stretch; }
    .mission-media {
        position: relative;
        min-height: 480px;
        overflow: hidden;
        border-radius: 28px;
        background: linear-gradient(155deg, rgba(248, 251, 255, 0.95), rgba(243, 248, 255, 0.92));
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .media-main {
        position: absolute;
        inset: 38px 34px 120px;
        border-radius: 24px;
        background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80') center/cover;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .media-thumb {
        position: absolute;
        bottom: 22px;
        width: 110px;
        height: 110px;
        border-radius: 22px;
        border: 1px solid rgba(59, 130, 246, 0.12);
        background-size: cover;
        background-position: center;
        box-shadow: 0 18px 40px rgba(37, 99, 235, 0.12);
    }

    .thumb-1 { left: 26px; background-image: url('https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=400'); }
    .thumb-2 { left: 146px; background-image: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=400'); }
    .thumb-3 { left: 266px; background: white; }

    .mission-copy { padding: 42px; border-radius: 28px; }
    .section-heading {
        font-family: var(--font-display);
        font-size: clamp(2.2rem, 4.5vw, 3.2rem);
        line-height: 1.1;
        margin: 16px 0 20px;
        color: #0f172a;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .accent {
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .divider-line { width: 60px; height: 3px; background: #2563eb; border-radius: 10px; margin-bottom: 24px; }
    .cms-dynamic-content { color: #64748b; line-height: 1.75; font-size: 1.05rem; }

    .mission-pills { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .mission-pill {
        padding: 10px 16px;
        border-radius: 999px;
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        transition: var(--transition);
    }
    .mission-pill:hover { border-color: #2563eb; color: #2563eb; transform: translateY(-2px); }

    /* FEATURE GRID */
    .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 36px; }
    .feature-card { padding: 32px; border-radius: 28px; transition: var(--transition); height: 100%; border: 1px solid rgba(0,0,0,0.03); background: white; }
    .feature-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
    .feature-icon { width: 50px; height: 50px; background: rgba(37, 99, 235, 0.05); color: #2563eb; border-radius: 14px; display: grid; place-items: center; font-size: 1.4rem; margin-bottom: 20px; }

    /* EXPLORE GRID */
    .explore-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-top: 36px; }
    .explore-card { padding: 28px; border-radius: 28px; display: flex; gap: 20px; align-items: flex-start; transition: var(--transition); background: white; border: 1px solid rgba(0,0,0,0.03); }
    .explore-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
    .explore-card-icon { width: 50px; height: 50px; min-width: 50px; background: rgba(37, 99, 235, 0.05); color: #2563eb; border-radius: 14px; display: grid; place-items: center; font-size: 1.3rem; }

    /* SPLIT SECTION */
    .split-section { display: grid; grid-template-columns: 0.95fr 1.05fr; gap: 32px; align-items: stretch; }
    .requests-panel { padding: 32px; border-radius: 28px; background: white; }
    .request-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: #f8fafc; border-radius: 18px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.02); }
    .request-count { padding: 6px 12px; background: rgba(37, 99, 235, 0.1); color: #2563eb; border-radius: 10px; font-weight: 800; font-size: 0.9rem; }

    .split-copy { padding: 42px; border-radius: 28px; background: white; }

    /* VISION BANNER */
    .vision-banner { padding: 60px 40px; border-radius: 28px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(56, 189, 248, 0.05)); border: 1px solid rgba(37, 99, 235, 0.1); text-align: center; }

    /* CLOSING MINIMAL */
    .closing-minimal { padding: 48px; border-radius: 30px; background: white; display: flex; justify-content: space-between; align-items: center; gap: 40px; border: 1px solid rgba(0,0,0,0.03); box-shadow: 0 30px 80px rgba(0,0,0,0.04); }
    .cta-button { padding: 14px 32px; border-radius: 12px; background: #2563eb; color: white; font-weight: 700; text-decoration: none; transition: var(--transition); display: inline-block; }
    .cta-button-secondary { padding: 14px 32px; border-radius: 12px; background: white; border: 1px solid rgba(0,0,0,0.1); color: #0f172a; font-weight: 700; transition: var(--transition); display: inline-block; }

    /* GLASS CARD SYSTEM */
    .glass-card { background: rgba(255, 255, 255, 0.6) !important; backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); }

    /* REVEAL SYSTEM */
    .reveal-up, .reveal-left, .reveal-right { opacity: 0; transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); }
    .reveal-up { transform: translateY(40px); }
    .reveal-left { transform: translateX(-40px); }
    .reveal-right { transform: translateX(40px); }
    .visible { opacity: 1; transform: translate(0, 0); }
    .delay-1 { transition-delay: 0.15s; }
    .delay-2 { transition-delay: 0.3s; }
    .delay-3 { transition-delay: 0.45s; }

    /* PARTICLE FIELD */
    .particle-field { position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 0; }
    .particle { position: absolute; top: -12px; border-radius: 50%; background: rgba(37, 99, 235, 0.2); animation: particleDrift linear infinite; }

    @keyframes particleDrift {
        0% { transform: translateY(0) scale(1); opacity: 0; }
        10% { opacity: 1; }
        100% { transform: translateY(100vh) translateX(var(--drift)) scale(0.5); opacity: 0; }
    }

    @media (max-width: 991px) {
        .mission-layout, .split-section, .feature-grid, .explore-grid { grid-template-columns: 1fr; }
        .closing-minimal { flex-direction: column; text-align: center; }
        .mission-media { min-height: 400px; }
    }

    /* CONTENT RESTRICTION */
    .restriction-container { position: relative; overflow: hidden; }
    .blurred-content { filter: blur(8px); pointer-events: none; user-select: none; opacity: 0.6; mask-image: linear-gradient(to bottom, black 0%, transparent 80%); }
    .login-to-unlock { position: absolute; bottom: 0; left: 0; right: 0; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 40px; background: linear-gradient(to bottom, transparent 0%, rgba(248, 251, 255, 0.95) 80%); z-index: 10; text-align: center; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Intersection Observer for Reveal animations
        const observerOptions = { threshold: 0.15, rootMargin: '0px 0px -50px 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right').forEach((el) => observer.observe(el));

        // Particle Field
        const particleField = document.getElementById('particleField');
        if (particleField) {
            const count = window.innerWidth < 768 ? 15 : 30;
            for (let i = 0; i < count; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 3 + 2;
                const duration = Math.random() * 15 + 15;
                const delay = Math.random() * 15;
                const drift = (Math.random() - 0.5) * 200;
                particle.style.cssText = `
                    left: ${Math.random() * 100}%;
                    width: ${size}px;
                    height: ${size}px;
                    animation-duration: ${duration}s;
                    animation-delay: -${delay}s;
                    --drift: ${drift}px;
                `;
                particleField.appendChild(particle);
            }
        }
    });
</script>
@endpush
@endsection
