{{-- OCR scan trigger (FR-5.x). Opens the OCR upload + review modal for its field.
     Presentational only — the parent <x-ocr-scanner> owns the Alpine state and
     wires @click. Output is always reviewed before save, never auto-committed. --}}
@props(['label' => 'Scan from printed copy'])

<button {{ $attributes->merge([
            'type' => 'button',
            'title' => 'Scan from a printed copy — upload an image to detect its text',
            'class' => 'inline-flex items-center gap-1.5 rounded-md border border-text/15 px-2.5 py-1.5 '
                .'text-xs font-semibold text-navy transition hover:bg-input '
                .'focus:outline-none focus:ring-2 focus:ring-cyan',
        ]) }}>
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
        <circle cx="12" cy="13" r="4"/>
    </svg>
    {{ $label }}
</button>
