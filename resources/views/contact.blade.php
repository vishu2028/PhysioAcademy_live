@extends('layouts.frontend')

@section('title', $page->title ?? 'Contact Us')

@push('styles')
<style>
    .contact-hero {
        padding: 160px 0 80px;
        background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 600px),
                    radial-gradient(circle at bottom left, rgba(56, 189, 248, 0.08), transparent 600px);
        text-align: center;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 40px;
        margin-top: -60px;
        position: relative;
        z-index: 10;
        margin-bottom: 100px;
    }
    .contact-info-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 32px;
        padding: 40px;
    }
    .contact-form-card {
        background: #fff;
        border-radius: 32px;
        padding: 50px;
        box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.1);
    }
    .info-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }
    .info-icon {
        width: 54px;
        height: 54px;
        background: rgba(37, 99, 235, 0.1);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .info-text h4 {
        color: #fff;
        margin-bottom: 5px;
        font-family: 'Sora', sans-serif;
    }
    .info-text p {
        color: rgba(255, 255, 255, 0.6);
        margin: 0;
    }
    .form-label {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 10px;
        display: block;
    }
    .form-control {
        background: #f8fafc;
        border: 2px solid transparent;
        border-radius: 16px;
        padding: 16px 20px;
        width: 100%;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }
    .submit-btn {
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
        border: none;
        padding: 18px 40px;
        border-radius: 16px;
        font-weight: 800;
        font-size: 1.1rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
    }
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(37, 99, 235, 0.3);
    }
    @media (max-width: 992px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $infoSection = $sections->get('contact-info');
    $socialSection = $sections->get('contact-socials');
    $socialItems = $socialSection ? $socialSection->items->where('enabled', true) : collect();
@endphp
<div class="contact-hero">
    <div class="section-container">
        <h1 class="hero-title reveal-up">{{ $page->title ?? 'Get in Touch' }}</h1>
        <p class="hero-subtitle reveal-up delay-1">{{ $page->meta_description ?? 'Have questions? We\'re here to help you on your academic journey.' }}</p>
    </div>
</div>

<div class="section-container">
    <div class="contact-grid">
        <div class="contact-info-card reveal-up delay-2">
            <h3 class="text-white mb-4 fw-bold">Contact Information</h3>
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-envelope"></i></div>
                <div class="info-text">
                    <h4>Email Us</h4>
                    <p>{{ $infoSection->content['email'] ?? get_setting('contact_email', 'hello@physiosphere.com') }}</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                <div class="info-text">
                    <h4>Location</h4>
                    <p>{{ $infoSection->content['address'] ?? get_setting('contact_address', 'Medical Square, Academic Block, Earth') }}</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="bi bi-telephone"></i></div>
                <div class="info-text">
                    <h4>Call Support</h4>
                    <p>{{ $infoSection->content['phone'] ?? get_setting('contact_phone', '+1 234 567 890') }}</p>
                </div>
            </div>

            <div class="mt-5">
                <h4 class="text-white mb-3">{{ $socialSection->content['heading'] ?? 'Follow our journey' }}</h4>
                <div class="d-flex gap-3">
                    @forelse($socialItems as $item)
                        <a href="{{ $item->body }}" class="info-icon" style="width:44px; height:44px; font-size:1.2rem;" target="_blank">
                            <i class="bi {{ $item->meta['icon'] ?? 'bi-link-45deg' }}"></i>
                        </a>
                    @empty
                        <a href="#" class="info-icon" style="width:44px; height:44px; font-size:1.2rem;"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="info-icon" style="width:44px; height:44px; font-size:1.2rem;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="info-icon" style="width:44px; height:44px; font-size:1.2rem;"><i class="bi bi-linkedin"></i></a>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="contact-form-card reveal-up delay-3">
            @if(session('success'))
                <div class="alert alert-success rounded-4 p-4 mb-4 border-0 bg-success-subtle text-success fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="How can we help?" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="submit-btn">Send Message</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
