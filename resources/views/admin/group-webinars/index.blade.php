@extends('layouts.admin')

@section('title', 'Group Webinar Requests')

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Webinar Requests', 'value' => $counts['total_requests'], 'icon' => 'bi-collection', 'class' => 'summary-neutral'],
            ['label' => 'Total Participants', 'value' => $counts['total_participants'], 'icon' => 'bi-people', 'class' => 'summary-info'],
            ['label' => 'Pending Payments', 'value' => $counts['pending_payments'], 'icon' => 'bi-credit-card', 'class' => 'summary-warning'],
            ['label' => 'Fully Paid Webinars', 'value' => $counts['fully_paid'], 'icon' => 'bi-cash-coin', 'class' => 'summary-success'],
            ['label' => 'Needs Scheduling', 'value' => $counts['needs_scheduling'], 'icon' => 'bi-calendar-event', 'class' => 'summary-primary'],
            ['label' => 'Confirmed Webinars', 'value' => $counts['confirmed'], 'icon' => 'bi-calendar-check', 'class' => 'summary-info'],
            ['label' => 'Completed Webinars', 'value' => $counts['completed'], 'icon' => 'bi-check-circle', 'class' => 'summary-success'],
        ];
        $revenueCards = [
            ['label' => 'Daily Revenue', 'period' => 'Today', 'value' => $revenues['daily'], 'icon' => 'bi-cash-stack', 'class' => 'revenue-daily'],
            ['label' => 'Weekly Revenue', 'period' => 'Current Week', 'value' => $revenues['weekly'], 'icon' => 'bi-calendar-week', 'class' => 'revenue-weekly'],
            ['label' => 'Monthly Revenue', 'period' => 'Current Month', 'value' => $revenues['monthly'], 'icon' => 'bi-calendar3', 'class' => 'revenue-monthly'],
            ['label' => 'Yearly Revenue', 'period' => 'Current Year', 'value' => $revenues['yearly'], 'icon' => 'bi-graph-up-arrow', 'class' => 'revenue-yearly'],
        ];
        $statusClasses = [
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
        ];
    @endphp

    <div class="group-webinars-admin-page">
        <div class="page-heading-section">
            <div>
                <h2 class="fw-bold mb-1">Group Webinar Requests</h2>
                <p class="text-secondary mb-0">Review group webinar requests, participant payments, schedule sessions and manage booking statuses.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <div class="fw-bold mb-1">Please fix the following errors:</div>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="booking-summary-grid">
            @foreach($summaryCards as $card)
                <div class="booking-summary-card {{ $card['class'] }}">
                    <div class="summary-card-content">
                        <div>
                            <div class="summary-card-label">{{ $card['label'] }}</div>
                            <div class="summary-card-value">{{ number_format((int) $card['value']) }}</div>
                        </div>
                        <div class="summary-card-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="revenue-overview-section">
            <div class="revenue-section-heading">
                <div>
                    <h5 class="fw-bold mb-1">Revenue Overview</h5>
                    <p class="text-secondary mb-0">Revenue from successfully verified individual participant payments.</p>
                </div>
                <div class="revenue-currency-label"><i class="bi bi-currency-rupee"></i> INR</div>
            </div>
            <div class="revenue-summary-grid">
                @foreach($revenueCards as $card)
                    <div class="revenue-summary-card {{ $card['class'] }}">
                        <div class="revenue-card-top">
                            <div><div class="revenue-card-label">{{ $card['label'] }}</div><div class="revenue-card-period">{{ $card['period'] }}</div></div>
                            <div class="revenue-card-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                        </div>
                        <div class="revenue-card-value"><span class="revenue-currency-symbol">₹</span>{{ number_format((float) $card['value'], 2) }}</div>
                        <div class="revenue-card-footer"><i class="bi bi-check-circle-fill"></i> Verified payments only</div>
                    </div>
                @endforeach
            </div>
            <div class="revenue-totals-row">
                <div class="revenue-total-card"><span>Total amount collected</span><strong>₹{{ number_format((float) $revenues['total_collected'], 2) }}</strong></div>
                <div class="revenue-total-card revenue-total-pending"><span>Total amount pending</span><strong>₹{{ number_format((float) $revenues['total_pending'], 2) }}</strong></div>
            </div>
        </section>

        <div class="card border-0 shadow-sm rounded-4 booking-filter-card">
            <div class="card-body p-4">
                <form action="{{ route('admin.group-webinars.index') }}" method="GET">
                    <div class="booking-filter-grid">
                        <div class="filter-field filter-search">
                            <label for="search" class="form-label fw-bold">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                                <input type="text" name="search" id="search" class="form-control" value="{{ $validated['search'] ?? '' }}" placeholder="Reference, organizer, participant email or topic...">
                            </div>
                        </div>
                        <div class="filter-field">
                            <label for="paymentStatus" class="form-label fw-bold">Payment Status</label>
                            <select name="payment_status" id="paymentStatus" class="form-select">
                                <option value="">All Payments</option>
                                <option value="fully_paid" @selected(($validated['payment_status'] ?? '') === 'fully_paid')>Fully Paid</option>
                                <option value="partial" @selected(($validated['payment_status'] ?? '') === 'partial')>Partially Paid</option>
                                <option value="pending" @selected(($validated['payment_status'] ?? '') === 'pending')>Pending</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="webinarStatus" class="form-label fw-bold">Webinar Status</label>
                            <select name="status" id="webinarStatus" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($validated['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="schedulingStatus" class="form-label fw-bold">Scheduling Status</label>
                            <select name="scheduling_status" id="schedulingStatus" class="form-select">
                                <option value="">All Scheduling</option>
                                <option value="scheduled" @selected(($validated['scheduling_status'] ?? '') === 'scheduled')>Scheduled</option>
                                <option value="not_scheduled" @selected(($validated['scheduling_status'] ?? '') === 'not_scheduled')>Not Scheduled</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="dateFrom" class="form-label fw-bold">Submitted From</label>
                            <input type="date" name="date_from" id="dateFrom" class="form-control" value="{{ $validated['date_from'] ?? '' }}">
                        </div>
                        <div class="filter-field">
                            <label for="dateTo" class="form-label fw-bold">Submitted To</label>
                            <input type="date" name="date_to" id="dateTo" class="form-control" value="{{ $validated['date_to'] ?? '' }}">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                            <a href="{{ route('admin.group-webinars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 booking-results-card">
            <div class="card-header bg-white border-0 p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div><h5 class="fw-bold mb-1">Webinar Requests</h5><p class="text-secondary small mb-0">Showing {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} requests</p></div>
                    <span class="results-count-badge"><i class="bi bi-collection me-1"></i>{{ number_format($requests->total()) }} requests</span>
                </div>
            </div>
            <div class="booking-table-wrapper">
                <div class="booking-table-scroll">
                    <table class="table booking-table mb-0">
                        <thead>
                        <tr>
                            <th class="column-reference">Reference</th>
                            <th class="column-organizer">Organizer</th>
                            <th class="column-webinar">Webinar Details</th>
                            <th class="column-participants">Participants</th>
                            <th class="column-payment">Payment</th>
                            <th class="column-status">Webinar Status</th>
                            <th class="column-schedule">Schedule</th>
                            <th class="column-submitted">Submitted</th>
                            <th class="column-action action-column">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($requests as $groupWebinar)
                            @php
                                $participantTotal = (int) $groupWebinar->participants_count;
                                $activeParticipantTotal = (int) $groupWebinar->active_participants_count;
                                $paidCount = (int) $groupWebinar->paid_participants_count;
                                $noPaymentRequiredCount = (int) $groupWebinar->no_payment_required_count;
                                $pendingCount = (int) $groupWebinar->pending_participants_count;
                                $expectedAmount = (float) ($groupWebinar->expected_amount ?? 0);
                                $collectedAmount = (float) ($groupWebinar->collected_amount ?? 0);
                                $pendingAmount = (float) ($groupWebinar->pending_amount ?? 0);
                                $settledCount = $paidCount + $noPaymentRequiredCount;
                                $paymentLabel = $activeParticipantTotal > 0 && $pendingCount === 0 && $settledCount >= $activeParticipantTotal
                                    ? 'Fully Paid'
                                    : ($paidCount > 0 ? 'Partially Paid' : ($pendingCount > 0 ? 'Pending' : 'Not Required'));
                                if ($groupWebinar->status === 'cancelled' && $paidCount === 0) {
                                    $paymentLabel = 'Cancelled';
                                }
                                $paymentClass = $paymentLabel === 'Fully Paid' ? 'payment-paid' : ($paymentLabel === 'Partially Paid' ? 'payment-partial' : ($paymentLabel === 'Pending' ? 'payment-pending' : 'payment-none'));
                            @endphp
                            <tr>
                                <td class="column-reference">
                                    <div class="reference-value">{{ $groupWebinar->reference }}</div>
                                    <div class="reference-id">Request ID #{{ $groupWebinar->id }}</div>
                                </td>
                                <td class="column-organizer">
                                    <div class="student-name">{{ $groupWebinar->organizer_name }}</div>
                                    <a class="student-email" href="mailto:{{ $groupWebinar->organizer_email }}">{{ $groupWebinar->organizer_email }}</a>
                                    <div class="student-phone">Phone not provided</div>
                                </td>
                                <td class="column-webinar">
                                    <div class="topic-value">{{ $groupWebinar->topic }}</div>
                                    <div class="topic-description">{{ \Illuminate\Support\Str::limit($groupWebinar->description, 90) }}</div>
                                    <div class="small text-muted">Submitted {{ $groupWebinar->created_at->format('d M Y') }}</div>
                                </td>
                                <td class="column-participants">
                                    <div class="participants-total">{{ $participantTotal }} participants</div>
                                    <div class="participants-breakdown"><span class="text-success">{{ $paidCount }} paid</span><span class="text-warning-emphasis">{{ $pendingCount }} pending</span></div>
                                    <a class="view-participants-link" href="{{ route('admin.group-webinars.show', $groupWebinar) }}#participants">View participants</a>
                                </td>
                                <td class="column-payment">
                                    <div class="amount-primary">Expected ₹{{ number_format($expectedAmount, 2) }}</div>
                                    <div class="amount-success">Collected ₹{{ number_format($collectedAmount, 2) }}</div>
                                    <div class="amount-pending">Pending ₹{{ number_format($pendingAmount, 2) }}</div>
                                    <span class="payment-status-badge {{ $paymentClass }}">{{ $paymentLabel }}</span>
                                </td>
                                <td class="column-status"><span class="booking-status-badge {{ $statusClasses[$groupWebinar->status] ?? 'status-pending' }}">{{ ucfirst($groupWebinar->status) }}</span></td>
                                <td class="column-schedule">
                                    @if($groupWebinar->scheduled_at)
                                        <div class="schedule-date">{{ $groupWebinar->scheduled_at->format('d M Y') }}</div>
                                        <div class="schedule-time">{{ $groupWebinar->scheduled_at->format('h:i A') }}</div>
                                        <div class="meeting-availability {{ $groupWebinar->meeting_link ? 'meeting-available' : 'meeting-missing' }}"><i class="bi {{ $groupWebinar->meeting_link ? 'bi-link-45deg' : 'bi-link-45deg' }}"></i>{{ $groupWebinar->meeting_link ? 'Meeting link added' : 'Link not added' }}</div>
                                    @else
                                        <span class="not-scheduled"><i class="bi bi-calendar-x me-1"></i>Not Scheduled</span>
                                    @endif
                                </td>
                                <td class="column-submitted">
                                    <div class="submitted-date">{{ $groupWebinar->created_at->format('d M Y') }}</div>
                                    <div class="submitted-time">{{ $groupWebinar->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="column-action action-column">
                                    <a href="{{ route('admin.group-webinars.show', $groupWebinar) }}" class="btn btn-sm btn-primary view-booking-button"><i class="bi bi-eye me-1"></i>View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="empty-state"><div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div><h6 class="fw-bold">No webinar requests found</h6><p class="text-muted mb-0">Try changing or clearing the filters.</p></div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($requests->hasPages())
                <div class="card-footer bg-white border-0 p-4">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .group-webinars-admin-page { width:100%; min-width:0; max-width:100%; overflow-x:hidden; }
            .group-webinars-admin-page *, .group-webinars-admin-page *::before, .group-webinars-admin-page *::after { box-sizing:border-box; }
            .group-webinars-admin-page .card { min-width:0; max-width:100%; }
            .page-heading-section { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:24px; }
            .page-heading-section h2 { color:#0F172A; font-size:1.55rem; }
            .page-heading-section p { font-size:.9rem; }
            .booking-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:24px; }
            .booking-summary-card { min-width:0; padding:20px; border:1px solid rgba(15,23,42,.05); border-radius:20px; background:#fff; box-shadow:0 8px 25px rgba(15,23,42,.06); }
            .summary-card-content { display:flex; align-items:center; justify-content:space-between; gap:12px; }
            .summary-card-label { color:#64748B; font-size:.77rem; font-weight:700; line-height:1.4; }
            .summary-card-value { margin-top:7px; color:#0F172A; font-size:1.65rem; font-weight:850; letter-spacing:-.03em; }
            .summary-card-icon,.revenue-card-icon { display:grid; flex:0 0 44px; width:44px; height:44px; place-items:center; border-radius:14px; background:rgba(0,74,173,.1); color:#004AAD; font-size:1.1rem; }
            .summary-warning .summary-card-icon { background:#fff7e6; color:#d97706; }
            .summary-success .summary-card-icon { background:#ecfdf3; color:#16a34a; }
            .summary-info .summary-card-icon { background:#eff6ff; color:#2563eb; }
            .summary-primary .summary-card-icon { background:#eef2ff; color:#4f46e5; }
            .revenue-overview-section { margin-bottom:24px; }
            .revenue-section-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:16px; }
            .revenue-section-heading h5 { color:#0F172A; }
            .revenue-section-heading p { font-size:.86rem; }
            .revenue-currency-label { display:inline-flex; align-items:center; gap:6px; min-height:35px; padding:0 12px; border-radius:999px; background:rgba(0,74,173,.08); color:#004AAD; font-size:.78rem; font-weight:800; }
            .revenue-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
            .revenue-summary-card { position:relative; min-width:0; overflow:hidden; padding:20px; border:1px solid rgba(15,23,42,.05); border-radius:20px; background:#fff; box-shadow:0 8px 25px rgba(15,23,42,.06); }
            .revenue-summary-card::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:#004AAD; }
            .revenue-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:20px; }
            .revenue-card-label { color:#334155; font-size:.88rem; font-weight:750; }
            .revenue-card-period { color:#94A3B8; font-size:.72rem; font-weight:600; }
            .revenue-card-value { display:flex; align-items:baseline; margin-bottom:12px; color:#0F172A; font-size:clamp(1.35rem,2vw,1.9rem); font-weight:850; line-height:1; letter-spacing:-.03em; overflow-wrap:anywhere; }
            .revenue-currency-symbol { margin-right:3px; font-size:.7em; }
            .revenue-card-footer { display:flex; align-items:center; gap:6px; color:#64748B; font-size:.7rem; font-weight:650; }
            .revenue-card-footer i { color:#16A34A; }
            .revenue-weekly::before { background:#7C3AED; } .revenue-weekly .revenue-card-icon { background:rgba(124,58,237,.1); color:#7C3AED; }
            .revenue-monthly::before { background:#0284C7; } .revenue-monthly .revenue-card-icon { background:rgba(2,132,199,.1); color:#0284C7; }
            .revenue-yearly::before { background:#15803D; } .revenue-yearly .revenue-card-icon { background:rgba(21,128,61,.1); color:#15803D; }
            .revenue-totals-row { display:flex; justify-content:flex-end; gap:12px; margin-top:14px; }
            .revenue-total-card { display:flex; align-items:center; justify-content:space-between; gap:22px; min-width:250px; padding:13px 16px; border:1px solid #dbeafe; border-radius:14px; background:#eff6ff; color:#1e3a8a; font-size:.82rem; }
            .revenue-total-card strong { color:#0f172a; font-size:1rem; white-space:nowrap; }
            .revenue-total-pending { border-color:#fde68a; background:#fffbeb; color:#92400e; }
            .booking-filter-card { margin-bottom:24px; }
            .booking-filter-grid { display:grid; grid-template-columns:minmax(230px,2fr) repeat(4,minmax(130px,1fr)) repeat(2,minmax(145px,1fr)) auto; align-items:end; gap:14px; }
            .filter-field { min-width:0; }
            .filter-field .form-label { margin-bottom:7px; color:#334155; font-size:.78rem; }
            .filter-field .form-control,.filter-field .form-select,.filter-field .input-group-text { min-height:43px; border-color:#E2E8F0; font-size:.83rem; }
            .filter-search .input-group { flex-wrap:nowrap; }
            .filter-search .form-control { min-width:0; }
            .filter-actions { display:flex; gap:8px; }
            .filter-actions .btn { min-height:43px; white-space:nowrap; }
            .booking-results-card { overflow:hidden; }
            .results-count-badge { display:inline-flex; align-items:center; padding:8px 12px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:750; }
            .booking-table-wrapper { width:100%; min-width:0; }
            .booking-table-scroll { width:100%; overflow-x:auto; overflow-y:hidden; -webkit-overflow-scrolling:touch; }
            .booking-table { min-width:1500px; table-layout:fixed; border-collapse:separate; border-spacing:0; }
            .booking-table .column-reference { width:155px; }
            .booking-table .column-organizer { width:190px; }
            .booking-table .column-webinar { width:240px; }
            .booking-table .column-participants { width:160px; }
            .booking-table .column-payment { width:200px; }
            .booking-table .column-status { width:145px; }
            .booking-table .column-schedule { width:175px; }
            .booking-table .column-submitted { width:130px; }
            .booking-table .column-action { width:115px; }
            .booking-table thead th { position:sticky; top:0; z-index:1; padding:14px 15px; border-bottom:1px solid #E2E8F0; background:#F8FAFC; color:#64748B; font-size:.68rem; font-weight:800; letter-spacing:.055em; text-transform:uppercase; white-space:nowrap; }
            .booking-table tbody td { padding:17px 15px; border-bottom:1px solid #F1F5F9; color:#475569; font-size:.8rem; vertical-align:top; overflow-wrap:anywhere; }
            .booking-table tbody tr:last-child td { border-bottom:0; }
            .booking-table tbody tr:hover td { background:#FBFDFF; }
            .reference-value,.student-name,.topic-value { color:#0F172A; font-weight:750; }
            .reference-id,.student-phone,.topic-description,.submitted-time,.schedule-time { margin-top:4px; color:#94A3B8; font-size:.71rem; }
            .student-email { display:block; margin-top:4px; color:#2563EB; font-size:.73rem; text-decoration:none; overflow-wrap:anywhere; }
            .topic-description { margin:5px 0; color:#64748B; line-height:1.45; }
            .participants-total { color:#0F172A; font-weight:750; white-space:nowrap; }
            .participants-breakdown { display:flex; flex-wrap:wrap; gap:8px; margin:5px 0; font-size:.71rem; }
            .view-participants-link { color:#2563EB; font-size:.71rem; font-weight:700; text-decoration:none; }
            .amount-primary,.amount-success,.amount-pending { margin-bottom:4px; color:#334155; font-size:.72rem; font-weight:650; white-space:nowrap; }
            .amount-success { color:#15803D; } .amount-pending { color:#B45309; }
            .payment-status-badge,.booking-status-badge { display:inline-flex; align-items:center; padding:5px 9px; border-radius:999px; font-size:.66rem; font-weight:800; white-space:nowrap; }
            .payment-status-badge { margin-top:4px; }
            .payment-paid,.status-completed { background:#DCFCE7; color:#15803D; }
            .payment-partial,.status-confirmed { background:#DBEAFE; color:#1D4ED8; }
            .payment-pending,.status-pending { background:#FEF3C7; color:#B45309; }
            .payment-none { background:#F1F5F9; color:#64748B; }
            .status-cancelled { background:#FEE2E2; color:#B91C1C; }
            .schedule-date,.submitted-date { color:#334155; font-size:.75rem; font-weight:700; }
            .meeting-availability { display:flex; align-items:center; gap:4px; margin-top:6px; font-size:.68rem; font-weight:650; }
            .meeting-available { color:#15803D; } .meeting-missing { color:#B45309; }
            .not-scheduled { display:inline-block; padding:6px 9px; border-radius:9px; background:#F1F5F9; color:#64748B; font-size:.7rem; font-weight:700; white-space:nowrap; }
            .action-column { position:sticky; right:0; z-index:2; background:#fff; box-shadow:-8px 0 12px -12px rgba(15,23,42,.4); }
            .booking-table thead .action-column { z-index:3; background:#F8FAFC; }
            .view-booking-button { border-radius:9px; font-size:.72rem; font-weight:700; white-space:nowrap; }
            .empty-state { padding:58px 20px; text-align:center; }
            .empty-state-icon { display:grid; width:54px; height:54px; place-items:center; margin:0 auto 13px; border-radius:16px; background:#EFF6FF; color:#2563EB; font-size:1.35rem; }
            @media(max-width:1400px) { .booking-filter-grid { grid-template-columns:repeat(4,minmax(0,1fr)); } .filter-search { grid-column:span 2; } .filter-actions { grid-column:span 2; justify-content:flex-end; } }
            @media(max-width:992px) { .booking-summary-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } .revenue-summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .booking-filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .filter-search,.filter-actions { grid-column:span 2; } }
            @media(max-width:576px) { .booking-summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; } .booking-summary-card,.revenue-summary-card { padding:16px; border-radius:16px; } .summary-card-label { font-size:.7rem; } .summary-card-value { font-size:1.35rem; } .summary-card-icon,.revenue-card-icon { width:38px; height:38px; flex-basis:38px; border-radius:12px; } .revenue-summary-grid { gap:10px; } .revenue-card-value { font-size:1.3rem; } .revenue-totals-row { flex-direction:column; } .revenue-total-card { min-width:0; width:100%; } .booking-filter-grid { grid-template-columns:1fr; } .filter-search,.filter-actions { grid-column:auto; } .filter-actions .btn { flex:1; } .revenue-section-heading { align-items:flex-start; } }
        </style>
    @endpush
@endsection
