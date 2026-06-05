{{-- Small status/metadata pill. Default tone reads as the cyan "year" badge. --}}
@props(['tone' => 'cyan'])

@php
    $tones = [
        'cyan' => 'bg-cyan/15 text-navy',
        'gold' => 'bg-gold/20 text-navy',
        'green' => 'bg-green/15 text-green',
    ];
    $tone = $tones[$tone] ?? $tones['cyan'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold $tone"]) }}>
    {{ $slot }}
</span>
