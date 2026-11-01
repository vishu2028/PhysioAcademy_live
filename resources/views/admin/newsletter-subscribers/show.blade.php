@extends('layouts.admin')

@section('title', 'Subscriber Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.newsletter-subscribers.index', $listingQuery) }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Newsletter Subscribers
    </a>
    <h2 class="fw-bold text-dark mt-2">Subscriber Details</h2>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-4 col-lg-3 text-secondary">Email Address</dt>
            <dd class="col-sm-8 col-lg-9 text-break">{{ $subscriber->email }}</dd>

            <dt class="col-sm-4 col-lg-3 text-secondary">Subscription Date</dt>
            <dd class="col-sm-8 col-lg-9">{{ $subscriber->subscribed_at->format('M d, Y') }}</dd>

            <dt class="col-sm-4 col-lg-3 text-secondary">Subscription Time</dt>
            <dd class="col-sm-8 col-lg-9">{{ $subscriber->subscribed_at->format('h:i:s A') }}</dd>

            <dt class="col-sm-4 col-lg-3 text-secondary">Status</dt>
            <dd class="col-sm-8 col-lg-9">{{ ucfirst($subscriber->status) }}</dd>
        </dl>
    </div>
</div>
@endsection
