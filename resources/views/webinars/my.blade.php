@extends('layouts.frontend')
@section('title','My Webinars')
@section('content')
<div class="container py-5">
    <h1 class="fw-bold mb-4">My Webinars</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('webinar_error'))<div class="alert alert-warning">{{ session('webinar_error') }}</div>@endif

    <section class="mb-5">
        <h2 class="h4 fw-bold mb-3">Scheduled Webinars</h2>
        <div class="row g-3">
            @forelse($registrations as $registration)
                <div class="col-md-6"><div class="card p-3 h-100">
                    <h3 class="h5">{{ $registration->webinar->title }}</h3>
                    <p>{{ $registration->webinar->starts_at->timezone($registration->webinar->timezone)->format('d M Y, h:i A') }}</p>
                    <span class="badge bg-secondary">{{ $registration->registration_status }}</span>
                    @if($registration->registration_status === 'confirmed' && $registration->webinar->meeting_link)
                        <a class="btn btn-primary mt-3" href="{{ route('webinars.join', $registration) }}">Join webinar</a>
                    @endif
                </div></div>
            @empty
                <p class="text-secondary">You have no scheduled webinar registrations.</p>
            @endforelse
        </div>
        {{ $registrations->links() }}
    </section>

    <section>
        <h2 class="h4 fw-bold mb-3">Group Webinar Requests</h2>
        <div class="row g-3">
            @forelse($groupBookings as $booking)
                <div class="col-md-6"><article class="card p-3 h-100" id="group-booking-{{ $booking->request->id }}">
                    <div class="d-flex justify-content-between gap-3">
                        <h3 class="h5">{{ $booking->request->topic }}</h3>
                        <span class="badge bg-secondary align-self-start">{{ ucwords(str_replace('_', ' ', $booking->request->status)) }}</span>
                    </div>
                    <p class="small text-secondary mb-2">Reference {{ $booking->request->reference }}</p>
                    <dl class="row small mb-0">
                        <dt class="col-6">Scheduled</dt>
                        <dd class="col-6">{{ $booking->request->scheduled_at?->format('d M Y, h:i A') ?? 'Awaiting schedule' }}</dd>
                        <dt class="col-6">Students</dt>
                        <dd class="col-6">{{ $booking->request->participant_count }}</dd>
                        <dt class="col-6">Your amount</dt>
                        <dd class="col-6">₹{{ number_format((float) $booking->amount_due, 2) }}</dd>
                        <dt class="col-6">Payment</dt>
                        <dd class="col-6">{{ ucwords(str_replace('_', ' ', $booking->payment_status)) }}</dd>
                    </dl>
                    @if($booking->request->admin_response)
                        <div class="alert alert-light border mt-3 mb-0"><strong>Admin response</strong><br>{{ $booking->request->admin_response }}</div>
                    @endif
                    @if(
                        $booking->request->status === \App\Models\GroupWebinarRequest::STATUS_CONFIRMED
                        && in_array($booking->payment_status, [
                            \App\Models\GroupWebinarParticipant::PAYMENT_PAID,
                            \App\Models\GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
                        ], true)
                        && $booking->request->meeting_link
                    )
                        <a class="btn btn-primary mt-3" href="{{ route('group-webinars.join', $booking->request) }}">Join group webinar</a>
                    @endif
                    @if($booking->terms_accepted_at)
                        <p class="small text-secondary mt-3 mb-0">Policy {{ $booking->policy_version }} accepted {{ $booking->terms_accepted_at->format('d M Y, h:i A') }}.</p>
                    @endif
                    @foreach($booking->request->adjustments as $adjustment)
                        @if($booking->role === 'organizer' || $adjustment->participant_id === $booking->id)
                            <p class="small mt-3 mb-0">
                                @if($adjustment->type === \App\Models\GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND)
                                    Dropout refund: ₹{{ number_format((float) $adjustment->amount, 2) }} — {{ ucwords(str_replace('_', ' ', $adjustment->status)) }}
                                @else
                                    Future session credit: ₹{{ number_format((float) $adjustment->amount, 2) }} — {{ ucwords(str_replace('_', ' ', $adjustment->status)) }}
                                @endif
                                @if($adjustment->provider_reference)<span class="text-secondary">({{ $adjustment->provider_reference }})</span>@endif
                            </p>
                        @endif
                    @endforeach
                    @if(
                        $booking->role === 'organizer'
                        && $booking->request->status === \App\Models\GroupWebinarRequest::STATUS_CONFIRMED
                        && $booking->request->participant_count > 10
                        && $booking->request->scheduled_at
                        && now('Asia/Kolkata')->lte($booking->request->scheduled_at->copy()->timezone('Asia/Kolkata')->subDay()->setTime(21, 0))
                    )
                        @php
                            $paidParticipants = $booking->request->participants
                                ->where('payment_status', \App\Models\GroupWebinarParticipant::PAYMENT_PAID)
                                ->reject(fn ($participant) => $booking->request->adjustments->contains(
                                    fn ($adjustment) => $adjustment->participant_id === $participant->id
                                        && $adjustment->type === \App\Models\GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND
                                        && in_array($adjustment->status, [
                                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_PENDING,
                                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_FAILED,
                                        ], true)
                                ));
                        @endphp
                        @if($paidParticipants->isNotEmpty())
                            <form method="POST" action="{{ route('group-webinars.adjustments.store', $booking->request) }}" class="mt-3">
                                @csrf
                                <input type="hidden" name="type" value="dropout_refund">
                                <label class="form-label small fw-semibold" for="dropout-{{ $booking->id }}">Request ₹10 refund for a participant dropout</label>
                                <div class="input-group">
                                    <select class="form-select" name="participant_id" id="dropout-{{ $booking->id }}" required>
                                        <option value="">Select a paid participant</option>
                                        @foreach($paidParticipants as $paidParticipant)
                                            <option value="{{ $paidParticipant->id }}">{{ $paidParticipant->name ?: $paidParticipant->email }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-primary" type="submit">Request refund</button>
                                </div>
                            </form>
                        @endif
                    @endif
                    @if(
                        $booking->role === 'organizer'
                        && $booking->request->status === \App\Models\GroupWebinarRequest::STATUS_PENDING
                        && ! $booking->request->scheduled_at
                        && filled($booking->request->admin_response)
                        && ! $booking->request->adjustments->contains(fn ($adjustment) => $adjustment->type === \App\Models\GroupWebinarAdjustmentRequest::TYPE_SESSION_CREDIT && in_array($adjustment->status, [
                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_PENDING,
                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                            \App\Models\GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                        ], true))
                    )
                        <form method="POST" action="{{ route('group-webinars.adjustments.store', $booking->request) }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="type" value="session_credit">
                            <button class="btn btn-outline-secondary" type="submit">Request future session credit</button>
                        </form>
                    @endif
                </article></div>
            @empty
                <p class="text-secondary">You have no group webinar requests.</p>
            @endforelse
        </div>
        {{ $groupBookings->links() }}
    </section>
</div>
@endsection
