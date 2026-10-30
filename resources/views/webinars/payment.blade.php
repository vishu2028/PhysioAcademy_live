@extends('layouts.frontend')

@section('title', 'Complete Webinar Payment')

@section('content')
<main class="container py-5" style="max-width:760px">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <h1 class="h3 mb-2">Complete Webinar Payment</h1>
        <p class="text-muted">{{ $webinar->title }}</p>
        <div class="alert alert-light border">
            <div class="d-flex justify-content-between"><span>Registration reference</span><strong>{{ $registration->registration_reference }}</strong></div>
            <div class="d-flex justify-content-between mt-2"><span>Student</span><strong>{{ $registration->student_name }}</strong></div>
            <div class="d-flex justify-content-between mt-2"><span>Original amount</span><strong>₹{{ number_format($registration->original_amount ?? $registration->amount, 2) }}</strong></div>
            @if($registration->coupon_code)
                <div class="d-flex justify-content-between mt-2"><span>Coupon</span><strong>{{ $registration->coupon_code }}</strong></div>
                <div class="d-flex justify-content-between mt-2"><span>Discount</span><strong>− ₹{{ number_format($registration->discount_amount, 2) }}</strong></div>
            @endif
            <div class="d-flex justify-content-between mt-2 fs-5"><span>Final payable</span><strong class="text-primary">₹{{ number_format($registration->amount, 2) }}</strong></div>
        </div>
        <div id="webinarPaymentMessage" class="alert d-none" role="status"></div>
        <button id="webinarPayNow" class="btn btn-primary btn-lg w-100" type="button">Pay ₹{{ number_format($registration->amount, 2) }}</button>
        <a class="btn btn-link mt-2" href="{{ route('webinars.show', $webinar) }}">Return to webinar</a>
        <form id="webinarPaymentVerification" method="post" action="{{ route('webinars.payment.verify', $registration) }}" class="d-none">
            @csrf
            <input name="razorpay_payment_id" id="webinarPaymentId">
            <input name="razorpay_signature" id="webinarPaymentSignature">
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const button=document.getElementById('webinarPayNow'),message=document.getElementById('webinarPaymentMessage');
    let opening=false;
    button.addEventListener('click',()=>{
        if(opening)return;
        if(typeof window.Razorpay!=='function'){message.textContent='Razorpay Checkout could not be loaded. Please refresh and try again.';message.className='alert alert-danger';return;}
        opening=true;button.disabled=true;
        const checkout=new window.Razorpay({
            key:@json(app(\App\Services\RazorpayService::class)->keyId()),
            amount:@json($order['amount']),currency:@json($order['currency']),
            name:@json(config('app.name')),description:@json($webinar->title),order_id:@json($order['id']),
            handler:(response)=>{
                document.getElementById('webinarPaymentId').value=response.razorpay_payment_id;
                document.getElementById('webinarPaymentSignature').value=response.razorpay_signature;
                document.getElementById('webinarPaymentVerification').submit();
            },
            modal:{ondismiss:()=>{opening=false;button.disabled=false;message.textContent='Payment was cancelled. You can try again.';message.className='alert alert-warning';}}
        });
        checkout.on('payment.failed',(response)=>{opening=false;button.disabled=false;message.textContent=response.error?.description||'Payment failed. Please try again.';message.className='alert alert-danger';});
        checkout.open();
    });
});
</script>
@endpush
