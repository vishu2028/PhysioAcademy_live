<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ get_setting('site_name', config('app.name', 'Physio Academy')) }}</title>

    @if(get_setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . get_setting('site_favicon')) }}">
    @endif

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* ─── ADMIN PANEL RESET ────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            margin: 0;
            overflow-x: hidden;
        }

        /* ─── LAYOUT WRAPPER ────────────────────────────────── */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ─── SIDEBAR ─────────────────────────────────────── */
        #sidebar-wrapper {
            width: 280px;
            min-width: 280px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            transition: margin-left 0.3s ease, transform 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar-wrapper::-webkit-scrollbar { width: 4px; }
        #sidebar-wrapper::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }

        .sidebar-brand {
            padding: 1.5rem;
            font-weight: 800;
            color: #1a202c;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .sidebar-section-label {
            padding: 0.5rem 1.5rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #a0aec0;
            margin-top: 0.75rem;
        }

        .nav-link-admin {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #718096;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 12px;
            margin: 0.15rem 0.75rem;
            white-space: nowrap;
        }

        .nav-link-admin i {
            margin-right: 0.85rem;
            font-size: 1.15rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-link-admin:hover {
            background-color: rgba(79,70,229,0.08);
            color: #4f46e5;
        }

        .nav-link-admin.active {
            background-color: #4f46e5;
            color: #fff;
            box-shadow: 0 4px 12px rgba(79,70,229,0.3);
        }

        /* ─── PAGE CONTENT ────────────────────────────────── */
        #page-content-wrapper {
            width: 100%;
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* ─── ADMIN HEADER ────────────────────────────────── */
        .admin-header {
            background: #fff;
            padding: 0.85rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .admin-header .search-form .input-group {
            border-radius: 12px;
            overflow: hidden;
        }

        .admin-header .search-form .form-control {
            min-width: 260px;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .admin-header .search-form .input-group-text {
            border-radius: 12px 0 0 12px;
        }

        /* ─── CARD SYSTEM ────────────────────────────────── */
        .card-stats {
            border: none;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .card-stats:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }

        /* ─── FORM ELEMENTS ──────────────────────────────── */
        .form-control:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79,70,229,0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79,70,229,0.3);
        }

        /* ─── TABLES ─────────────────────────────────────── */
        .table thead th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            border-bottom: 1px solid #f1f5f9;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            color: #475569;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.9rem;
        }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* ─── BADGES ─────────────────────────────────────── */
        .bg-success-subtle { background: rgba(16,185,129,0.1) !important; }
        .bg-danger-subtle { background: rgba(239,68,68,0.1) !important; }

        /* ─── SIDEBAR OVERLAY (mobile) ───────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1035;
            backdrop-filter: blur(4px);
        }

        .sidebar-overlay.active { display: block; }

        /* ─── RESPONSIVE ─────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
            }

            #sidebar-wrapper.toggled {
                transform: translateX(0);
            }

            #page-content-wrapper {
                margin-left: 0;
            }

            .admin-header {
                padding: 0.85rem 1rem;
            }

            .admin-header .search-form .form-control {
                min-width: 160px;
            }

            .container-fluid {
                padding: 1rem !important;
            }
        }

        @media (max-width: 575.98px) {
            .admin-header .search-form {
                display: none !important;
            }
        }

        /* ─── DATATABLES OVERRIDE ────────────────────────── */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.3rem 0.6rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: none !important;
            padding: 0.3rem 0.7rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: #fff !important;
            border: none !important;
        }

        /* ─── SELECT2 OVERRIDE ───────────────────────────── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 10px;
            border-color: #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-brand">
                <span class="fs-4">{{ get_setting('site_name', 'Physio Academy') }}</span>
            </div>
            <div class="list-group list-group-flush pt-2">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-admin @if(request()->routeIs('admin.dashboard')) active @endif">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>

                <div class="sidebar-section-label">Homepage Management</div>
                <a href="{{ route('admin.hero.index') }}" class="nav-link-admin @if(request()->routeIs('admin.hero.*')) active @endif">
                    <i class="bi bi-layout-text-window-reverse"></i> Hero Section
                </a>
                <a href="{{ route('admin.features.index') }}" class="nav-link-admin @if(request()->routeIs('admin.features.*')) active @endif">
                    <i class="bi bi-stars"></i> Platform Features
                </a>
                <a href="{{ route('admin.faqs.index') }}" class="nav-link-admin @if(request()->routeIs('admin.faqs.*')) active @endif">
                    <i class="bi bi-question-circle"></i> FAQs
                </a>
                <a href="{{ route('admin.testimonials.index') }}" class="nav-link-admin @if(request()->routeIs('admin.testimonials.*')) active @endif">
                    <i class="bi bi-chat-quote"></i> Testimonials
                </a>

                <div class="sidebar-section-label">Academic Management</div>
                <a href="{{ route('admin.subjects.index') }}" class="nav-link-admin @if(request()->routeIs('admin.subjects.*')) active @endif">
                    <i class="bi bi-book"></i> Subjects
                </a>
                <a href="{{ route('admin.academic-years.index') }}" class="nav-link-admin @if(request()->routeIs('admin.academic-years.*')) active @endif">
                    <i class="bi bi-calendar-check"></i> Academic Years
                </a>
                <a href="{{ route('admin.units.index') }}" class="nav-link-admin @if(request()->routeIs('admin.units.*')) active @endif">
                    <i class="bi bi-diagram-3"></i> Units
                </a>
                <a href="{{ route('admin.unit-topics.index') }}" class="nav-link-admin @if(request()->routeIs('admin.unit-topics.*')) active @endif">
                    <i class="bi bi-list-task"></i> Topics
                </a>
                <a href="{{ route('admin.topics.index') }}" class="nav-link-admin @if(request()->routeIs('admin.topics.*')) active @endif">
                    <i class="bi bi-mortarboard"></i> Topics & LMS
                </a>
                <a href="{{ route('admin.exam-aids.index') }}" class="nav-link-admin @if(request()->routeIs('admin.exam-aids.*')) active @endif">
                    <i class="bi bi-journal-text"></i> Exam Aid
                </a>

                <a href="{{ route('admin.parent-topics.index') }}"
                   class="nav-link-admin @if(request()->routeIs('admin.parent-topics.*')) active @endif">
                    <i class="bi bi-diagram-3"></i> Parent Topics
                </a>

                <div class="sidebar-section-label">Content Management</div>
                <a href="{{ route('admin.pages.index') }}" class="nav-link-admin @if(request()->routeIs('admin.pages.*')) active @endif">
                    <i class="bi bi-file-earmark-richtext"></i> CMS Pages
                </a>
                <a href="{{ route('admin.media.index') }}" class="nav-link-admin @if(request()->routeIs('admin.media.*')) active @endif">
                    <i class="bi bi-images"></i> Media Library
                </a>
                <a href="{{ route('admin.menus.index') }}" class="nav-link-admin @if(request()->routeIs('admin.menus.*')) active @endif">
                    <i class="bi bi-list-ul"></i> Navigation Menus
                </a>

                <div class="sidebar-section-label">UI Components</div>
                <a href="{{ route('admin.sliders.index') }}" class="nav-link-admin @if(request()->routeIs('admin.sliders.*')) active @endif">
                    <i class="bi bi-collection-play"></i> Sliders
                </a>
                <a href="{{ route('admin.banners.index') }}" class="nav-link-admin @if(request()->routeIs('admin.banners.*')) active @endif">
                    <i class="bi bi-megaphone"></i> Activity Banners
                </a>

                <div class="sidebar-section-label">System & Users</div>
                @php
                    $unreadMessages = \App\Models\Message::unread()->count();
                @endphp
                <a href="{{ route('admin.messages.index') }}" class="nav-link-admin @if(request()->routeIs('admin.messages.*')) active @endif">
                    <i class="bi bi-envelope"></i> Inquiries @if($unreadMessages > 0) <span class="badge bg-danger ms-auto rounded-pill">{{ $unreadMessages }}</span> @endif
                </a>
                @php
                    $pendingDoubts = \App\Models\Doubt::where('status', 'pending')->count();
                @endphp

                <a href="{{ route('admin.doubts.index') }}" class="nav-link-admin @if(request()->routeIs('admin.doubts.*')) active @endif">
                    <i class="bi bi-question-circle"></i> Student Doubts

                    @if($pendingDoubts > 0)
                        <span class="badge bg-danger ms-auto rounded-pill">{{ $pendingDoubts }}</span>
                    @endif
                </a>
                @php
                    $pendingDoubtSessions =
                        \App\Models\DoubtSessionBooking::where(
                            'booking_status',
                            \App\Models\DoubtSessionBooking::STATUS_PENDING_SCHEDULE
                        )->count();
                @endphp

                <a
                    href="{{ route('admin.doubt-session-bookings.index') }}"
                    class="nav-link-admin @if(
        request()->routeIs(
            'admin.doubt-session-bookings.*'
        )
    ) active @endif"
                >
                    <i class="bi bi-camera-video"></i>
                    Doubt Sessions

                    @if($pendingDoubtSessions > 0)
                        <span class="badge bg-danger ms-auto rounded-pill">
            {{ $pendingDoubtSessions }}
        </span>
                    @endif
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-link-admin @if(request()->routeIs('admin.users.*')) active @endif">
                    <i class="bi bi-people"></i> User Management
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link-admin @if(request()->routeIs('admin.settings.*')) active @endif">
                    <i class="bi bi-gear-fill"></i> Site Settings
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="nav-link-admin @if(request()->routeIs('admin.activity-logs.*')) active @endif">
                    <i class="bi bi-journal-text"></i> Activity Logs
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <header class="admin-header">
                <button class="btn btn-light d-lg-none rounded-3" id="menu-toggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="search-form d-none d-md-flex">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-0 shadow-none" placeholder="Search...">
                    </div>
                </div>
                <div class="header-right d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle shadow-sm position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                3+
                            </span>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center gap-2 border shadow-sm rounded-3" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff" class="rounded-2" width="30" alt="Avatar">
                            <span class="fw-bold small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2" style="border-radius: 12px; min-width: 200px;">
                            <li><a class="dropdown-item rounded-2 py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item rounded-2 py-2" href="/"><i class="bi bi-globe me-2"></i>Main Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item rounded-2 text-danger py-2" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // DataTables
            if($('.datatable').length > 0) {
                $('.datatable').DataTable({
                    "pageLength": 10,
                    "language": {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Search records..."
                    }
                });
            }

            // Select2
            if($('.select2').length > 0) {
                $('.select2').select2({ theme: 'bootstrap-5' });
            }

            // Toastr
            toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

            // Sidebar Toggle (responsive)
            $("#menu-toggle").click(function(e) {
                e.preventDefault();
                $("#sidebar-wrapper").toggleClass("toggled");
                $("#sidebarOverlay").toggleClass("active");
            });

            // Click overlay to close sidebar
            $("#sidebarOverlay").click(function() {
                $("#sidebar-wrapper").removeClass("toggled");
                $(this).removeClass("active");
            });

            // Close sidebar on resize to desktop
            $(window).on('resize', function() {
                if ($(window).width() >= 992) {
                    $("#sidebar-wrapper").removeClass("toggled");
                    $("#sidebarOverlay").removeClass("active");
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
