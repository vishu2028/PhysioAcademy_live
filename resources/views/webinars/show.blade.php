@extends('layouts.frontend')
@section('title',brand_text($webinar->title))
@section('content')
<div class="container py-5"><div class="card p-4 rounded-4 border-0 shadow-sm">
<a href="{{ route('webinars.index') }}" class="small text-secondary text-decoration-none mb-3">← All Webinars</a>
<h1 class="fw-bold">{{ brand_text($webinar->title) }}</h1>
@if($webinar->subject)<p class="text-primary fw-semibold">{{ brand_text($webinar->subject) }}</p>@endif
<p class="lead">{{ brand_text($webinar->description) }}</p>
<div class="row g-3 my-2">
    <div class="col-sm-6"><strong>Instructor</strong><br>{{ brand_text($webinar->speaker ?: 'PhysioSource') }}</div>
    <div class="col-sm-6"><strong>Schedule</strong><br>{{ $webinar->starts_at->timezone($webinar->timezone)->format('d M Y, h:i A') }} ({{ $webinar->timezone }})</div>
    <div class="col-sm-6"><strong>Duration</strong><br>{{ $webinar->duration_minutes }} minutes</div>
    <div class="col-sm-6"><strong>Price</strong><br>{{ $webinar->isPaid() ? '₹'.number_format($webinar->price,2) : 'Free' }}</div>
    <div class="col-sm-6"><strong>Registration</strong><br>{{ $registrationOpen ? 'Open' : 'Closed' }}</div>
    <div class="col-sm-6"><strong>Available seats</strong><br>{{ $webinar->capacity ? max(0, $webinar->capacity - $webinar->active_registrations_count) : 'Open capacity' }}</div>
    @if($webinar->learning_objectives)
        <div class="col-12"><strong>Learning objectives</strong><br>{{ $webinar->learning_objectives }}</div>
    @endif
</div>
@if(session('webinar_error'))<div class="alert alert-danger">{{ session('webinar_error') }}</div>@endif
@if($errors->has('coupon_code'))<div class="alert alert-danger">{{ $errors->first('coupon_code') }}</div>@endif
@if($registration)<div class="alert alert-success">You are {{ $registration->registration_status }}.</div>@elseif(! $registrationOpen)
<div class="alert alert-light border">Registration is closed for this webinar.</div>
@elseif(auth()->check())
<form method="post" action="{{ route('webinars.register',$webinar) }}" id="webinarRegisterForm">@csrf
@if($webinar->isPaid())
<section class="coupon-entry-card mb-3"><h3>Apply Coupon</h3><div class="input-group"><input id="webinarCouponInput" class="form-control text-uppercase" maxlength="64" placeholder="Enter coupon code"><button id="webinarCouponApply" class="btn btn-outline-primary" type="button">Apply</button></div><input type="hidden" name="coupon_code" id="webinarCouponCode" value="{{ old('coupon_code') }}"><p id="webinarCouponMessage" class="small mt-2 mb-0" role="status" aria-live="polite"></p><div id="webinarCouponBreakdown" class="mt-2" hidden><div>Original price: <strong id="webinarCouponOriginal"></strong></div><div>Applied coupon: <strong id="webinarCouponApplied"></strong></div><div>Discount type: <strong id="webinarCouponType"></strong></div><div>Discount amount: <strong id="webinarCouponDiscount"></strong></div><div>Final payable: <strong id="webinarCouponFinal"></strong></div><button id="webinarCouponRemove" class="btn btn-link btn-sm px-0" type="button">Remove coupon</button></div></section>
@endif
<button class="btn btn-primary" type="submit">{{ $webinar->isPaid() ? 'Continue to payment' : 'Register now' }}</button></form>
@else<a class="btn btn-primary" href="{{ route('login', ['redirect' => route('webinars.show', $webinar)]) }}">Login to register</a>@endif</div></div>
@if($webinar->isPaid())
<style>.coupon-entry-card{padding:18px;border:1px solid rgba(0,74,173,.16);border-radius:16px;background:#f7faff}.coupon-entry-card h3{font-size:1rem;font-weight:800;color:#0f172a}.coupon-entry-card strong{color:#004AAD}</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{const input=document.getElementById('webinarCouponInput'),hidden=document.getElementById('webinarCouponCode'),apply=document.getElementById('webinarCouponApply'),remove=document.getElementById('webinarCouponRemove'),msg=document.getElementById('webinarCouponMessage'),breakdown=document.getElementById('webinarCouponBreakdown');const money=n=>`₹${Number(n).toFixed(2)}`;apply?.addEventListener('click',async()=>{apply.disabled=true;msg.textContent='Validating coupon…';try{const r=await fetch({{ Js::from(route('coupons.validate')) }},{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({code:input.value,service:'webinar',webinar_id:{{ Js::from($webinar->id) }})});const q=await r.json();if(!r.ok)throw new Error(q.message||'Invalid coupon code.');hidden.value=q.code;input.value=q.code;document.getElementById('webinarCouponApplied').textContent=q.code;document.getElementById('webinarCouponType').textContent=q.discount_description;document.getElementById('webinarCouponOriginal').textContent=money(q.original_amount);document.getElementById('webinarCouponDiscount').textContent=`−${money(q.discount_amount)}`;document.getElementById('webinarCouponFinal').textContent=money(q.final_amount);breakdown.hidden=false;msg.textContent=q.message;msg.className='small mt-2 mb-0 text-success';}catch(e){hidden.value='';breakdown.hidden=true;msg.textContent=e.message;msg.className='small mt-2 mb-0 text-danger';}finally{apply.disabled=false;}});input?.addEventListener('input',()=>{if(input.value.toUpperCase()!==hidden.value){hidden.value='';breakdown.hidden=true;msg.textContent='Apply the edited code to recalculate your price.';msg.className='small mt-2 mb-0 text-muted';}});remove?.addEventListener('click',()=>{hidden.value='';input.value='';breakdown.hidden=true;msg.textContent='Coupon removed.';msg.className='small mt-2 mb-0 text-muted';});if(hidden?.value)apply.click();});
</script>
@endif
@endsection
