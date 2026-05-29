<div class="page-hero">
  <div class="page-hero-inner">
    <h1 class="page-hero-title">
      @isset($icon)
        <span class="page-hero-icon">{!! $icon !!}</span>
      @endisset
      {{ $title }}
    </h1>

    <p class="page-hero-subtitle">{{ $subtitle }}</p>

    <div class="page-hero-breadcrumb">
      <a href="{{ route('home') }}">Home</a>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
      <span>{{ $breadcrumbLabel }}</span>
    </div>
  </div>
</div>
