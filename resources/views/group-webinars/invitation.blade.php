@extends('layouts.frontend')

@section('title', 'Group Webinar Invitation')

@section('content')
<div class="container py-5" style="max-width: 820px">
    <a class="small text-secondary text-decoration-none" href="{{ route('webinars.index') }}">← Webinars</a>
    <div class="card border-0 shadow-sm rounded-4 mt-3">
        <div class="card-body p-4 p-md-5">
            @if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
            @if(session('webinar_error'))<div class="alert alert-warning rounded-3">{{ session('webinar_error') }}</div>@endif

            @if($expired)
                <span class="badge bg-warning-subtle text-warning-emphasis">Invitation expired</span>
                <h1 class="h3 fw-bold mt-3">Request a new invitation</h1>
                <p class="text-secondary">This payment link has expired. Enter the reference and email from your invitation to request a replacement. For your security, we will not reveal whether those details match.</p>
                <form method="POST" action="{{ route('group-webinars.invitation.resend') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="reference">Webinar reference</label>
                        <input class="form-control" name="reference" id="reference" value="{{ old('reference') }}" required maxlength="50">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Participant email</label>
                        <input class="form-control" type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255">
                    </div>
                    <div class="col-12"><button class="btn btn-primary rounded-pill">Request a new invitation</button></div>
                </form>
            @else
                <span class="badge rounded-pill bg-primary-subtle text-primary">Participant invitation</span>
                <h1 class="h3 fw-bold mt-3">{{ $participant->request->topic }}</h1>
                <p class="text-secondary">{{ $participant->request->description }}</p>
                <div class="row g-3 my-3">
                    <div class="col-md-6"><div class="bg-light rounded-3 p-3"><div class="small text-secondary">Webinar reference</div><strong>{{ $participant->request->reference }}</strong></div></div>
                    <div class="col-md-6"><div class="bg-light rounded-3 p-3"><div class="small text-secondary">Organizer</div><strong>{{ $participant->request->organizer_name }}</strong><div class="small">{{ $participant->request->organizer_email }}</div></div></div>
                    <div class="col-md-6"><div class="bg-light rounded-3 p-3"><div class="small text-secondary">Your participant email</div><strong>{{ $participant->email }}</strong></div></div>
                    <div class="col-md-6"><div class="bg-light rounded-3 p-3"><div class="small text-secondary">Your individual amount</div><strong>{{ $participant->request->is_free ? 'Free' : '₹'.number_format((float) $participant->amount_due, 2) }}</strong></div></div>
                </div>
                <p class="mb-3">Your payment status: <span class="badge {{ $participant->payment_status === 'paid' ? 'bg-success' : ($participant->payment_status === 'not_required' ? 'bg-info' : 'bg-warning text-dark') }}">{{ ucwords(str_replace('_', ' ', $participant->payment_status)) }}</span></p>
                <div class="alert alert-primary rounded-3">Each person pays their own amount through a unique invitation. The organizer does not pay for the group.</div>

                @if($participant->payment_status === 'paid' || $participant->payment_status === 'not_required')
                    <div class="alert alert-success rounded-3 mb-0">{{ $participant->payment_status === 'paid' ? 'Your payment has been verified.' : 'No payment is required for this webinar.' }}</div>
                @elseif($participant->status === 'cancelled')
                    <div class="alert alert-danger rounded-3 mb-0">Your participant registration has been cancelled.</div>
                @else
                    <form method="POST" action="{{ route('group-webinars.payment.start', $token) }}" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="coupon">Coupon code (optional)</label>
                            <div class="input-group">
                                <input class="form-control" name="coupon_code" id="coupon" value="{{ old('coupon_code') }}" maxlength="64" autocomplete="off">
                                <button class="btn btn-outline-primary" type="button" id="applyCoupon">Apply</button>
                            </div>
                            @error('coupon_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <p id="couponMessage" class="small mt-2 mb-0" role="status" aria-live="polite"></p>
                            <div id="couponBreakdown" class="border rounded-3 p-3 mt-2" hidden>
                                <div class="d-flex justify-content-between gap-3"><span>Original amount</span><strong id="couponOriginal"></strong></div>
                                <div class="d-flex justify-content-between gap-3"><span>Discount</span><strong id="couponDiscount"></strong></div>
                                <div class="d-flex justify-content-between gap-3"><span>Payable</span><strong id="couponFinal"></strong></div>
                                <button type="button" class="btn btn-link btn-sm px-0" id="removeCoupon">Remove coupon</button>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-lg rounded-pill px-4">Pay your individual amount</button>
                    </form>
                @endif

                @if($participant->request->scheduled_at)
                    <hr class="my-4">
                    <div><strong>Scheduled:</strong> {{ $participant->request->scheduled_at->format('d M Y, h:i A') }}</div>
                @endif
                @if($participant->request->meeting_link)
                    <div class="mt-2"><strong>Meeting link:</strong> <a href="{{ $participant->request->meeting_link }}" rel="noopener noreferrer">{{ $participant->request->meeting_link }}</a></div>
                @endif
                @if($participant->request->admin_response)
                    <div class="alert alert-light border mt-3 mb-0"><strong>Message from the admin</strong><br>{{ $participant->request->admin_response }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
<script>
(() => {
    const input = document.getElementById('coupon');
    const apply = document.getElementById('applyCoupon');
    const remove = document.getElementById('removeCoupon');
    const message = document.getElementById('couponMessage');
    const breakdown = document.getElementById('couponBreakdown');
    if (!input || !apply || !breakdown) return;
    let quotedCode = '';

    const money = amount => `₹${Number(amount).toFixed(2)}`;
    apply.addEventListener('click', async () => {
        apply.disabled = true;
        message.textContent = 'Validating coupon…';
        message.className = 'small mt-2 mb-0 text-muted';
        try {
            const response = await fetch(@json(route('group-webinars.coupon.quote', $token)), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({code: input.value})
            });
            const quote = await response.json();
            if (!response.ok) throw new Error(quote.message || 'Invalid coupon code.');
            document.getElementById('couponOriginal').textContent = money(quote.original_amount);
            document.getElementById('couponDiscount').textContent = `−${money(quote.discount_amount)}`;
            document.getElementById('couponFinal').textContent = money(quote.final_amount);
            input.value = quote.code;
            quotedCode = quote.code;
            breakdown.hidden = false;
            message.textContent = quote.message;
            message.className = 'small mt-2 mb-0 text-success';
        } catch (error) {
            quotedCode = '';
            breakdown.hidden = true;
            message.textContent = error.message || 'Unable to validate coupon.';
            message.className = 'small mt-2 mb-0 text-danger';
        } finally {
            apply.disabled = false;
        }
    });

    remove?.addEventListener('click', () => {
        input.value = '';
        quotedCode = '';
        breakdown.hidden = true;
        message.textContent = 'Coupon removed.';
        message.className = 'small mt-2 mb-0 text-muted';
    });
    input.addEventListener('input', () => {
        if (input.value.trim().toUpperCase() !== quotedCode) {
            quotedCode = '';
            breakdown.hidden = true;
            message.textContent = 'Apply the edited code to recalculate your price.';
            message.className = 'small mt-2 mb-0 text-muted';
        }
    });
    if (input.value) apply.click();
})();
</script>
@endsection
