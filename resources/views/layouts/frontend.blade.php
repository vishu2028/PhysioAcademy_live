<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', get_setting('site_name', 'Physio Academy')) — Your Academic Guide for Physiotherapy</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}" />

  @if(get_setting('site_favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . get_setting('site_favicon')) }}">
  @endif
  @stack('styles')
</head>
@php
  $protectionEnabled = get_setting('enable_content_protection', '0') === '1';
  $isProtectedPage = $protectionEnabled && (isset($pageProtected) ? $pageProtected : false);
@endphp
<body
  data-protected="{{ $isProtectedPage ? 'true' : 'false' }}"
  data-site-name="{{ get_setting('site_name', 'Physio Academy') }}"
  @auth data-user-email="{{ auth()->user()->email }}" data-user-id="{{ auth()->user()->id }}" @endauth
  data-protection-right-click="{{ get_setting('protection_disable_right_click', '0') }}"
  data-protection-devtools="{{ get_setting('protection_disable_devtools', '0') }}"
  data-protection-copy="{{ get_setting('protection_disable_copy', '0') }}"
  data-protection-drag="{{ get_setting('protection_disable_drag', '0') }}"
  data-protection-watermark="{{ get_setting('protection_enable_watermark', '0') }}"
>

<!-- SCROLL PROGRESS -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- BACK TO TOP -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="{{ url('/') }}" class="nav-logo">
      <div class="logo-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
      </div>
      <span class="logo-text">Physio <span class="logo-accent">Academy</span></span>
    </a>

      <div class="nav-links" id="navLinks">
          @php
              $mainMenu = \App\Models\Menu::getByLocation('header_menu');

              /*
               * Guest user restriction
               * Agar user login/register nahi hai to sirf Home aur About allowed hain
               */
              $isGuestUser = auth()->guest();

              /*
               * Signup/Register URL
               */
              $signupUrl = route('register');

              /*
               * Sirf Home aur About guest ke liye allowed hain
               */
              $isAllowedForGuest = function ($url) {
                  $path = trim(parse_url(url($url), PHP_URL_PATH), '/');

                  return in_array($path, ['', 'about'], true);
              };

              /*
               * Agar guest user hai aur URL Home/About nahi hai,
               * to register page par bhej do.
               */
              $getNavUrl = function ($url) use ($isGuestUser, $isAllowedForGuest, $signupUrl) {
                  if ($isGuestUser && ! $isAllowedForGuest($url)) {
                      return $signupUrl;
                  }

                  return url($url);
              };
          @endphp

          @if($mainMenu)
              @foreach($mainMenu->items as $item)
                  @if($item->children->count() > 0)
                      @php
                          $itemActive = false;

                          foreach ($item->children as $child) {
                              $childPath = trim(parse_url(url($child->url), PHP_URL_PATH), '/');
                              $childIsActive = Request::is($childPath) || Request::is($childPath.'/*');

                              if ($childIsActive) {
                                  $itemActive = true;
                              }
                          }
                      @endphp

                      <div class="nav-dropdown {{ $itemActive ? 'active' : '' }}">
                          @if($isGuestUser)
                              <a href="{{ $signupUrl }}" class="nav-link nav-dropdown-toggle">
                                  {{ $item->title }}
                                  <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                      <polyline points="6 9 12 15 18 9"/>
                                  </svg>
                              </a>
                          @else
                              <button class="nav-link nav-dropdown-toggle {{ $itemActive ? 'active' : '' }}">
                                  {{ $item->title }}
                                  <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                      <polyline points="6 9 12 15 18 9"/>
                                  </svg>
                              </button>

                              <div class="nav-dropdown-menu">
                                  @foreach($item->children as $child)
                                      @php
                                          $childPath = trim(parse_url(url($child->url), PHP_URL_PATH), '/');
                                          $childActive = Request::is($childPath) || Request::is($childPath.'/*');
                                      @endphp

                                      <a href="{{ url($child->url) }}" class="nav-dropdown-item {{ $childActive ? 'active' : '' }}">
                                          @if($child->icon)
                                              <span class="ui-icon ui-icon-{{ $child->icon }}"></span>
                                          @endif

                                          {{ $child->title }}
                                      </a>
                                  @endforeach
                              </div>
                          @endif
                      </div>
                  @else
                      @php
                          $itemPath = trim($item->url, '/');
                          $itemActive = Request::is($itemPath);
                      @endphp

                      <a href="{{ $getNavUrl($item->url) }}" class="nav-link {{ $itemActive ? 'active' : '' }}">
                          {{ $item->title }}
                      </a>
                  @endif
              @endforeach
          @else
              <a href="{{ url('/') }}" class="nav-link {{ Request::is('/') ? 'active' : '' }}">
                  Home
              </a>

              <a href="{{ route('about') }}" class="nav-link {{ Request::is('about') ? 'active' : '' }}">
                  About
              </a>

              @php
                  $topicsDropdownActive = Request::routeIs('topics.*') || Request::routeIs('search');
              @endphp

              <div class="nav-dropdown {{ $topicsDropdownActive ? 'active' : '' }}">
                  @if($isGuestUser)
                      <a href="{{ $signupUrl }}" class="nav-link nav-dropdown-toggle">
                          Topics
                          <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                              <polyline points="6 9 12 15 18 9"/>
                          </svg>
                      </a>
                  @else
                      <button class="nav-link nav-dropdown-toggle {{ $topicsDropdownActive ? 'active' : '' }}">
                          Topics
                          <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                              <polyline points="6 9 12 15 18 9"/>
                          </svg>
                      </button>

                      <div class="nav-dropdown-menu">
                          <a href="{{ route('search') }}" class="nav-dropdown-item {{ Request::routeIs('search') ? 'active' : '' }}">
                              <span class="ui-icon ui-icon-search"></span>
                              Search
                          </a>

                          <a href="{{ route('topics.year') }}" class="nav-dropdown-item {{ Request::routeIs('topics.year') ? 'active' : '' }}">
                              <span class="ui-icon ui-icon-calendar"></span>
                              By Year
                          </a>

                          <a href="{{ route('topics.index') }}" class="nav-dropdown-item {{ Request::routeIs('topics.index') ? 'active' : '' }}">
                              <span class="ui-icon ui-icon-book"></span>
                              By Subjects
                          </a>
                      </div>
                  @endif
              </div>

              <a href="{{ $isGuestUser ? $signupUrl : route('exam-aid') }}" class="nav-link {{ Request::is('exam-aid') ? 'active' : '' }}">
                  Exam Aid
              </a>
          @endif

          @if($isGuestUser)
              <a href="{{ $signupUrl }}" class="nav-search-btn" aria-label="Search">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="11" cy="11" r="8"/>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
              </a>
          @else
              <button class="nav-search-btn" id="searchToggle">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="11" cy="11" r="8"/>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
              </button>
          @endif

          <a href="{{ $isGuestUser ? $signupUrl : route('bookmarks') }}" class="nav-bookmark-btn active" aria-label="Bookmarks">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
              </svg>
          </a>
      </div>

    <div class="nav-cta">
      @auth
        @role('super_admin|admin')
            <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Admin</a>
        @endrole
{{--        <form action="{{ route('logout') }}" method="POST" class="d-inline">--}}
{{--            @csrf--}}
{{--            <button type="submit" class="btn-primary-nav" style="border:none; cursor:pointer;">Logout</button>--}}
{{--        </form>--}}
            @auth
                @php
                    $user = auth()->user();

                    $name = trim($user->name ?? '');
                    $emailName = explode('@', $user->email ?? 'user')[0];

                    $parts = preg_split('/\s+/', $name ?: $emailName);

                    if (count($parts) >= 2) {
                        $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
                    } else {
                        $initials = strtoupper(mb_substr($parts[0] ?? 'U', 0, 2));
                    }
                @endphp

                <div class="nav-user-menu">
                    <button type="button" class="nav-user-avatar">
                        {{ $initials }}
                    </button>

                    <div class="nav-user-dropdown">
                        <form action="{{ route('logout') }}" method="POST" class="nav-user-logout-form">
                            @csrf

                            <button type="submit" class="nav-user-logout-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M15 17L20 12L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 19H6C4.89543 19 4 18.1046 4 17V7C4 5.89543 4.89543 5 6 5H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
      @else
        <a href="{{ route('login') }}" class="btn-ghost">Login</a>
        <a href="{{ route('register') }}" class="btn-primary-nav">Sign Up</a>
      @endauth
    </div>

    <button class="hamburger" id="hamburger">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

@yield('content')

@include('layouts.partials.footer')

<!-- SEARCH OVERLAY -->
<div class="search-overlay" id="searchOverlay">
  <div class="search-overlay-bg"></div>
  <div class="search-modal">
    <div class="search-input-wrap">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" class="search-input" id="mainSearch" placeholder="Search topics, subjects, questions..." autocomplete="off"/>
      <button class="search-close" id="searchClose">ESC</button>
    </div>
    {{-- Dynamic suggestions container - populated by JS via AJAX --}}
    <div class="search-suggestions" id="searchSuggestions">
      <div id="searchSuggestionsDynamic">
        {{-- Loading skeleton shown while JS initialises --}}
        <div class="suggestion-group">
          <span class="suggestion-label">Trending Topics</span>
          <div class="suggestion-items" id="defaultSuggestionItems">
            <div class="suggestion-item" style="opacity:.4;pointer-events:none">
              <span class="suggest-icon ui-icon ui-icon-flame" aria-hidden="true"></span>Loading...
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="search-no-result" id="searchNoResult" style="display:none">
      <div class="no-result-animation">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </div>
      <p>No results found for "<span id="noResultQuery"></span>"</p>
      <a href="{{ route('search') }}" id="btnRequestTopic" class="btn-request-topic">
        <span class="ui-icon ui-icon-mail" aria-hidden="true"></span> View all results
      </a>
    </div>
  </div>
</div>
{{-- Pass routes to JS via meta tags --}}
<meta name="search-suggestions-url" content="{{ route('search.suggestions') }}">
<meta name="search-url" content="{{ route('search') }}">
<meta name="topic-base-url" content="{{ url('/topic') }}">

<!-- AUTH MODAL -->
<div class="auth-overlay" id="authOverlay">
  <div class="auth-modal glass-card">
    <button class="auth-close" id="authClose">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="auth-glow"></div>

    <div class="auth-tabs">
      <button class="auth-tab active" data-tab="login">Login</button>
      <button class="auth-tab" data-tab="register">Register</button>
      <div class="auth-tab-slider" id="authTabSlider"></div>
    </div>

    <div class="auth-panel" id="loginPanel">
      <h3 class="auth-title">Welcome Back</h3>
      <p class="auth-subtitle">Continue your learning journey</p>
      <form action="{{ route('login') }}" method="POST" class="auth-form">
        @csrf
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input type="email" name="email" placeholder="Email address" class="auth-input" required/>
        </div>
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" name="password" placeholder="Password" class="auth-input" id="loginPass" required/>
          <button type="button" class="pass-toggle" onclick="togglePass('loginPass')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <button type="submit" class="auth-submit-btn">Login to Physio Academy</button>
        <div class="auth-divider"><span>or continue with</span></div>
        <a href="{{ url('auth/google') }}" class="auth-social-btn" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
          <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          Continue with Google
        </a>
      </form>
    </div>

    <div class="auth-panel hidden" id="registerPanel">
      <h3 class="auth-title">Join Physio Academy</h3>
      <p class="auth-subtitle">Start your structured learning journey</p>
      <form action="{{ route('register') }}" method="POST" class="auth-form">
        @csrf
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input type="text" name="name" placeholder="Full name" class="auth-input" required/>
        </div>
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <input type="email" name="email" placeholder="Email address" class="auth-input" required/>
        </div>
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" name="password" placeholder="Password" class="auth-input" id="regPass" required/>
          <button type="button" class="pass-toggle" onclick="togglePass('regPass')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="auth-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/></svg>
          <select name="year" class="auth-input" style="color:rgba(255,255,255,0.7)" required>
            <option value="">Select Year</option>
            <option>First Year</option>
            <option>Second Year</option>
            <option>Third Year</option>
            <option>Fourth Year</option>
            <option>Internship</option>
          </select>
        </div>
        <button type="submit" class="auth-submit-btn">Create Account</button>
        <div class="auth-divider"><span>or continue with</span></div>
        <a href="{{ url('auth/google') }}" class="auth-social-btn" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
          <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          Sign up with Google
        </a>
      </form>
    </div>
  </div>
</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('ui-physio/script.js') }}"></script>
<script src="{{ asset('ui-physio/protection.js') }}?v={{ time() }}"></script>
@stack('scripts')

