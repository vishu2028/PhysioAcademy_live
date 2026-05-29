@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'class' => '',
])

@php
    $baseClasses = 'btn fw-semibold transition-all duration-300 rounded-lg';
    $variants = [
        'primary' => 'btn-primary shadow-sm hover-shadow-lg',
        'secondary' => 'btn-secondary shadow-sm hover-shadow-lg',
        'outline-primary' => 'btn-outline-primary',
        'outline-dark' => 'btn-outline-dark',
        'link' => 'btn-link text-decoration-none',
        'white' => 'btn-white bg-white text-dark shadow-sm hover-shadow-lg',
    ];
    $sizes = [
        'sm' => 'btn-sm px-3 py-2',
        'md' => 'px-4 py-2',
        'lg' => 'btn-lg px-5 py-3',
    ];
    
    $classes = "{$baseClasses} " . ($variants[$variant] ?? $variants['primary']) . " " . ($sizes[$size] ?? $sizes['md']) . " {$class}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

<style>
    .btn-white:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
    }
    .hover-shadow-lg:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
</style>
