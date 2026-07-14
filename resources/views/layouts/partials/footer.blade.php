<footer class="global-footer reveal-up" id="globalFooter">
    <div class="footer-ambient" aria-hidden="true">
        <span></span><span></span><span></span>
    </div>
    <div class="footer-grid-overlay" aria-hidden="true"></div>
    <div class="footer-shell">
        <div class="footer-brand-card">
            <div class="footer-logo-wrap">
                <div class="footer-logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3L4 7.2v9.2L12 21l8-4.6V7.2L12 3Z" stroke="currentColor" stroke-width="1.7"/><path d="M8.2 12h7.6M12 8.2v7.6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                </div>
                <span>{{ get_setting('footer_tagline', 'Student Driven Platform') }}</span>
            </div>
            <h2>{{ get_setting('site_name', 'Physio Academy') }}</h2>
            <p>{{ get_setting('footer_text', 'A modern academic ecosystem designed for physiotherapy students to learn smarter, navigate syllabus easily, and access structured educational support.') }}</p>
            <div class="footer-status-row">
                <span></span>
                {{ get_setting('footer_status', 'Academic resources syncing across the learning academy') }}
            </div>
        </div>

        @php
            $footerMenu = \App\Models\Menu::getByLocation('footer_quick_links');
        @endphp
        <nav class="footer-column footer-links-group" aria-label="Quick links">
            <h3>Quick Links</h3>
            @if($footerMenu)
                @foreach($footerMenu->items as $item)
                    <a href="{{ $item->url }}" target="{{ $item->target }}">{{ $item->title }}</a>
                @endforeach
            @else
                <a href="/">Home</a>
                <a href="/about">About</a>
                <a href="/topics">Topics</a>
                <a href="/exam-aid">Exam Aid</a>
                <a href="/search">Search</a>
            @endif
        </nav>

        <div class="footer-column footer-support">
            <h3>Support</h3>
            @php
                $supportMenu = \App\Models\Menu::getByLocation('footer_support');
            @endphp
            @if($supportMenu)
                @foreach($supportMenu->items as $item)
                    <a href="{{ $item->url }}" target="{{ $item->target }}">{{ $item->title }}</a>
                @endforeach
            @else
                <a href="{{ route('faq') }}">FAQ</a>
                <a href="#">Feedback</a>
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
            @endif
        </div>

        <div class="footer-column footer-contact">
            <h3>Contact</h3>
            <a href="{{ get_setting('contact_url', 'https://www.physioacademy.com') }}">
                <span>URL</span>
                {{ get_setting('contact_url_display', 'www.physioacademy.com') }}
            </a>
            <a href="mailto:{{ get_setting('contact_email', 'support@physioacademy.com') }}">
                <span>Email</span>
                {{ get_setting('contact_email', 'support@physioacademy.com') }}
            </a>
            @php
                $phone = get_setting('site_phone');
                $phoneLink = $phone ? preg_replace('/[^0-9+]/', '', $phone) : '#';
            @endphp

            @if($phone)
                <a href="tel:{{ $phoneLink }}">
                    <span>Phone</span>
                    {{ $phone }}
                </a>
            @endif
            @php
                $socialLinks = [
                    'instagram_url' => [
                        'label' => 'Instagram',
                        'icon' => 'bi bi-instagram',
                    ],
                    'linkedin_url' => [
                        'label' => 'LinkedIn',
                        'icon' => 'bi bi-linkedin',
                    ],
                    'youtube_url' => [
                        'label' => 'YouTube',
                        'icon' => 'bi bi-youtube',
                    ],
                    'facebook_url' => [
                        'label' => 'Facebook',
                        'icon' => 'bi bi-facebook',
                    ],
                ];
            @endphp

            <div class="footer-socials" aria-label="Social links">
                @foreach($socialLinks as $key => $social)
                    @php
                        $url = trim((string) get_setting($key, ''));
                    @endphp

                    @if($url !== '' && $url !== '#')
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}">
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    @endif
                @endforeach
            </div>
            {{--      <div class="footer-socials" aria-label="Social links">--}}
            {{--        <a href="{{ get_setting('social_instagram', '#') }}" aria-label="Instagram"><i class="bi bi-instagram"></i></a>--}}
            {{--        <a href="{{ get_setting('social_linkedin', '#') }}" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>--}}
            {{--        <a href="{{ get_setting('social_youtube', '#') }}" aria-label="YouTube"><i class="bi bi-youtube"></i></a>--}}
            {{--        <a href="{{ get_setting('social_facebook', '#') }}" aria-label="Facebook"><i class="bi bi-facebook"></i></a>--}}
            {{--      </div>--}}
        </div>

        <div class="footer-newsletter">
            <h3>Subscribe Now</h3>
            <p>{{ get_setting('newsletter_text', 'Get updates about important topics, exam resources, and new learning materials.') }}</p>
            <form class="footer-subscribe-form" id="footerSubscribeForm">
                <label for="footerEmail" class="sr-only">Email address</label>
                <input id="footerEmail" type="email" placeholder="Enter your email" required>
                <button type="submit"><span>Subscribe</span></button>
            </form>
            <small>{{ get_setting('newsletter_small_text', 'Join our YouTube & academic community.') }}</small>
            <div class="footer-subscribe-message" id="footerSubscribeMessage" role="status" aria-live="polite"></div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>{!! get_setting('copyright_text', '© 2026 Physio Academy || All rights reserved') !!}</span>
        <span>{{ get_setting('footer_bottom_tag', 'Built for focused physiotherapy learning.') }}</span>
        <span>Developed By <a href="{{ e(config('app.developer.url', '#')) }}" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline;text-underline-offset:3px;font-weight:600;">{{ e(config('app.developer.name', 'Developer')) }}</a></span>
    </div>
</footer>
