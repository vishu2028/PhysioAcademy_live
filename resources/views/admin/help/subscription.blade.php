@extends('layouts.admin')

@section('title', 'Help · Subscription')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-center gap-3 mb-3">
                <i class="bi bi-credit-card fs-2 text-primary"></i>
                <div>
                    <p class="text-uppercase small fw-bold text-primary mb-1">Help</p>
                    <h1 class="h3 mb-0">Subscription</h1>
                </div>
            </div>
            <div class="alert alert-info">
                PhysioSource currently has no recurring subscription plans or user subscription management.
            </div>
            <p class="text-secondary">
                Existing payment flows are for individual webinar, group webinar, and doubt-session bookings.
                They do not define recurring plans, subscription access, or renewals.
            </p>
            <h2 class="h5 mt-4">Information needed before subscription management can be implemented</h2>
            <ul class="text-secondary mb-0">
                <li>Plan names, prices, currency, and billing intervals</li>
                <li>Which features or content each plan grants access to</li>
                <li>Trial, renewal, cancellation, refund, and failed-payment rules</li>
                <li>The payment provider and required subscription/webhook behavior</li>
            </ul>
        </div>
    </div>
</div>
@endsection
