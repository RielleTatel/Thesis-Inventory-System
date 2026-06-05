{{-- Label/value row for the thesis detail page. --}}
@props(['label', 'divider' => true])

<div class="grid sm:grid-cols-[180px_1fr] gap-2 sm:gap-5 py-4 @if ($divider) border-b border-text/10 @endif">
    <dt class="text-xs font-bold uppercase tracking-wide text-text/50 pt-0.5">{{ $label }}</dt>
    <dd class="flex flex-wrap gap-2 items-center">{{ $slot }}</dd>
</div>
