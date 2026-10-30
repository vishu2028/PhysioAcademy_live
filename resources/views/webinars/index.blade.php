@extends('layouts.frontend')
@section('title','Webinars')
@section('content')
<div class="container py-5">
    @if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h1 class="fw-bold mb-1">Webinars</h1><p class="text-secondary mb-0">Join an upcoming webinar or request a private group session around a shared topic.</p></div>
        <div class="d-flex flex-wrap gap-2">
            @if(get_setting('webinar_group_enabled', '0') == '1' && get_setting('webinar_group_allow_requests', '1') == '1')
                @auth
                    <a class="btn btn-primary rounded-pill" href="{{ route('group-webinars.create') }}"><i class="bi bi-people me-1"></i>Request Group Webinar</a>
                @else
                    <a class="btn btn-primary rounded-pill" href="{{ route('login') }}"><i class="bi bi-people me-1"></i>Sign in to request a Group Webinar</a>
                @endauth
            @endif
            @auth
                <a class="btn btn-outline-primary rounded-pill" href="{{ route('doubt-sessions.create') }}">One-on-One Doubt Session</a>
            @else
                <a class="btn btn-outline-primary rounded-pill" href="{{ route('login') }}">One-on-One Doubt Session</a>
            @endauth
        </div>
    </div>
    <div class="row g-4">
        @forelse($webinars as $webinar)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <h2 class="h5 fw-bold">{{ $webinar->title }}</h2>
                    <p class="text-secondary">{{ Str::limit($webinar->description,120) }}</p>
                    <p class="small"><i class="bi bi-calendar me-1"></i>{{ $webinar->starts_at->timezone($webinar->timezone)->format('d M Y, h:i A') }}</p>
                    <a class="btn btn-outline-primary mt-auto" href="{{ route('webinars.show',$webinar) }}">View webinar</a>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-light border rounded-4">No upcoming webinars.</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $webinars->links() }}</div>
</div>
@endsection
