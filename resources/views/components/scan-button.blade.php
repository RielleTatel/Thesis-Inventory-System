{{-- OCR capture placeholder (FR-5.x). UI only — no Tesseract wiring yet.
     Output must always be reviewable before save, so this stays a no-op for now. --}}
<button type="button" disabled
        title="Scan from a printed copy — OCR capture coming soon"
        class="inline-flex items-center gap-1.5 rounded-md border border-text/10 px-2.5 py-1.5 text-xs font-semibold text-text/60 cursor-not-allowed">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
        <circle cx="12" cy="13" r="4"/>
    </svg>
    Scan from printed copy
</button>
