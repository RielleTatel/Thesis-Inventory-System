@props([
    'target',          // id of the textarea this scanner fills (e.g. "abstract")
    'field' => 'text', // human label shown in the modal (e.g. "Abstract")
])

@php
    $modalId = 'ocr-'.$target;
    $reviewId = $target.'-ocr-review';
@endphp

{{--
    Reusable OCR scanner: a trigger button + an upload → review modal.
    UPLOAD path only for now, accepting MULTIPLE images per field (read in order
    and combined). Camera and QR paths plug in later by reusing the same
    controller (addFiles + the review step) — they only add a capture method.
    OCR text is always shown for edit and never auto-committed (FR-5.3).
--}}
<div x-data="ocrScanner('{{ $target }}')" class="inline-block">
    {{-- Trigger --}}
    <x-scan-button @click="openModal()" :aria-controls="$modalId" x-bind:aria-expanded="open" />

    {{-- Modal (teleported to body so it overlays the whole page) --}}
    <template x-teleport="body">
        <div x-show="open"
             id="{{ $modalId }}"
             role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title"
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
             style="display: none;"
             x-on:keydown.escape.window="closeModal()"
             x-on:paste.window="onPaste($event)">
            {{-- Overlay --}}
            <div x-show="open" x-transition.opacity
                 class="fixed inset-0 bg-text/60" @click="closeModal()"></div>

            {{-- Panel --}}
            <div x-show="open"
                 x-transition
                 x-ref="panel" tabindex="-1"
                 class="relative mx-auto mt-10 w-full max-w-lg rounded-lg bg-surface shadow-xl focus:outline-none">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-text/10 px-5 py-4">
                    <h2 id="{{ $modalId }}-title" class="text-base font-semibold text-text">
                        Scan {{ $field }} from a printed copy
                    </h2>
                    <button type="button" @click="closeModal()" aria-label="Close"
                            class="rounded text-text/40 transition hover:text-text focus:outline-none focus:ring-2 focus:ring-cyan">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="space-y-4 px-5 py-5">
                    <p x-show="error" x-text="error" x-cloak
                       class="rounded-md bg-danger/10 px-3 py-2 text-xs font-semibold text-danger"></p>

                    {{-- Dropzone (upload path — multiple images allowed) --}}
                    <label x-show="status === 'idle'"
                           @dragover.prevent="dragging = true"
                           @dragleave.prevent="dragging = false"
                           @drop.prevent="onDrop($event)"
                           :class="dragging ? 'border-cyan bg-input' : 'border-text/20'"
                           class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-10 text-center transition hover:bg-input">
                        <svg class="h-8 w-8 text-text/40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>
                        </svg>
                        <span class="text-sm font-semibold text-text">
                            Drop images here, <span class="text-cyan">browse</span>, or paste an image (Ctrl+V)
                        </span>
                        <span class="text-xs text-text/50">
                            PNG or JPG — clear photos, scans, or screenshots of the printed {{ strtolower($field) }}.
                            Add several and they're read in order.
                        </span>
                        <input type="file" accept="image/*" multiple class="sr-only"
                               x-ref="file" @change="onPick($event)">
                    </label>

                    {{-- Chosen images, in read order (with remove) --}}
                    <div x-show="status === 'idle' && files.length" x-cloak class="space-y-2">
                        <p class="text-xs font-semibold text-text/60">
                            <span x-text="files.length"></span> image<span x-show="files.length !== 1">s</span> — read in this order:
                        </p>
                        <ul class="space-y-1.5">
                            <template x-for="(file, index) in files" :key="index">
                                <li class="flex items-center gap-2 rounded-md bg-input px-3 py-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-navy text-[11px] font-semibold text-surface"
                                          x-text="index + 1"></span>
                                    <span class="min-w-0 flex-1 truncate text-xs text-text" x-text="file.name"></span>
                                    <button type="button" @click="removeFile(index)"
                                            :aria-label="`Remove ${file.name}`"
                                            class="shrink-0 rounded text-text/40 transition hover:text-danger focus:outline-none focus:ring-2 focus:ring-cyan">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Reading progress (per image) --}}
                    <div x-show="status === 'reading'" class="space-y-3 py-6" x-cloak>
                        <div class="flex items-center justify-between text-sm font-semibold text-text">
                            <span>Reading image <span x-text="current"></span> of <span x-text="total"></span>…</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-input">
                            <div class="h-full rounded-full bg-cyan transition-all" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="text-xs text-text/50">This runs in your browser and may take a few seconds per image.</p>
                    </div>

                    {{-- Review & edit (never auto-committed) --}}
                    <div x-show="status === 'done'" class="space-y-2" x-cloak>
                        <label for="{{ $reviewId }}" class="block text-sm font-semibold text-text">
                            Review &amp; edit the detected text
                        </label>
                        <textarea id="{{ $reviewId }}" x-model="text" rows="8"
                                  class="w-full rounded-md border-0 bg-input text-sm text-text placeholder:text-text/40 focus:ring-2 focus:ring-cyan"></textarea>
                        <p class="text-xs text-text/50">
                            Text from all images is combined in order. OCR can make mistakes — check it before
                            using it. Nothing is saved until you select &ldquo;Use this text&rdquo;.
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-text/10 px-5 py-4">
                    <x-btn type="button" variant="ghost" @click="closeModal()">Cancel</x-btn>
                    <x-btn type="button" variant="primary" x-show="status === 'idle' && files.length" x-cloak
                           @click="run()">
                        Read <span x-text="files.length"></span> image<span x-show="files.length !== 1">s</span>
                    </x-btn>
                    <x-btn type="button" variant="ghost" x-show="status === 'done'" x-cloak
                           @click="reset()">Start over</x-btn>
                    <x-btn type="button" variant="primary" x-show="status === 'done'" x-cloak
                           @click="useText()">Use this text</x-btn>
                </div>
            </div>
        </div>
    </template>
</div>
