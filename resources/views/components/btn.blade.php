@props([
    'variant' => 'primary',
    'href' => null,
])

@php
    // Single source of truth for button styling — variants map to brand tokens.
    $base = 'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 '
        .'text-sm font-semibold transition focus:outline-none focus:ring-2 '
        .'focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

    $variants = [
        'primary' => 'bg-navy text-surface hover:bg-navy/90 focus:ring-navy',
        'accent' => 'bg-gold text-text hover:bg-gold/90 focus:ring-gold',
        'success' => 'bg-green text-surface hover:bg-green/90 focus:ring-green',
        'info' => 'bg-cyan text-surface hover:bg-cyan/90 focus:ring-cyan',
        'ghost' => 'bg-transparent text-navy hover:bg-input focus:ring-navy',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
