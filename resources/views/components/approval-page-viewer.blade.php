@props(['thesis'])

{{--
    Single "View approval page" button for a thesis, opening the stored
    approval/signature page image in an Alpine lightbox. Renders nothing when
    the thesis has no approval page. One button per thesis (not per name).
--}}
@if ($thesis->hasApprovalPage())
    @php($url = $thesis->approvalPageUrl())
    <div x-data="{ open: false }" class="mt-4">
        <x-btn type="button" variant="info" @click="open = true" aria-haspopup="dialog" x-bind:aria-expanded="open">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            View approval page
        </x-btn>

        <template x-teleport="body">
            <div x-show="open" x-cloak
                 role="dialog" aria-modal="true" aria-label="Thesis approval page"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-on:keydown.escape.window="open = false">
                {{-- Overlay --}}
                <div x-show="open" x-transition.opacity class="fixed inset-0 bg-text/70" @click="open = false"></div>

                {{-- Panel --}}
                <div x-show="open" x-transition
                     class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg bg-surface shadow-xl">
                    <div class="flex items-center justify-between border-b border-text/10 px-5 py-4">
                        <h2 class="text-base font-semibold text-text">Approval / signature page</h2>
                        <button type="button" @click="open = false" aria-label="Close"
                                class="rounded text-text/40 transition hover:text-text focus:outline-none focus:ring-2 focus:ring-cyan">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="overflow-auto p-4">
                        {{-- Load the image only once the modal is first opened. --}}
                        <img x-bind:src="open ? '{{ $url }}' : ''" alt="Thesis approval page"
                             class="mx-auto h-auto max-w-full rounded">
                        <p class="mt-3 text-center text-xs text-text/50">
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="font-semibold text-cyan hover:underline">Open in a new tab</a>
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
