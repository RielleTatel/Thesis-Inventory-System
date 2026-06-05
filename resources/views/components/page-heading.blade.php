{{-- Page heading with the brand cyan underline accent. Subtitle via slot. --}}
@props(['title'])

<div {{ $attributes }}>
    <h1 class="text-2xl font-bold text-navy">{{ $title }}</h1>
    <span class="block w-12 h-1 mt-2 rounded-full bg-cyan"></span>
    @if (trim($slot) !== '')
        <div class="mt-3 text-sm text-text/60">{{ $slot }}</div>
    @endif
</div>
