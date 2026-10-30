@extends('layouts.admin')

@section('title', 'Group Webinar Request')

@section('content')
    @php
        $statusClasses = [
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
        ];
    @endphp

    <div class="group-webinar-detail-page">
        <a href="{{ route('admin.group-webinars.index') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to Group Webinar Requests</a>

        <div class="detail-heading">
            <div>
                <h1>{{ $groupWebinar->reference }}</h1>
                <p>Request ID #{{ $groupWebinar->id }} · Submitted {{ $groupWebinar->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <span class="booking-status-badge {{ $statusClasses[$groupWebinar->status] ?? 'status-pending' }}">{{ ucfirst($groupWebinar->status) }}</span>
        </div>

        @if(session('success'))<div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <div class="fw-bold mb-1">Please fix the following errors:</div>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="detail-summary-grid">
            <div class="detail-summary-card"><span>Total participants</span><strong>{{ $groupWebinar->participants->count() }}</strong></div>
            <div class="detail-summary-card"><span>Total expected amount</span><strong>₹{{ number_format($expected, 2) }}</strong></div>
            <div class="detail-summary-card summary-collected"><span>Successfully collected</span><strong>₹{{ number_format($collected, 2) }}</strong></div>
            <div class="detail-summary-card summary-pending"><span>Amount pending</span><strong>₹{{ number_format($pending, 2) }}</strong></div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <section class="card border-0 shadow-sm rounded-4 detail-card mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0"><h2>Organizer Details</h2></div>
                    <div class="card-body p-4 pt-3">
                        <div class="detail-info-grid">
                            <div><span>Name</span><strong>{{ $groupWebinar->organizer_name }}</strong></div>
                            <div><span>Email</span><a href="mailto:{{ $groupWebinar->organizer_email }}">{{ $groupWebinar->organizer_email }}</a></div>
                            <div><span>Phone</span><strong class="text-secondary">Not provided</strong></div>
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm rounded-4 detail-card mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0"><h2>Webinar Details</h2></div>
                    <div class="card-body p-4 pt-3">
                        <div class="detail-info-grid">
                            <div><span>Reference ID</span><strong>{{ $groupWebinar->reference }}</strong></div>
                            <div><span>Request date</span><strong>{{ $groupWebinar->created_at->format('d M Y, h:i A') }}</strong></div>
                            <div><span>Topic</span><strong>{{ $groupWebinar->topic }}</strong></div>
                            <div><span>Webinar status</span><strong><span class="booking-status-badge {{ $statusClasses[$groupWebinar->status] ?? 'status-pending' }}">{{ ucfirst($groupWebinar->status) }}</span></strong></div>
                            <div><span>Price per person</span><strong>{{ $groupWebinar->is_free ? 'Free' : '₹'.number_format((float) $groupWebinar->price_per_person, 2) }}</strong></div>
                            <div><span>Duration</span><strong>{{ $groupWebinar->duration_minutes }} minutes</strong></div>
                            <div class="detail-full"><span>Description</span><p>{{ $groupWebinar->description }}</p></div>
                            <div><span>Scheduled date and time</span><strong>{{ $groupWebinar->scheduled_at?->format('d M Y, h:i A') ?? 'Not scheduled' }}</strong></div>
                            <div><span>Meeting link</span>
                                @if($groupWebinar->meeting_link)
                                    <a href="{{ $groupWebinar->meeting_link }}" target="_blank" rel="noopener noreferrer">Open meeting link <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                                @else
                                    <strong class="text-secondary">Not added</strong>
                                @endif
                            </div>
                            @if($groupWebinar->admin_response)
                                <div class="detail-full"><span>Current admin response</span><p class="mb-0">{{ $groupWebinar->admin_response }}</p></div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm rounded-4 detail-card mb-4" id="participants">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div><h2 class="mb-1">Complete Participant List</h2><p class="text-secondary small mb-0">Each participant has an independent invitation and payment record.</p></div>
                            <span class="results-count-badge">{{ $groupWebinar->participants->count() }} participants</span>
                        </div>
                    </div>
                    <div class="table-responsive participant-table-scroll">
                        <table class="table participant-table align-middle mb-0">
                            <thead><tr><th>Participant</th><th>Account</th><th>Invitation</th><th>Payment</th><th>Individual Amount</th><th>Coupon</th><th>Razorpay Reference</th><th>Action</th></tr></thead>
                            <tbody>
                            @foreach($groupWebinar->participants as $participant)
                                @php
                                    $participantPaid = (float) $participant->payments
                                        ->where('status', \App\Models\GroupWebinarParticipantPayment::STATUS_PAID)
                                        ->where('currency', 'INR')
                                        ->sum(fn ($payment) => (float) $payment->amount);
                                    $paymentBadgeClass = match($participant->payment_status) {
                                        'paid' => 'payment-paid',
                                        'failed' => 'payment-failed',
                                        'not_required' => 'payment-none',
                                        default => 'payment-pending',
                                    };
                                    $latestDelivery = $participant->emailDeliveries->first();
                                    $invitationLabel = $participant->invitation_sent_at
                                        ? 'Sent'
                                        : ($latestDelivery?->failed_at
                                            ? 'Failed'
                                            : ($latestDelivery?->queued_at ? 'Queued' : 'Not sent'));
                                    $invitationClass = $invitationLabel === 'Sent' ? 'payment-paid' : ($invitationLabel === 'Failed' ? 'payment-failed' : 'payment-pending');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="participant-name">{{ $participant->name ?: ($participant->role === 'organizer' ? $groupWebinar->organizer_name : 'Name not provided') }}</div>
                                        <a href="mailto:{{ $participant->email }}">{{ $participant->email }}</a>
                                        @if($participant->role === 'organizer')<span class="organizer-tag">Organizer</span>@endif
                                    </td>
                                    <td>{{ $participant->user_id ? 'Registered User' : 'Guest' }}</td>
                                    <td><span class="payment-status-badge {{ $invitationClass }}">{{ $invitationLabel }}</span><div class="table-subtext">{{ $participant->invitation_expires_at?->format('d M Y, h:i A') ? 'Expires '.$participant->invitation_expires_at->format('d M Y, h:i A') : 'No expiry recorded' }}</div></td>
                                    <td>
                                        <span class="payment-status-badge {{ $paymentBadgeClass }}">{{ ucwords(str_replace('_', ' ', $participant->payment_status)) }}</span>
                                        <div class="table-subtext">{{ ucwords(str_replace('_', ' ', $participant->status)) }}</div>
                                    </td>
                                    <td>
                                        <div class="amount-success">Paid ₹{{ number_format($participantPaid, 2) }}</div>
                                        <div class="amount-primary">Individual amount ₹{{ number_format((float) $participant->amount_due, 2) }}</div>
                                        @if($participant->payment_status !== 'paid' && $participant->payment_status !== 'not_required')
                                            <div class="amount-pending">Outstanding ₹{{ number_format((float) $participant->amount_due, 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->coupon_code)
                                            <div class="fw-semibold">{{ $participant->coupon_code }}</div>
                                            <div class="table-subtext">Discount ₹{{ number_format((float) $participant->payments->max('discount_amount'), 2) }}</div>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td class="razorpay-references">
                                        @forelse($participant->payments as $payment)
                                            <div class="razorpay-payment-ref">{{ $payment->razorpay_payment_id ?: 'No payment ID' }}</div>
                                            <div class="table-subtext">Order {{ $payment->razorpay_order_id ?: '—' }} · {{ ucfirst($payment->status) }}</div>
                                            @if($payment->payment_error)<div class="text-danger small">{{ $payment->payment_error }}</div>@endif
                                        @empty
                                            <span class="text-secondary">No payment attempt</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if($participant->payment_status !== 'paid' && $participant->status !== 'cancelled')
                                            <form method="POST" action="{{ route('admin.group-webinars.participants.resend', $participant) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary resend-button" type="submit"><i class="bi bi-envelope-arrow-up me-1"></i>Resend invite</button>
                                            </form>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="card border-0 shadow-sm rounded-4 detail-card mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0"><h2>Payment Summary</h2></div>
                    <div class="card-body p-4">
                        <div class="payment-summary-grid">
                            <div><span>Total participants</span><strong>{{ $groupWebinar->participants->count() }}</strong></div>
                            <div><span>Total expected</span><strong>₹{{ number_format($expected, 2) }}</strong></div>
                            <div><span>Successfully collected</span><strong class="text-success">₹{{ number_format($collected, 2) }}</strong></div>
                            <div><span>Outstanding</span><strong class="text-warning-emphasis">₹{{ number_format($pending, 2) }}</strong></div>
                        </div>
                        <div class="payment-breakdown-list mt-4">
                            @foreach($groupWebinar->participants as $participant)
                                @php
                                    $individualPaid = (float) $participant->payments
                                        ->where('status', \App\Models\GroupWebinarParticipantPayment::STATUS_PAID)
                                        ->where('currency', 'INR')
                                        ->sum(fn ($payment) => (float) $payment->amount);
                                @endphp
                                <div class="payment-breakdown-row">
                                    <span>{{ $participant->name ?: $participant->email }} <small>{{ $participant->email }}{{ $participant->role === 'organizer' ? ' · Organizer' : '' }}</small></span>
                                    <span>Paid ₹{{ number_format($individualPaid, 2) }} <small>{{ ucwords(str_replace('_', ' ', $participant->payment_status)) }}</small></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="card border-0 shadow-sm rounded-4 detail-card scheduling-card">
                    <div class="card-body p-4">
                        <h2 class="mb-1">Admin Response &amp; Scheduling</h2>
                        <p class="text-secondary small mb-4">Save the status, schedule, meeting link, or participant-facing response.</p>
                        <form method="POST" action="{{ route('admin.group-webinars.update', $groupWebinar) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="status">Webinar status</label>
                                <select class="form-select" name="status" id="status" required>
                                    @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $groupWebinar->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="scheduledAt">Scheduled date and time</label>
                                <input class="form-control" type="datetime-local" name="scheduled_at" id="scheduledAt" value="{{ old('scheduled_at', $groupWebinar->scheduled_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="meetingLink">Meeting link</label>
                                <input class="form-control" type="url" name="meeting_link" id="meetingLink" value="{{ old('meeting_link', $groupWebinar->meeting_link) }}" maxlength="2048">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="adminResponse">Response / message for participants</label>
                                <textarea class="form-control" name="admin_response" id="adminResponse" rows="6" maxlength="5000">{{ old('admin_response', $groupWebinar->admin_response) }}</textarea>
                            </div>
                            <button class="btn btn-primary w-100 py-2"><i class="bi bi-send me-1"></i>Save and notify participants</button>
                        </form>
                        @if($groupWebinar->confirmedBy)<div class="small text-secondary mt-3">Last confirmed by {{ $groupWebinar->confirmedBy->name }}.</div>@endif
                    </div>
                </section>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .group-webinar-detail-page { width:100%; min-width:0; max-width:100%; }
            .group-webinar-detail-page *,.group-webinar-detail-page *::before,.group-webinar-detail-page *::after { box-sizing:border-box; }
            .back-link { color:#64748B; font-size:.82rem; text-decoration:none; }
            .back-link:hover { color:#2563EB; }
            .detail-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:14px 0 24px; }
            .detail-heading h1 { margin:0 0 5px; color:#0F172A; font-size:1.55rem; font-weight:800; }
            .detail-heading p { margin:0; color:#64748B; font-size:.84rem; }
            .detail-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:24px; }
            .detail-summary-card { display:flex; flex-direction:column; gap:9px; min-width:0; padding:19px; border:1px solid rgba(15,23,42,.06); border-radius:18px; background:#fff; box-shadow:0 8px 25px rgba(15,23,42,.05); }
            .detail-summary-card span,.payment-summary-grid span { color:#64748B; font-size:.74rem; font-weight:650; }
            .detail-summary-card strong { color:#0F172A; font-size:1.34rem; font-weight:850; overflow-wrap:anywhere; }
            .summary-collected strong { color:#15803D; } .summary-pending strong { color:#B45309; }
            .detail-card { min-width:0; overflow:hidden; }
            .detail-card h2 { margin:0; color:#0F172A; font-size:1rem; font-weight:800; }
            .detail-info-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px 26px; }
            .detail-info-grid > div { min-width:0; }
            .detail-info-grid > div > span { display:block; margin-bottom:6px; color:#64748B; font-size:.72rem; font-weight:650; }
            .detail-info-grid strong,.detail-info-grid a { color:#0F172A; font-size:.86rem; font-weight:700; overflow-wrap:anywhere; }
            .detail-info-grid a { color:#2563EB; text-decoration:none; }
            .detail-info-grid p { margin:0; color:#334155; font-size:.85rem; line-height:1.6; white-space:pre-line; }
            .detail-full { grid-column:1/-1; }
            .booking-status-badge,.payment-status-badge { display:inline-flex; align-items:center; width:max-content; max-width:100%; padding:6px 10px; border-radius:999px; font-size:.69rem; font-weight:800; white-space:nowrap; }
            .status-completed,.payment-paid { background:#DCFCE7; color:#15803D; }
            .status-confirmed { background:#DBEAFE; color:#1D4ED8; }
            .status-pending,.payment-pending { background:#FEF3C7; color:#B45309; }
            .status-cancelled,.payment-failed { background:#FEE2E2; color:#B91C1C; }
            .payment-none { background:#F1F5F9; color:#64748B; }
            .results-count-badge { display:inline-flex; width:max-content; padding:7px 11px; border-radius:999px; background:#EFF6FF; color:#1D4ED8; font-size:.72rem; font-weight:750; }
            .participant-table-scroll { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
            .participant-table { min-width:1380px; }
            .participant-table thead th { padding:13px 14px; border-bottom:1px solid #E2E8F0; background:#F8FAFC; color:#64748B; font-size:.66rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
            .participant-table tbody td { max-width:220px; padding:15px 14px; border-bottom:1px solid #F1F5F9; color:#475569; font-size:.75rem; vertical-align:top; overflow-wrap:anywhere; }
            .participant-table tbody tr:last-child td { border-bottom:0; }
            .participant-name { margin-bottom:4px; color:#0F172A; font-weight:750; }
            .participant-table a { color:#2563EB; font-size:.72rem; text-decoration:none; }
            .organizer-tag { display:block; width:max-content; margin-top:5px; padding:3px 7px; border-radius:999px; background:#EFF6FF; color:#1D4ED8; font-size:.63rem; font-weight:750; }
            .table-subtext { margin-top:4px; color:#94A3B8; font-size:.66rem; }
            .amount-success,.amount-primary,.amount-pending { margin-bottom:4px; color:#15803D; font-size:.7rem; font-weight:700; white-space:nowrap; }
            .amount-primary { color:#334155; } .amount-pending { color:#B45309; }
            .razorpay-payment-ref { color:#0F172A; font-size:.69rem; font-weight:700; }
            .razorpay-references > div + div { margin-top:7px; }
            .resend-button { font-size:.68rem; white-space:nowrap; }
            .payment-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
            .payment-summary-grid > div { display:flex; flex-direction:column; gap:6px; min-width:0; padding:13px; border-radius:12px; background:#F8FAFC; }
            .payment-summary-grid strong { color:#0F172A; font-size:.95rem; overflow-wrap:anywhere; }
            .payment-breakdown-list { border-top:1px solid #E2E8F0; }
            .payment-breakdown-row { display:flex; justify-content:space-between; gap:15px; padding:12px 0; border-bottom:1px solid #F1F5F9; color:#334155; font-size:.75rem; font-weight:700; }
            .payment-breakdown-row:last-child { border-bottom:0; }
            .payment-breakdown-row small { display:block; margin-top:4px; color:#94A3B8; font-size:.67rem; font-weight:500; }
            .payment-breakdown-row > span:last-child { text-align:right; white-space:nowrap; }
            .scheduling-card { position:sticky; top:90px; }
            .scheduling-card .form-label { color:#334155; font-size:.78rem; }
            .scheduling-card .form-control,.scheduling-card .form-select { min-height:43px; border-color:#E2E8F0; font-size:.83rem; }
            @media(max-width:1199px) { .scheduling-card { position:static; } }
            @media(max-width:767px) { .detail-summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; } .detail-summary-card { padding:15px; } .detail-summary-card strong { font-size:1.15rem; } .detail-info-grid { gap:18px 14px; } .payment-summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
            @media(max-width:480px) { .detail-heading { align-items:flex-start; flex-direction:column; } .detail-summary-grid { grid-template-columns:1fr 1fr; } .payment-breakdown-row { flex-direction:column; } .payment-breakdown-row > span:last-child { text-align:left; } }
        </style>
    @endpush
@endsection
