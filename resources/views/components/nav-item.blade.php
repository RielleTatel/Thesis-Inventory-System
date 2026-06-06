{{-- Sidebar nav link for the app shell. Active = gold; idle = muted on dark. --}}
@props(['href', 'active' => false])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition '
       .($active ? 'bg-gold text-navy' : 'text-surface/80 hover:bg-surface/10 hover:text-surface')]) }}>
    {{ $slot }}
</a>
