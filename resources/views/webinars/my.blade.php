@extends('layouts.frontend')
@section('title','My Webinars')
@section('content')
<div class="container py-5"><h1>My Webinars</h1>@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif<div class="row g-3">@forelse($registrations as $registration)<div class="col-md-6"><div class="card p-3"><h4>{{ $registration->webinar->title }}</h4><p>{{ $registration->webinar->starts_at->timezone($registration->webinar->timezone)->format('d M Y, h:i A') }}</p><span class="badge bg-secondary">{{ $registration->registration_status }}</span>@if($registration->registration_status === 'confirmed' && $registration->webinar->meeting_link)<a class="btn btn-primary mt-3" href="{{ route('webinars.join',$registration) }}">Join webinar</a>@endif</div></div>@empty<p>You have no webinar registrations.</p>@endforelse</div>{{ $registrations->links() }}</div>
@endsection
