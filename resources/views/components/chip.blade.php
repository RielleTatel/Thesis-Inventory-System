{{-- Reusable chip for multi-value fields. `kind`: person | keyword. --}}
@props(['kind' => 'keyword'])

@php
    $base = 'inline-flex items-center text-xs font-medium';
    $kinds = [
        'person' => 'rounded-lg px-3 py-1.5 bg-surface border border-text/10 text-text',
        'keyword' => 'rounded-full px-3 py-1 bg-input text-navy/80 border border-navy/5',
    ];
    $kind = $kinds[$kind] ?? $kinds['keyword'];
@endphp

<span {{ $attributes->merge(['class' => "$base $kind"]) }}>
    {{ $slot }}
</span>
