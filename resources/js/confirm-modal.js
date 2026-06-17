/**
 * Reusable confirmation modal controller (replaces native confirm()).
 *
 * One instance lives in the app shell. Any destructive action opens it by
 * dispatching a `confirm` window event with a payload, e.g.:
 *
 *   $dispatch('confirm', {
 *       action: '/theses/1',          // form target
 *       method: 'DELETE',             // spoofed HTTP method
 *       title: 'Delete thesis record',
 *       message: 'This can't be undone.',
 *       confirmLabel: 'Delete record',
 *   })
 *
 * On confirm it submits a CSRF-protected form to `action`; nothing happens
 * unless the user confirms.
 *
 * Registered as an Alpine component: `Alpine.data('confirmModal', confirmModal)`.
 */
export default function confirmModal() {
    return {
        show: false,
        action: '',
        method: 'DELETE',
        title: 'Are you sure?',
        message: '',
        confirmLabel: 'Confirm',

        open(detail = {}) {
            this.action = detail.action ?? '';
            this.method = detail.method ?? 'DELETE';
            this.title = detail.title ?? 'Are you sure?';
            this.message = detail.message ?? '';
            this.confirmLabel = detail.confirmLabel ?? 'Confirm';
            this.show = true;
            document.body.classList.add('overflow-y-hidden');
            // Focus the confirm button for keyboard + screen-reader users.
            this.$nextTick(() => this.$refs.confirmBtn?.focus());
        },

        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        },
    };
}
