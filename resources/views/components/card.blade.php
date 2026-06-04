@props(['title' => null])

{{-- Reusable surface panel (coding standard #8). Use instead of copy-pasting
     bg-surface/shadow/rounded markup. --}}
<div {{ $attributes->merge(['class' => 'bg-surface rounded-lg shadow-sm overflow-hidden']) }}>
    @isset($title)
        <div class="px-6 py-4 border-b border-bg">
            <h2 class="font-semibold text-text">{{ $title }}</h2>
        </div>
    @endisset

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