</body>
</html>
<style>
    .nav-user-menu {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    /* Avatar */
    .nav-user-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 1px solid rgba(59, 130, 246, 0.25);
        background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
        color: #ffffff;
        font-size: 21px;
        font-weight: 700;
        line-height: 54px;
        text-align: center;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.22);
        transition: all 0.22s ease;
    }

    .nav-user-avatar:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(37, 99, 235, 0.30);
    }

    /* Dropdown Wrapper */
    .nav-user-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 155px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px) scale(0.98);
        transition: all 0.22s ease;
        z-index: 9999;
    }

    .nav-user-menu:hover .nav-user-dropdown,
    .nav-user-menu:focus-within .nav-user-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    /* Small Arrow */
    .nav-user-dropdown::before {
        content: "";
        position: absolute;
        top: -7px;
        right: 24px;
        width: 14px;
        height: 14px;
        background: rgba(255, 255, 255, 0.98);
        transform: rotate(45deg);
        border-left: 1px solid rgba(37, 99, 235, 0.08);
        border-top: 1px solid rgba(37, 99, 235, 0.08);
        z-index: -1;
    }

    /* Logout Box */
    .nav-user-logout-form {
        margin: 0;
        padding: 7px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 18px;
        border: 1px solid rgba(37, 99, 235, 0.10);
        box-shadow: 0 14px 35px rgba(37, 99, 235, 0.14);
        backdrop-filter: blur(10px);
    }

    /* Logout Button */
    .nav-user-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        padding: 11px 14px;
        border: none;
        border-radius: 13px;
        background: transparent;
        color: #2563eb;
        font-size: 15px;
        font-weight: 650;
        line-height: 1;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .nav-user-logout-btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .nav-user-logout-btn:hover {
        background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
    }

    .nav-user-logout-btn:active {
        transform: translateY(0);
    }

    /* Mobile adjustment */
    @media (max-width: 768px) {
        .nav-user-avatar {
            width: 46px;
            height: 46px;
            line-height: 46px;
            font-size: 18px;
        }

        .nav-user-dropdown {
            width: 145px;
            right: -4px;
        }

        .nav-user-logout-btn {
            font-size: 14px;
            padding: 10px 12px;
        }
    }
</style>
