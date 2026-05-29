@extends('layouts.frontend')

@section('title', $page->title)

@push('styles')
<style>
    .page-hero {
        padding: 160px 0 80px;
        background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent 400px),
                    radial-gradient(circle at bottom left, rgba(56, 189, 248, 0.05), transparent 400px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
    }
    .page-title {
        font-family: 'Sora', sans-serif;
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 20px;
    }
    .page-content-wrap {
        padding: 80px 0;
        min-height: 400px;
    }
    .page-body {
        max-width: 800px;
        margin: 0 auto;
        font-size: 1.1rem;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.8);
    }
    .page-body h2, .page-body h3 {
        color: #fff;
        margin-top: 40px;
        margin-bottom: 20px;
    }
    .page-body ul, .page-body ol {
        margin-bottom: 30px;
        padding-left: 20px;
    }
    .page-body img {
        max-width: 100%;
        border-radius: 16px;
        margin: 40px 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
<div class="page-hero">
    <div class="section-container">
        <h1 class="page-title reveal-up">{{ $page->title }}</h1>
        <div class="hero-badge reveal-up delay-1">
            <span class="badge-dot"></span>
            <span>Last Updated: {{ $page->updated_at->format('M d, Y') }}</span>
        </div>
    </div>
</div>

<section class="page-content-wrap">
    <div class="section-container">
        <div class="page-body reveal-up delay-2">
            {!! $page->content !!}
        </div>
    </div>
</section>
@endsection
