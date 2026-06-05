{{-- Uppercase section heading; optional cyan underline accent. --}}
@props(['underline' => false])

<div {{ $attributes->merge(['class' => 'text-xs font-bold uppercase tracking-wide text-text/50'
    . ($underline ? ' inline-block border-b-2 border-cyan pb-1' : '')]) }}>
    {{ $slot }}
</div>
