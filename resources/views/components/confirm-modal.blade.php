{{--
    Reusable confirmation modal for destructive actions (replaces native confirm()).
    Rendered once in the app shell. Open it from any button with:

        @click="$dispatch('confirm', {
            action: '{{ route('…') }}', method: 'DELETE',
            title: 'Delete this thesis?', message: 'This can't be undone.',
            confirmLabel: 'Delete',
        })"

    It submits a CSRF-protected form to the given action only when confirmed.
    Tokens only — no hardcoded hex (coding standard #8).
--}}
<div x-data="confirmModal()"
     x-cloak
     @confirm.window="open($event.detail)"
     x-show="show"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 grid place-items-center p-4"
     style="display: none;">
    {{-- Overlay --}}
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-text/50" @click="close()"></div>

    {{-- Panel --}}
    <div x-show="show"
         x-transition
         role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title"
         class="relative w-full max-w-md rounded-lg bg-surface p-6 shadow-xl">
        <h3 id="confirm-modal-title" class="text-lg font-semibold text-navy" x-text="title"></h3>
        <p class="mt-2 text-sm leading-relaxed text-text/70" x-text="message"></p>

        <form :action="action" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            <input type="hidden" name="_method" :value="method">
            <x-btn type="button" variant="ghost" @click="close()">Cancel</x-btn>
            <x-btn type="submit" variant="danger" x-ref="confirmBtn" x-text="confirmLabel">Confirm</x-btn>
        </form>
    </div>
</div>
