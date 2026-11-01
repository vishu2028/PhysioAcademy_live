@extends('layouts.admin')

@push('styles')
<style>
    /* ─── Premium Dashboard Card System ─── */
    .dash-stat-card {
        position: relative;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.04);
        border-radius: 20px;
        padding: 28px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.03);
    }

    .dash-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 20px 20px 0 0;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .dash-stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
        border-color: transparent;
    }

    .dash-stat-card:hover::before {
        opacity: 1;
    }

    /* Card accent colors */
    .dash-stat-card.card-indigo::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
    .dash-stat-card.card-cyan::before { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
    .dash-stat-card.card-amber::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .dash-stat-card.card-emerald::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .dash-stat-card.card-blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .dash-stat-card.card-rose::before { background: linear-gradient(90deg, #f43f5e, #fb7185); }

    /* Icon containers with gradient backgrounds */
    .dash-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.4s ease;
        flex-shrink: 0;
    }

    .dash-stat-card:hover .dash-icon-wrap {
        transform: scale(1.1) rotate(5deg);
    }

    .dash-icon-wrap.icon-indigo {
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(129,140,248,0.08));
        color: #6366f1;
    }
    .dash-icon-wrap.icon-cyan {
        background: linear-gradient(135deg, rgba(6,182,212,0.12), rgba(34,211,238,0.08));
        color: #06b6d4;
    }
    .dash-icon-wrap.icon-amber {
        background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(251,191,36,0.08));
        color: #f59e0b;
    }
    .dash-icon-wrap.icon-emerald {
        background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(52,211,153,0.08));
        color: #10b981;
    }
    .dash-icon-wrap.icon-blue {
        background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(96,165,250,0.08));
        color: #3b82f6;
    }
    .dash-icon-wrap.icon-rose {
        background: linear-gradient(135deg, rgba(244,63,94,0.12), rgba(251,113,133,0.08));
        color: #f43f5e;
    }

    .dash-stat-link {
        display: block;
        height: 100%;
        color: inherit;
        text-decoration: none;
        border-radius: 20px;
    }

    .dash-stat-link:focus-visible {
        outline: 3px solid #6366f1;
        outline-offset: 3px;
    }

    .dash-stat-card {
        min-height: 154px;
    }

    .dash-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .dash-stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 0;
    }

    .dash-stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        margin-top: 12px;
    }

    .dash-stat-trend.trend-up {
        background: rgba(16,185,129,0.1);
        color: #059669;
    }

    .dash-stat-trend.trend-neutral {
        background: rgba(100,116,139,0.1);
        color: #64748b;
    }

    /* ─── Section Headers ─── */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-header h5 {
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        font-size: 1.05rem;
    }

    /* ─── Premium Chart & Table Cards ─── */
    .dash-panel {
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.04);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }

    .dash-panel:hover {
        box-shadow: 0 12px 40px rgba(0,0,0,0.07);
    }

    .dash-panel .card-body {
        padding: 24px;
    }

    /* ─── Activity Table ─── */
    .dash-panel .table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        border-bottom: 1px solid #f1f5f9;
        padding: 14px 20px;
    }

    .dash-panel .table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        color: #475569;
        border-bottom: 1px solid #f8fafc;
    }

    /* ─── Animated counter ─── */
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dash-stat-value {
        animation: countUp 0.6s ease-out forwards;
    }

    /* ─── Responsive Tweak ─── */
    @media (max-width: 768px) {
        .dash-stat-card { padding: 20px; }
        .dash-stat-value { font-size: 1.6rem; }
        .dash-icon-wrap { width: 48px; height: 48px; font-size: 1.25rem; }
    }
</style>
@endpush

@section('content')
{{-- ─── Statistics Cards Row ─── --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.users.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-indigo">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Total Users</p>
                        <h3 class="dash-stat-value">{{ $stats['users'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-indigo">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.subjects.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-cyan">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Subjects</p>
                        <h3 class="dash-stat-value">{{ $stats['subjects'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-cyan">
                        <i class="bi bi-book-half"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.academic-years.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-amber">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Academic Years</p>
                        <h3 class="dash-stat-value">{{ $stats['academic_years'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-amber">
                        <i class="bi bi-calendar3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.exam-aids.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-emerald">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Exam Aid</p>
                        <h3 class="dash-stat-value">{{ $stats['exam_aids'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-emerald">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.doubt-session-bookings.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Doubt Sessions</p>
                        <h3 class="dash-stat-value">{{ $stats['doubt_sessions'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-blue">
                        <i class="bi bi-camera-video"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-2">
        <a href="{{ route('admin.group-webinars.index') }}" class="dash-stat-link">
            <div class="dash-stat-card card-rose">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="dash-stat-label">Webinars</p>
                        <h3 class="dash-stat-value">{{ $stats['group_webinars'] }}</h3>
                    </div>
                    <div class="dash-icon-wrap icon-rose">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ─── Charts Row ─── --}}
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="dash-panel h-100">
            <div class="section-header">
                <h5><i class="bi bi-graph-up-arrow me-2 text-primary"></i>User Growth Analytics</h5>
                <span class="badge bg-light text-muted fw-bold px-3 py-2 rounded-pill">Last 6 months</span>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-panel h-100">
            <div class="section-header">
                <h5><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Content Distribution</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="contentDistChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ─── Activity Row ─── --}}
<div class="row g-4">
    <div class="col-12">
        <div class="dash-panel">
            <div class="section-header">
                <h5><i class="bi bi-clock-history me-2 text-primary"></i>Recent Activity</h5>
                <a href="#" class="text-decoration-none text-primary fw-bold small">View All →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead>
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $activity)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.8rem;">
                                            {{ substr($activity['user'], 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold mb-0">{{ $activity['user'] }}</span>
                                            <small class="text-muted">{{ $activity['subtitle'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $activity['action'] }}</td>
                                <td>{{ $activity['timestamp'] }}</td>
                                <td>
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> {{ $activity['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
                                        <span class="text-muted fw-medium">No recent activity found.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');

    // Create gradient fill
    const gradient = userGrowthCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($chartData['users']) !!},
                borderColor: '#6366f1',
                backgroundColor: gradient,
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#6366f1',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 }, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 }, color: '#94a3b8' }
                }
            }
        }
    });

    const contentDistCtx = document.getElementById('contentDistChart').getContext('2d');
    new Chart(contentDistCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($chartData['distribution'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($chartData['distribution'])) !!},
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = Number(context.parsed) || 0;
                            const total = context.dataset.data.reduce((sum, count) => sum + (Number(count) || 0), 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) : '0.0';

                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
