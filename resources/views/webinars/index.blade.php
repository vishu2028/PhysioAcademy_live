@extends('layouts.frontend')
@section('title','Webinars')
@section('content')
<div class="container py-5"><h1>Upcoming Webinars</h1><div class="row g-4">@forelse($webinars as $webinar)<div class="col-md-4"><div class="card h-100 p-3"><h3>{{ $webinar->title }}</h3><p>{{ Str::limit($webinar->description,120) }}</p><p><i class="bi bi-calendar"></i> {{ $webinar->starts_at->timezone($webinar->timezone)->format('d M Y, h:i A') }}</p><a class="btn btn-primary" href="{{ route('webinars.show',$webinar) }}">View webinar</a></div></div>@empty<p>No upcoming webinars.</p>@endforelse</div>{{ $webinars->links() }}</div>
@endsection
