<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', get_setting('site_name', 'Physio Academy'))</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    

    
    <style>
        :root {
            --primary-color: {{ get_setting('primary_color', '#2563eb') }};
            --secondary-color: {{ get_setting('secondary_color', '#38bdf8') }};
        }
        
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            background: 
                radial-gradient(circle at 0% 0%, rgba(56,189,248,0.1), transparent 30%),
                radial-gradient(circle at 100% 100%, rgba(37,99,235,0.1), transparent 30%),
                #f8fbff;
        }

        .dashboard-sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(37,99,235,0.1);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .dashboard-main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            position: relative;
        }

        .nav-logo-db {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 50px;
            text-decoration: none;
        }

        .db-menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }

        .db-menu-item {
            margin-bottom: 8px;
        }

        .db-menu-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            border-radius: 14px;
            color: #64748b;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .db-menu-link:hover, .db-menu-link.active {
            background: rgba(37,99,235,0.08);
            color: #2563eb;
            transform: translateX(5px);
        }

        .db-menu-link i {
            font-size: 1.2rem;
        }

        .user-profile-mini {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(37,99,235,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-mini {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .glass-card-db {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(37,99,235,0.1);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 50px rgba(37,99,235,0.05);
            transition: all 0.3s ease;
        }

        .glass-card-db:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 60px rgba(37,99,235,0.1);
            border-color: rgba(37,99,235,0.2);
        }

        /* ─── SECURITY LAYER STYLES ─── */
        .v-protected-page {
            user-select: none !important;
            -webkit-user-select: none !important;
        }

        #pa-watermark {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
            opacity: 0.03;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .watermark-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 100px;
            transform: rotate(-30deg) scale(1.5);
            width: 200%;
        }

        .watermark-inner span {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            white-space: nowrap;
        }

        #pa-security-notice {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1e293b;
            color: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            z-index: 10000;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        #pa-security-notice.show {
            transform: translateX(-50%) translateY(0);
        }

        .secure-blur {
            filter: blur(8px);
            transition: filter 0.3s ease;
        }

        .secure-blur:hover {
            filter: blur(0);
        }

        @media (max-width: 992px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .dashboard-sidebar.active {
                transform: translateX(0);
            }
            .dashboard-main {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
@php
    $protectionEnabled = get_setting('enable_content_protection', '0') === '1';
    $isProtectedPage = $protectionEnabled && (isset($pageProtected) ? $pageProtected : true); // Default to true for premium dashboard
@endphp
<body 
    class="@yield('body-class')"
    data-protected="{{ $isProtectedPage ? 'true' : 'false' }}"
    data-site-name="{{ get_setting('site_name', 'Physio Academy') }}"
    @auth data-user-email="{{ auth()->user()->email }}" data-user-id="{{ auth()->user()->id }}" @endauth
    data-protection-right-click="{{ get_setting('protection_disable_right_click', '0') }}"
    data-protection-devtools="{{ get_setting('protection_disable_devtools', '0') }}"
    data-protection-copy="{{ get_setting('protection_disable_copy', '0') }}"
    data-protection-drag="{{ get_setting('protection_disable_drag', '0') }}"
    data-protection-watermark="{{ get_setting('protection_enable_watermark', '0') }}"
>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <a href="{{ url('/') }}" class="nav-logo-db">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="logo-text">Physio <span class="logo-accent">Academy</span></span>
            </a>

            <ul class="db-menu-list">

                <li class="db-menu-item">
                    <a href="#" class="db-menu-link {{ Request::is('user/bookmarks*') ? 'active' : '' }}">
                        <i class="bi bi-bookmark-star-fill"></i>
                        <span>Bookmarks</span>
                    </a>
                </li>
                <li class="db-menu-item">
                    <a href="#" class="db-menu-link {{ Request::is('user/courses*') ? 'active' : '' }}">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        <span>Topics</span>
                    </a>
                </li>
                <li class="db-menu-item">
                    <a href="{{ route('profile.edit') }}" class="db-menu-link {{ Request::is('profile*') ? 'active' : '' }}">
                        <i class="bi bi-person-bounding-box"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="db-menu-item">
                    <a href="{{ route('home') }}" class="db-menu-link text-primary">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Back to Home</span>
                    </a>
                </li>
                <li class="db-menu-item" style="margin-top: 20px;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="db-menu-link w-100 text-start border-0 bg-transparent" style="cursor: pointer;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>

            <div class="user-profile-mini">
                <div class="user-avatar-mini">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="user-info-mini">
                    <p class="mb-0 fw-bold small text-dark">{{ auth()->user()->name }}</p>
                    <p class="mb-0 x-small text-muted" style="font-size: 0.7rem;">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Top Header -->
            <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
                <div class="search-bar-db">
                    <h2 class="fw-bold mb-0">Welcome back, <span class="text-gradient">{{ explode(' ', auth()->user()->name)[0] }}!</span></h2>
                    <p class="text-muted small mb-0">Track your learning progress and resume your studies.</p>
                </div>
                <div class="header-actions-db d-flex align-items-center gap-3">
                    <button class="bm-icon-btn shadow-sm" style="width: 44px; height: 44px; border-radius: 12px;">
                        <i class="bi bi-search"></i>
                    </button>
                    <button class="bm-icon-btn shadow-sm" style="width: 44px; height: 44px; border-radius: 12px;">
                        <i class="bi bi-bell"></i>
                    </button>
                </div>
            </div>

            @yield('content')
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('ui-physio/script.js') }}"></script>
    <script src="{{ asset('ui-physio/protection.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html>
