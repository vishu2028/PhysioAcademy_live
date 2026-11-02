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
                <div class="d-flex justify-content-between border-bottom py-3"><span>Applied coupon</span><strong>{{ $payment->coupon_code }}</strong></div>
                <div class="d-flex justify-content-between border-bottom py-3"><span>Discount type</span><strong>
                    @if($payment->coupon?->discount_type === 'percentage')
                        {{ rtrim(rtrim(number_format((float) $payment->coupon->discount_value, 2, '.', ''), '0'), '.') }}% discount
                    @elseif($payment->coupon)
                        ₹{{ number_format((float) $payment->coupon->discount_value, 2) }} fixed discount
                    @else
                        Coupon discount
                    @endif
                </strong></div>
                <div class="d-flex justify-content-between border-bottom py-3"><span>Discount amount</span><strong>− ₹{{ number_format((float) $payment->discount_amount, 2) }}</strong></div>
            @endif
            <div class="d-flex justify-content-between py-3 fs-5"><span>Payable by you</span><strong>₹{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</strong></div>
            <p class="small text-secondary">This Razorpay order is linked only to your participant invitation. The organizer and other participants have separate payment links and orders.</p>
            @if(session('webinar_error'))<div class="alert alert-danger">{{ session('webinar_error') }}</div>@endif
            <div class="alert alert-danger d-none" id="checkoutMessage" role="alert"></div>
            <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 mt-2" id="payButton">Pay ₹{{ number_format((float) $payment->amount, 2) }} securely with Razorpay</button>
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
<script>
(() => {
    const button = document.getElementById('payButton');
    const message = document.getElementById('checkoutMessage');
    const scriptUrl = 'https://checkout.razorpay.com/v1/checkout.js';
    let scriptPromise;
    let checkoutOpen = false;

    const loadCheckout = () => {
        if (typeof window.Razorpay === 'function') return Promise.resolve();
        if (scriptPromise) return scriptPromise;

        scriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            const timeout = window.setTimeout(() => reject(new Error('Razorpay Checkout timed out.')), 15000);
            script.src = scriptUrl;
            script.async = true;
            script.crossOrigin = 'anonymous';
            script.onload = () => {
                window.clearTimeout(timeout);
                typeof window.Razorpay === 'function'
                    ? resolve()
                    : reject(new Error('Razorpay Checkout loaded without its API.'));
            };
            script.onerror = () => {
                window.clearTimeout(timeout);
                reject(new Error('Razorpay Checkout could not be loaded. Check your connection and retry.'));
            };
            document.head.appendChild(script);
        });
        return scriptPromise;
    };

    button.addEventListener('click', async () => {
        if (checkoutOpen) return;
        checkoutOpen = true;
        button.disabled = true;
        button.textContent = 'Loading secure checkout…';
        message.classList.add('d-none');

        try {
            await loadCheckout();
            const checkout = new window.Razorpay({
                key: @json($keyId),
                amount: @json($amountSubunits),
                currency: @json($payment->currency),
                name: @json(brand_text(config('app.name', 'PhysioSource'))),
                description: @json(brand_text($paymentDescription)),
                order_id: @json($payment->razorpay_order_id),
                prefill: { email: @json($participant->email) },
                theme: { color: '#2563eb' },
                modal: {
                    ondismiss: () => {
                        checkoutOpen = false;
                        document.getElementById('abandonForm').submit();
                    }
                },
                handler: response => {
                    const form = document.getElementById('verificationForm');
                    form.querySelector('[name="razorpay_payment_id"]').value = response.razorpay_payment_id;
                    form.querySelector('[name="razorpay_signature"]').value = response.razorpay_signature;
                    form.submit();
                }
            });
            checkout.on('payment.failed', response => {
                checkoutOpen = false;
                const paymentId = response.error?.metadata?.payment_id;
                if (paymentId) {
                    const form = document.getElementById('failureForm');
                    form.querySelector('[name="razorpay_payment_id"]').value = paymentId;
                    form.submit();
                    return;
                }
                message.textContent = response.error?.description || 'Payment failed. You can try again.';
                message.classList.remove('d-none');
                button.disabled = false;
                button.textContent = 'Pay ₹{{ number_format((float) $payment->amount, 2) }} securely with Razorpay';
            });
            checkout.open();
        } catch (error) {
            checkoutOpen = false;
            scriptPromise = null;
            message.textContent = error.message || 'Razorpay Checkout could not be loaded.';
            message.classList.remove('d-none');
            button.disabled = false;
            button.textContent = 'Retry Razorpay Checkout';
        }
    });
})();
</script>
@endsection
