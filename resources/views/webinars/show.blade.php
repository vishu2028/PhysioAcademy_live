@extends('layouts.frontend')
@section('title',$webinar->title)
@section('content')
<div class="container py-5"><div class="card p-4"><h1>{{ $webinar->title }}</h1><p class="lead">{{ $webinar->description }}</p><p>By {{ $webinar->speaker ?: 'Physio Academy' }} · {{ $webinar->starts_at->timezone($webinar->timezone)->format('d M Y, h:i A') }}</p><p class="fw-bold">{{ $webinar->isPaid() ? '₹'.number_format($webinar->price,2) : 'Free' }}</p>@if($registration)<div class="alert alert-success">You are {{ $registration->registration_status }}.</div>@elseif(auth()->check())<form method="post" action="{{ route('webinars.register',$webinar) }}">@csrf<button class="btn btn-primary">Register now</button></form>@else<a class="btn btn-primary" href="{{ route('login') }}">Login to register</a>@endif</div></div>
@endsection
