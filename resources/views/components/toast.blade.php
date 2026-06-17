{{--
    Reusable toast notification, driven by Laravel session flash messages:
        ->with('success', 'Thesis saved.')   or   ->with('error', '…')
    Corner-positioned, auto-dismissing, with success/error variants and a close
    button. Rendered once in the app shell so every authenticated page gets it.
    Tokens only — no hardcoded hex (coding standard #8).
--}}
@php
    $success = session('success');
    $error = session('error');
    $message = $success ?? $error;
    $isError = (bool) $error && ! $success;
@endphp

@if ($message)
    <div x-data="{ show: false }"
         x-init="$nextTick(() => show = true); setTimeout(() => show = false, 4500)"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-4 sm:translate-y-0"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         role="status" aria-live="polite"
         class="fixed top-4 right-4 z-50 w-[calc(100%-2rem)] max-w-sm">
        <div class="flex items-start gap-3 rounded-lg border-l-4 bg-surface p-4 shadow-lg
                    {{ $isError ? 'border-danger' : 'border-green' }}">
            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full
                         {{ $isError ? 'bg-danger/10 text-danger' : 'bg-green/10 text-green' }}">
                @if ($isError)
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                @else
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                @endif
            </span>
            <p class="min-w-0 flex-1 text-sm font-semibold text-text">{{ $message }}</p>
            <button type="button" @click="show = false" aria-label="Dismiss"
                    class="shrink-0 rounded text-text/40 transition hover:text-text focus:outline-none focus:ring-2 focus:ring-cyan">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
@endif
