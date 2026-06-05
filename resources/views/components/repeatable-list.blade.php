{{-- Add/remove repeatable inputs for an ordered multi-value field.
     Submits as name[] in DOM order → the Action stores each with its position. --}}
@props([
    'name',
    'label',
    'hint' => null,
    'placeholder' => '',
    'values' => [''],
    'numbered' => true,
])

@php
    // Normalise to a non-empty list so the editor always shows one row.
    $initial = array_values(array_filter((array) $values, fn ($v) => trim((string) $v) !== ''));
    $initial = $initial === [] ? [''] : $initial;
@endphp

<div x-data="{ items: @js($initial) }">
    <label class="block text-sm font-semibold text-text mb-1">{{ $label }}</label>
    @if ($hint)
        <p class="text-xs text-text/50 mb-2">{{ $hint }}</p>
    @endif

    <div class="space-y-2">
        <template x-for="(item, idx) in items" :key="idx">
            {{-- Each entry is its own bordered box (design .rep-row): light surface,
                 border, with a white input inside. --}}
            <div class="flex items-center gap-2.5 rounded-md border border-text/10 bg-bg p-2">
                {{-- Order is top-to-bottom; handle is a visual affordance (no drag yet). --}}
                <span class="shrink-0 text-text/30 cursor-grab" aria-hidden="true" title="Order: top to bottom">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/>
                        <circle cx="15" cy="6" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                    </svg>
                </span>
                @if ($numbered)
                    <span class="grid place-items-center w-6 h-6 shrink-0 rounded-full bg-surface border border-text/20 text-text/70 text-xs font-bold"
                          x-text="idx + 1"></span>
                @endif
                <input type="text" name="{{ $name }}[]" x-model="items[idx]" placeholder="{{ $placeholder }}"
                       class="flex-1 rounded-md border border-text/10 bg-surface text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan focus:border-cyan">
                <button type="button" @click="items.splice(idx, 1)" x-show="items.length > 1"
                        class="grid place-items-center w-8 h-8 shrink-0 rounded-md text-text/50 hover:bg-surface hover:text-danger transition"
                        aria-label="Remove">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Bordered button (design .btn-secondary), not a plain text link. --}}
    <button type="button" @click="items.push('')"
            class="mt-2 inline-flex items-center gap-1.5 rounded-md border border-text/15 bg-surface px-3 py-1.5 text-sm font-semibold text-text hover:bg-bg hover:border-text/30 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Add another
    </button>

    @error($name)
        <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>
    @enderror
</div>
