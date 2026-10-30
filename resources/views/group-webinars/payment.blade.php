@extends('layouts.frontend')

@section('title', 'Pay for Group Webinar')

@section('content')
<div class="container py-5" style="max-width: 680px">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <span class="badge rounded-pill bg-primary-subtle text-primary">Secure individual payment</span>
            <h1 class="h3 fw-bold mt-3">{{ $participant->request->topic }}</h1>
            <p class="text-secondary mb-4">Webinar {{ $participant->request->reference }}</p>
            <div class="d-flex justify-content-between border-bottom py-3"><span>Participant</span><strong>{{ $participant->email }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-3"><span>Original amount</span><strong>₹{{ number_format((float) $payment->original_amount, 2) }}</strong></div>
            @if((float) $payment->discount_amount > 0)
                <div class="d-flex justify-content-between border-bottom py-3"><span>Coupon discount ({{ $payment->coupon_code }})</span><strong>− ₹{{ number_format((float) $payment->discount_amount, 2) }}</strong></div>
            @endif
            <div class="d-flex justify-content-between py-3 fs-5"><span>Payable by you</span><strong>₹{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</strong></div>
            <p class="small text-secondary">This Razorpay order is linked only to your participant invitation. The organizer and other participants have separate payment links and orders.</p>
            @if(session('webinar_error'))<div class="alert alert-danger">{{ session('webinar_error') }}</div>@endif
            <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 mt-2" id="payButton">Pay securely with Razorpay</button>
            <a class="btn btn-link w-100 mt-2" href="{{ route('group-webinars.invitation', $token) }}">Back to invitation</a>
            <form method="POST" action="{{ route('group-webinars.payment.verify', ['token' => $token, 'payment' => $payment->public_reference]) }}" id="verificationForm" class="d-none">
                @csrf
                <input type="hidden" name="razorpay_payment_id">
                <input type="hidden" name="razorpay_signature">
            </form>
            <form method="POST" action="{{ route('group-webinars.payment.failure', ['token' => $token, 'payment' => $payment->public_reference]) }}" id="failureForm" class="d-none">
                @csrf
                <input type="hidden" name="razorpay_payment_id">
            </form>
            <form method="POST" action="{{ route('group-webinars.payment.abandon', ['token' => $token, 'payment' => $payment->public_reference]) }}" id="abandonForm" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', () => {
    const checkout = new Razorpay({
        key: @json($keyId),
        amount: {{ (int) round((float) $payment->amount * 100) }},
        currency: @json($payment->currency),
        name: @json(config('app.name')),
        description: @json($participant->request->topic),
        order_id: @json($payment->razorpay_order_id),
        prefill: { email: @json($participant->email) },
        theme: { color: '#2563eb' },
        modal: { ondismiss: () => document.getElementById('abandonForm').submit() },
        handler: response => {
            const form = document.getElementById('verificationForm');
            form.querySelector('[name="razorpay_payment_id"]').value = response.razorpay_payment_id;
            form.querySelector('[name="razorpay_signature"]').value = response.razorpay_signature;
            form.submit();
        }
    });
    checkout.on('payment.failed', response => {
        const paymentId = response.error && response.error.metadata
            ? response.error.metadata.payment_id
            : null;
        if (paymentId) {
            const form = document.getElementById('failureForm');
            form.querySelector('[name="razorpay_payment_id"]').value = paymentId;
            form.submit();
        }
    });
    checkout.open();
});
</script>
@endsection
