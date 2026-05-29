@extends('layouts.physio')

@section('title', 'Profile Settings')

@section('content')
<div class="profile-role-wrapper pb-5">
    
    @if(auth()->user()->hasRole('admin'))
    <!-- ADMIN PROFILE VIEW -->
    <div class="admin-profile-header mb-5">
        <div class="admin-cover-art"></div>
        <div class="admin-meta-row d-flex align-items-end gap-3 gap-md-4 px-4">
            <div class="admin-avatar-main shadow-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="mb-3">
                <h2 class="fw-bold text-dark mb-1">{{ $user->name }}</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark rounded-pill px-3 py-2 fw-bold small"><i class="bi bi-shield-lock-fill me-1"></i> System Administrator</span>
                    <span class="text-muted small">Managing Physio Academy since {{ $user->created_at->format('Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin System Stats -->
    <div class="row g-3 mb-5 px-md-2">
        <div class="col-md-4">
            <div class="admin-stat-card glass-card-db p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold mb-0">{{ $stats['total_users'] ?? 0 }}</h3>
                        <small class="text-muted fw-bold text-uppercase x-small">Total Users</small>
                    </div>
                    <div class="stat-icon-wrap"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card glass-card-db p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold mb-0">{{ $stats['total_topics'] ?? 0 }}</h3>
                        <small class="text-muted fw-bold text-uppercase x-small">Active Topics</small>
                    </div>
                    <div class="stat-icon-wrap"><i class="bi bi-journal-text"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card glass-card-db p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold mb-0">99.9%</h3>
                        <small class="text-muted fw-bold text-uppercase x-small">Uptime</small>
                    </div>
                    <div class="stat-icon-wrap"><i class="bi bi-activity"></i></div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- USER PROFILE VIEW -->
    <div class="user-profile-hero mb-5 px-3">
        <div class="glass-card-db p-4 rounded-5 overflow-hidden position-relative">
            <div class="d-flex align-items-center gap-4 position-relative z-1">
                <div class="user-avatar-hero shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
                    <p class="text-muted small mb-2">{{ $user->email }}</p>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold x-small">
                        Verified Student
                    </span>
                </div>
                <div class="ms-auto d-none d-md-block text-end">
                    <p class="text-muted small mb-0 font-display">Student ID</p>
                    <p class="fw-bold text-primary mb-0">#PA-{{ $user->id + 1000 }}</p>
                </div>
            </div>
            <div class="hero-decorative-orb"></div>
        </div>
    </div>
    @endif

    <div class="row g-4 @if(!auth()->user()->hasRole('admin')) px-3 @endif">
        <!-- Sidebar Management Card -->
        <div class="col-lg-4">
            <div class="glass-card-db p-4 h-100">
                <h5 class="fw-bold mb-4">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <button class="nav-link-item active" onclick="scrollToSection('info-card')">
                        <i class="bi bi-person"></i> Profile Information
                    </button>
                    <button class="nav-link-item" onclick="scrollToSection('security-card')">
                        <i class="bi bi-shield-lock"></i> Security Settings
                    </button>
                    <button class="nav-link-item text-danger" onclick="scrollToSection('danger-card')">
                        <i class="bi bi-trash"></i> Account Deletion
                    </button>
                    <hr class="my-3 opacity-10">
                    <a href="{{ route('home') }}" class="nav-link-item text-primary">
                        <i class="bi bi-house-door"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Forms Column -->
        <div class="col-lg-8">
            <!-- Information Form -->
            <div class="glass-card-db p-4 p-md-5 mb-4" id="info-card">
                <h4 class="fw-bold mb-4">Edit Profile</h4>
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control-custom @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Save Profile</button>
                        @if (session('status') === 'profile-updated')
                            <span class="ms-3 text-success animate-fade x-small fw-bold">Updated Successfully</span>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Password Form -->
            <div class="glass-card-db p-4 p-md-5 mb-4" id="security-card">
                <h4 class="fw-bold mb-4">Change Password</h4>
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control-custom @error('current_password', 'updatePassword') is-invalid @enderror">
                            @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">New Password</label>
                            <input type="password" name="password" class="form-control-custom @error('password', 'updatePassword') is-invalid @enderror">
                            @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control-custom">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <span class="ms-3 text-success animate-fade x-small fw-bold">Password Updated</span>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Deletion Banner -->
            <div class="glass-card-db p-4 border-danger-subtle bg-danger-soft-hover transition-all" id="danger-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-danger">Privacy & Deletion</h5>
                        <p class="text-muted small mb-0">Permanently remove your account and all associated data.</p>
                    </div>
                    <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmDeletionModal">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refined Deletion Modal -->
<div class="modal fade" id="confirmDeletionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 28px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Confirm Permanent Deletion</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="text-muted small">Once your account is deleted, your data will be permanently removed. Please verify your password to proceed.</p>
                    <input type="password" name="password" class="form-control-custom mt-3" placeholder="Verify password" required>
                </div>
                <div class="modal-footer border-0 gap-2 pb-0 pt-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Delete Forever</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ROLE-BASED PROFILE DESIGN SYSTEM */
    .admin-profile-header { position: relative; height: 180px; margin-top: -20px; }
    .admin-cover-art { position: absolute; inset: 0; background: linear-gradient(135deg, #1e293b, #334155); border-radius: 30px; }
    .admin-meta-row { position: absolute; bottom: -30px; left: 0; width: 100%; transition: transform 0.3s; }
    .admin-avatar-main { width: 120px; height: 120px; border-radius: 30px; background: #fff; color: #1e293b; display: grid; place-items: center; font-size: 3.5rem; font-weight: 800; border: 5px solid #fff; }
    .stat-icon-wrap { width: 42px; height: 42px; border-radius: 12px; background: rgba(37,99,235,0.08); color: #2563eb; display: grid; place-items: center; font-size: 1.2rem; }

    .user-avatar-hero { width: 100px; height: 100px; border-radius: 28px; background: linear-gradient(135deg, #2563eb, #38bdf8); color: #fff; display: grid; place-items: center; font-size: 2.8rem; font-weight: 800; border: 4px solid #fff; }
    .hero-decorative-orb { position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(37,99,235,0.05); border-radius: 50%; filter: blur(50px); pointer-events: none; }

    .nav-link-item { width: 100%; padding: 12px 18px; border-radius: 14px; border: none; background: transparent; text-align: left; font-weight: 600; color: #64748b; transition: 0.2s; display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .nav-link-item:hover, .nav-link-item.active { background: rgba(37,99,235,0.08); color: #2563eb; }

    .form-control-custom { width: 100%; padding: 12px 18px; border-radius: 14px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 500; outline: none; transition: 0.2s; }
    .form-control-custom:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.08); }
    
    .bg-danger-soft-hover:hover { background-color: rgba(239, 68, 68, 0.03); }
    .x-small { font-size: 0.7rem; }
    .animate-fade { animation: fadeOut 4s forwards; }
    @keyframes fadeOut { 0%, 80% { opacity: 1; } 100% { opacity: 0; } }

    @media (max-width: 768px) {
        .admin-meta-row { flex-direction: column; align-items: center !important; text-align: center; bottom: -80px; }
        .admin-profile-header { margin-bottom: 100px !important; }
        .admin-avatar-main { width: 100px; height: 100px; font-size: 2.8rem; }
        .user-profile-hero .d-flex { flex-direction: column; align-items: center; text-align: center; }
    }
</style>
@endpush

@push('scripts')
<script>
    function scrollToSection(id) {
        document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
@endpush
@endsection
