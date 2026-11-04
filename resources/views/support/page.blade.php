@extends('layouts.frontend')

@section('title', $pageTitle)

@push('styles')
<style>
    .support-page { padding: 140px 0 80px; background: linear-gradient(180deg, #f8fbff 0%, #fff 45%); min-height: 65vh; }
    .support-card { max-width: 900px; margin: 0 auto; padding: clamp(24px, 5vw, 56px); background: #fff; border: 1px solid #e6edf7; border-radius: 28px; box-shadow: 0 24px 70px rgba(15, 65, 120, .08); }
    .support-kicker { color: #2563eb; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .support-title { color: #0f172a; font-family: Sora, sans-serif; font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; margin: 10px 0 30px; }
    .support-content { color: #475569; font-size: 1.05rem; line-height: 1.85; }
    .support-content h2, .support-content h3, .support-content h4 { color: #0f172a; margin: 2rem 0 1rem; }
    .support-content img { max-width: 100%; height: auto; border-radius: 14px; }
    .support-content table { width: 100%; margin: 1.5rem 0; border-collapse: collapse; }
    .support-content th, .support-content td { border: 1px solid #dbe5f1; padding: .7rem; }
    .support-content a { color: #2563eb; }
    .support-empty { color: #64748b; background: #f8fafc; border-radius: 16px; padding: 20px; }
    .support-form { border-top: 1px solid #e6edf7; margin-top: 36px; padding-top: 30px; }
    @media (max-width: 575px) { .support-page { padding-top: 110px; } }
</style>
@endpush

@section('content')
<main class="support-page">
    <div class="section-container">
        <article class="support-card">
            <div class="support-kicker">PhysioSource Support</div>
            <h1 class="support-title">{{ brand_text($pageTitle) }}</h1>

            @if($slug === 'faq' && $faqs->isNotEmpty())
                <div class="support-content">
                    @foreach($faqs as $faq)
                        <h3>{{ brand_text($faq->question) }}</h3>
                        <p>{!! nl2br(e(brand_text($faq->answer))) !!}</p>
                    @endforeach
                </div>
            @elseif($page?->content)
                <div class="support-content">{!! brand_text($page->content) !!}</div>
            @else
                <div class="support-empty">This page is being prepared. Please check back soon.</div>
            @endif

            @if($slug === 'feedback')
                <div class="support-form">
                    <h2 class="h4 fw-bold text-dark mb-3">Share your feedback</h2>
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('support.feedback.submit') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="feedbackName">Name</label><input id="feedbackName" name="name" class="form-control" value="{{ old('name', auth()->user()?->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="feedbackEmail">Email</label><input id="feedbackEmail" name="email" type="email" class="form-control" value="{{ old('email', auth()->user()?->email) }}" required></div>
                            <div class="col-12"><label class="form-label" for="feedbackMessage">Feedback</label><textarea id="feedbackMessage" name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea></div>
                            <div class="col-12"><button class="btn btn-primary rounded-pill px-4" type="submit">Send Feedback</button></div>
                        </div>
                    </form>
                </div>
            @endif
        </article>
    </div>
</main>
@endsection
