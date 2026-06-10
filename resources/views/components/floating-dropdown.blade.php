@props([
    'align' => 'bottom-start',
    'offset' => 6,
    'width' => 'w-64',
    'triggerClass' => '',
    'panelClass' => '',
])

{{-- Reusable floating dropdown (coding standard #8 — one component, not copy-pasted
     markup). Distinct from the Breeze <x-dropdown> used in the nav.

     The panel is teleported to <body> and pinned to the trigger with x-anchor
     (Floating UI), so an ancestor's overflow-hidden / rounded clipping can't chop
     the list. It opens downward by default, flips up only when there's genuinely no
     room below, and re-anchors on scroll/resize (Floating UI flip + shift +
     autoUpdate). Past its max-height the panel scrolls internally instead of growing.

     Caller supplies the trigger via <x-slot:trigger> and the panel rows as the
     default slot. Teleported content keeps the caller's surrounding Alpine scope,
     so option rows can still read/write the parent x-data (e.g. a `selected` array).
     `open` is exposed to the trigger slot for things like a rotating chevron. --}}
<div x-data="{ open: false }" {{ $attributes }}>
    <button type="button" x-ref="floatingTrigger" @click.stop="open = ! open"
            :aria-expanded="open" aria-haspopup="listbox"
            class="{{ $triggerClass }}">
        {{ $trigger }}
    </button>

    <template x-teleport="body">
        <div x-show="open"
             x-anchor.{{ $align }}.offset.{{ $offset }}="$refs.floatingTrigger"
             @click.outside="open = false"
             @keydown.escape.window="open = false"
             x-transition.opacity
             role="listbox"
             style="display:none"
             class="z-50 max-h-64 overflow-y-auto rounded-md bg-surface py-1 shadow-lg ring-1 ring-text/10 {{ $width }} {{ $panelClass }}">
            {{ $slot }}
        </div>
    </template>
</div>
