@extends('layouts.frontend')

@section('title', 'Frequently Asked Questions')

@section('content')

    {{-- ═══ PAGE HERO (uses existing .page-hero from style.css) ═══ --}}
    <div style="padding-top: 72px;">
        <div class="page-hero">
            <div class="page-hero-inner">
                <div class="faq-kicker">
                    <span class="faq-kicker-dot"></span>
                    Help Center
                </div>
                <h1 class="page-hero-title">
                <span class="page-hero-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.5 9a2.7 2.7 0 1 1 4.8 1.7c-.8.7-1.3 1.2-1.3 2.3M12 17h.01"/></svg>
                </span>
                    Frequently Asked Questions
                </h1>
                <p class="page-hero-subtitle">
                    Find quick answers to common questions about {{ get_setting('site_name', 'Physio Academy') }}, curriculum access, and platform features.
                </p>
                <div class="page-hero-breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    <span>FAQ</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ FAQ BODY ═══ --}}
    <main class="faq-page">
        <div class="faq-shell">

            {{-- ── Two-column layout ── --}}
            <div class="faq-container">
                <div class="faq-layout">

                    {{-- Sidebar --}}
                    <aside class="faq-sidebar reveal-left">
                        <div class="faq-help-card glass-card">
                            <div class="fhc-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/><path d="M8 10h8M8 14h5"/></svg>
                            </div>
                            <h3 class="fhc-title">Still have questions?</h3>
                            <p class="fhc-text">Can't find what you're looking for? Our team is ready to help.</p>
                            <a href="{{ route('contact') }}" class="fhc-btn">Get in Touch</a>
                        </div>

                        <div class="faq-stats-card glass-card">
                            <div class="fsc-item">
                                <div class="fsc-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"/></svg>
                                </div>
                                <div>
                                    <div class="fsc-label">{{ $faqs->count() }} Questions</div>
                                    <div class="fsc-sub">Answered &amp; documented</div>
                                </div>
                            </div>
                            <div class="fsc-item">
                                <div class="fsc-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                </div>
                                <div>
                                    <div class="fsc-label">Quick Answers</div>
                                    <div class="fsc-sub">Regularly updated</div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    {{-- Main accordion --}}
                    <div class="faq-main reveal-right">
                        @forelse($faqs as $faq)
                            <div class="faq-item {{ $loop->first ? 'faq-item--open' : '' }} reveal-up" style="transition-delay: {{ $loop->index * 0.05 }}s">
                                <button class="faq-question" type="button" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    <span class="faq-num">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <span class="faq-question-text">{{ $faq->question }}</span>
                                    <span class="faq-chevron">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                                </button>
                                <div class="faq-answer" {{ $loop->first ? '' : 'hidden' }}>
                                    <div class="faq-answer-inner">
                                        {!! nl2br(e($faq->answer)) !!}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="faq-empty glass-card">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.5 9a2.7 2.7 0 1 1 4.8 1.7c-.8.7-1.3 1.2-1.3 2.3M12 17h.01"/></svg>
                                <h4>No FAQs Published Yet</h4>
                                <p>We're currently documenting answers to common questions. Please check back soon.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

            {{-- ── CTA Banner ── --}}
            <div class="faq-container faq-cta reveal-up">
                <div class="faq-cta-inner">
                    <div class="faq-cta-blob faq-cta-blob-1"></div>
                    <div class="faq-cta-blob faq-cta-blob-2"></div>
                    <div class="faq-cta-content">
                        <h2 class="faq-cta-title">Master Your Syllabus Today</h2>
                        <p class="faq-cta-sub">Join thousands of physiotherapy students excelling with structured study plans and examiner-vetted resources.</p>
                        <div class="faq-cta-actions">
                            @guest
                                <a href="{{ route('register') }}" class="faq-cta-btn-primary">Get Started Free</a>
                                <a href="{{ route('login') }}" class="faq-cta-btn-secondary">Login</a>
                            @else
                                <a href="{{ route('topics.index') }}" class="faq-cta-btn-primary">Browse Topics</a>
                                <a href="{{ route('exam-aid') }}" class="faq-cta-btn-secondary">Exam Aid</a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    @push('styles')
        <style>
            .faq-page {
                --faq-brand-blue: #004AAD;
                --faq-brand-white: #FFFFFF;
                --faq-brand-grey: #D9D9D9;
            }

            /* ═══════════════════════════════════════════════
               FAQ PAGE — Self-contained styles
               Follows the same pattern as about.blade.php
               No Bootstrap dependencies
               ═══════════════════════════════════════════════ */

            .faq-page {
                background: linear-gradient(180deg, #FFFFFF 0%, rgba(217, 217, 217, 0.20) 100%);
                position: relative;
                overflow: hidden;
            }

            .faq-shell {
                position: relative;
                z-index: 1;
                padding: 60px 0 80px;
            }

            /* Container — identical to .about-container */
            .faq-container {
                max-width: 1280px;
                margin: 0 auto;
                width: 100%;
                padding: 0 24px;
            }

            /* — Kicker badge (matches .section-kicker style) — */
            .faq-kicker {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                font-size: 0.72rem;
                font-weight: 700;
                color: #0f172a;
                text-transform: uppercase;
                letter-spacing: 0.18em;
                margin-bottom: 18px;
            }
            .faq-kicker-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: linear-gradient(135deg, #004AAD, #004AAD);
                box-shadow: 0 0 12px rgba(0, 74, 173, 0.4);
                flex-shrink: 0;
            }

            /* ── TWO-COLUMN LAYOUT ── */
            .faq-layout {
                display: grid;
                grid-template-columns: 280px 1fr;
                gap: 48px;
                align-items: start;
            }

            /* ── SIDEBAR ── */
            .faq-sidebar {
                position: sticky;
                top: 96px;
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .faq-help-card {
                padding: 32px 28px;
                border-radius: 24px;
                text-align: center;
            }

            .fhc-icon {
                width: 60px;
                height: 60px;
                border-radius: 16px;
                background: linear-gradient(135deg, rgba(0, 74, 173, 0.1), rgba(0, 74, 173, 0.1));
                border: 1px solid rgba(0, 74, 173, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                color: #004AAD;
            }

            .fhc-title {
                font-family: var(--font-display);
                font-size: 1.15rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 10px;
            }

            .fhc-text {
                font-size: 0.9rem;
                color: #64748b;
                line-height: 1.6;
                margin: 0 0 20px;
            }

            .fhc-btn {
                display: block;
                padding: 12px 24px;
                background: linear-gradient(135deg, #004AAD, #004AAD);
                color: #FFFFFF;
                border-radius: 12px;
                font-weight: 700;
                font-size: 0.9rem;
                text-decoration: none;
                transition: all 0.25s ease;
                box-shadow: 0 8px 20px rgba(0, 74, 173, 0.2);
            }
            .fhc-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 28px rgba(0, 74, 173, 0.3);
            }

            .faq-stats-card {
                padding: 24px;
                border-radius: 20px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .fsc-item {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .fsc-icon {
                width: 40px;
                height: 40px;
                min-width: 40px;
                border-radius: 10px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .fsc-label {
                font-weight: 700;
                font-size: 0.95rem;
                color: #0f172a;
            }

            .fsc-sub {
                font-size: 0.8rem;
                color: #94a3b8;
                margin-top: 2px;
            }

            /* ── FAQ ACCORDION ITEMS ── */
            .faq-item {
                border: 1px solid rgba(0, 74, 173, 0.12);
                border-radius: 16px;
                overflow: hidden;
                background: rgba(255,255,255,0.7);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                margin-bottom: 14px;
                transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
                box-shadow: 0 4px 16px rgba(0, 74, 173, 0.04);
            }

            .faq-item:hover {
                border-color: rgba(0, 74, 173, 0.25);
                box-shadow: 0 8px 28px rgba(0, 74, 173, 0.07);
            }

            .faq-item--open {
                border-color: rgba(0, 74, 173, 0.35);
                background: #FFFFFF;
                box-shadow: 0 12px 40px rgba(0, 74, 173, 0.1);
            }

            /* Question button */
            .faq-question {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 22px 24px;
                background: none;
                border: none;
                cursor: pointer;
                text-align: left;
                font-family: var(--font-display);
                font-size: 1rem;
                font-weight: 700;
                color: #0f172a;
                transition: color 0.2s ease;
            }

            .faq-item--open .faq-question {
                color: #004AAD;
            }

            .faq-num {
                min-width: 34px;
                height: 34px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                border-radius: 8px;
                font-size: 0.8rem;
                font-weight: 800;
                flex-shrink: 0;
                transition: background 0.2s, color 0.2s;
            }

            .faq-item--open .faq-num {
                background: #004AAD;
                color: #FFFFFF;
            }

            .faq-question-text {
                flex: 1;
                line-height: 1.5;
            }

            .faq-chevron {
                flex-shrink: 0;
                color: #94a3b8;
                transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), color 0.2s;
                display: flex;
                align-items: center;
            }

            .faq-item--open .faq-chevron {
                transform: rotate(180deg);
                color: #004AAD;
            }

            /* Answer panel */
            .faq-answer {
                overflow: hidden;
                transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.3s ease;
                max-height: 1000px;
                opacity: 1;
            }

            .faq-answer[hidden] {
                display: block !important; /* override browser hidden so we can animate */
                max-height: 0;
                opacity: 0;
            }

            .faq-answer-inner {
                padding: 0 24px 24px 74px;
                font-size: 0.975rem;
                color: #64748b;
                line-height: 1.8;
                font-family: var(--font-body);
            }

            /* Empty state */
            .faq-empty {
                padding: 60px 40px;
                border-radius: 24px;
                text-align: center;
                color: #64748b;
            }
            .faq-empty svg {
                color: #D9D9D9;
                margin-bottom: 20px;
            }
            .faq-empty h4 {
                font-family: var(--font-display);
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 10px;
                font-size: 1.3rem;
            }
            .faq-empty p {
                font-size: 0.95rem;
                max-width: 400px;
                margin: 0 auto;
            }

            /* ── CTA BANNER ── */
            .faq-cta {
                margin-top: 70px;
            }

            .faq-cta-inner {
                background: #004AAD;
                border-radius: 28px;
                padding: 64px 56px;
                position: relative;
                overflow: hidden;
                text-align: center;
                color: #FFFFFF;
            }

            .faq-cta-blob {
                position: absolute;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(0, 74, 173, 0.22), transparent 70%);
                filter: blur(70px);
                pointer-events: none;
                z-index: 0;
            }
            .faq-cta-blob-1 { top: -200px; left: -100px; }
            .faq-cta-blob-2 { bottom: -200px; right: -100px; background: radial-gradient(circle, rgba(0, 74, 173, 0.15), transparent 70%); }

            .faq-cta-content {
                position: relative;
                z-index: 1;
                max-width: 620px;
                margin: 0 auto;
            }

            .faq-cta-title {
                font-family: var(--font-display);
                font-size: clamp(1.8rem, 3.5vw, 2.6rem);
                font-weight: 800;
                margin: 0 0 16px;
                line-height: 1.2;
            }

            .faq-cta-sub {
                font-size: 1.05rem;
                opacity: 0.75;
                line-height: 1.7;
                margin: 0 0 36px;
            }

            .faq-cta-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 16px;
                flex-wrap: wrap;
            }

            .faq-cta-btn-primary {
                padding: 14px 36px;
                background: #FFFFFF;
                color: #004AAD;
                font-weight: 700;
                font-size: 0.95rem;
                border-radius: 12px;
                text-decoration: none;
                transition: all 0.25s ease;
                box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            }
            .faq-cta-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 14px 32px rgba(0,0,0,0.2);
            }

            .faq-cta-btn-secondary {
                padding: 13px 32px;
                border: 1.5px solid rgba(255,255,255,0.3);
                color: #FFFFFF;
                font-weight: 600;
                font-size: 0.95rem;
                border-radius: 12px;
                text-decoration: none;
                transition: all 0.25s ease;
            }
            .faq-cta-btn-secondary:hover {
                background: rgba(255,255,255,0.08);
                border-color: rgba(255,255,255,0.5);
            }


            /* ─── PHYSIO SOURCE FAQ BRAND OVERRIDES ───────────────────────────── */
            .faq-page .fhc-btn,
            .faq-page .faq-item--open .faq-num {
                background: #004AAD;
                color: #FFFFFF;
            }

            .faq-page .fhc-btn:hover {
                background: #003B8A;
            }

            .faq-page .faq-cta-inner {
                background: #004AAD;
            }

            .faq-page .faq-cta-btn-primary {
                background: #FFFFFF;
                color: #004AAD;
            }

            .faq-page .faq-cta-btn-secondary {
                color: #FFFFFF;
                border-color: rgba(255, 255, 255, 0.45);
            }

            .faq-page .faq-cta-btn-secondary:hover {
                background: rgba(255, 255, 255, 0.10);
                border-color: #FFFFFF;
            }

            .faq-page .faq-empty svg {
                color: #D9D9D9;
            }

            /* ── RESPONSIVE ── */
            @media (max-width: 1024px) {
                .faq-layout {
                    grid-template-columns: 240px 1fr;
                    gap: 32px;
                }
            }

            @media (max-width: 768px) {
                .faq-layout {
                    grid-template-columns: 1fr;
                }
                .faq-sidebar {
                    position: static;
                    order: 2;
                }
                .faq-main-wrap {
                    order: 1;
                }
                .faq-cta-inner {
                    padding: 40px 28px;
                }
                .faq-answer-inner {
                    padding: 0 20px 20px 20px;
                }
                .faq-question {
                    padding: 18px 20px;
                    font-size: 0.95rem;
                }
                .faq-layout {
                    gap: 48px;
                }
            }

            @media (max-width: 480px) {
                .faq-shell { padding: 40px 0 60px; }
                .faq-cta-inner { padding: 32px 20px; }
                .faq-cta-actions { flex-direction: column; }
                .faq-cta-btn-primary, .faq-cta-btn-secondary {
                    width: 100%;
                    text-align: center;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                'use strict';

                // Pure JS accordion — no Bootstrap required
                function initFaqAccordion() {
                    var items = document.querySelectorAll('.faq-item');

                    items.forEach(function (item) {
                        var btn    = item.querySelector('.faq-question');
                        var answer = item.querySelector('.faq-answer');

                        btn.addEventListener('click', function () {
                            var isOpen = item.classList.contains('faq-item--open');

                            // Close all
                            items.forEach(function (el) {
                                el.classList.remove('faq-item--open');
                                var a = el.querySelector('.faq-answer');
                                a.setAttribute('hidden', '');
                                el.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                            });

                            // Toggle clicked
                            if (!isOpen) {
                                item.classList.add('faq-item--open');
                                answer.removeAttribute('hidden');
                                btn.setAttribute('aria-expanded', 'true');
                            }
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initFaqAccordion);
                } else {
                    initFaqAccordion();
                }
            })();
        </script>
    @endpush

@endsection
