@extends('layouts.frontend')
@section('title','Complete payment')
@section('content')
<div class="container py-5 text-center"><h2>Complete payment for {{ $webinar->title }}</h2><p>₹{{ number_format($registration->amount,2) }}</p><button id="pay" class="btn btn-primary">Pay now</button></div>
@endsection
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script><script>
document.getElementById('pay').onclick=function(){new Razorpay({key:"{{ app(App\Services\RazorpayService::class)->keyId() }}",amount:"{{ $order['amount'] }}",currency:"{{ $order['currency'] }}",name:"{{ config('app.name') }}",order_id:"{{ $order['id'] }}",handler:function(r){let f=document.createElement('form');f.method='POST';f.action="{{ route('webinars.payment.verify',$registration) }}";f.innerHTML='<input name="_token" value="{{ csrf_token() }}"><input name="razorpay_payment_id" value="'+r.razorpay_payment_id+'"><input name="razorpay_signature" value="'+r.razorpay_signature+'">';document.body.appendChild(f);f.submit();}}).open()};
</script>
@endpush
