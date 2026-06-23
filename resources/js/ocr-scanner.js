import cleanOcrText from './ocr-clean';
import recognizeImage from './ocr-engine';
import parseApprovalPage from './parse-approval-page';
import preprocessImage from './preprocess-image';
import { titleCaseAuthor, titleCaseTitle } from './text-case';

/**
 * Reusable OCR capture + review controller (SRS FR-5.3 / NFR-3.3).
 *
 * UPLOAD path only for now, and it accepts MULTIPLE images (e.g. an abstract
 * photographed across two pages). Images are read in the order listed; the
 * cleaned text of each is combined into one editable review box.
 *
 * Two modes share this exact pipeline:
 *   - 'field'    — fills a single target textarea (abstract / recommendations).
 *   - 'approval' — parses a thesis approval/signature page and fills several
 *                  form fields at once via parseApprovalPage().
 * Either way the result is shown for review and never auto-committed.
 *
 * The camera and QR capture paths added later reuse this controller as-is: they
 * only need to produce image File/Blob(s) and call `addFiles()`.
 *
 * Registered as an Alpine component: `Alpine.data('ocrScanner', ocrScanner)`.
 *
 * @param {string} targetId  id of the textarea filled in 'field' mode.
 * @param {string} mode      'field' (default) or 'approval'.
 */
export default function ocrScanner(targetId, mode = 'field') {
    return {
        targetId,
        mode,
        open: false,
        dragging: false,
        status: 'idle', // idle | reading | done
        files: [], // chosen images, in the order they'll be read
        current: 0, // 1-based index of the image being read
        total: 0, // number of images in this run
        progress: 0, // current image's progress %
        text: '',
        error: '',

        openModal() {
            this.open = true;
            document.body.classList.add('overflow-y-hidden');
            // Move focus into the dialog for keyboard + screen-reader users.
            this.$nextTick(() => this.$refs.panel?.focus());
        },

        closeModal() {
            this.open = false;
            document.body.classList.remove('overflow-y-hidden');
            this.reset();
        },

        reset() {
            this.status = 'idle';
            this.files = [];
            this.current = 0;
            this.total = 0;
            this.progress = 0;
            this.text = '';
            this.error = '';
            this.dragging = false;
            if (this.$refs.file) this.$refs.file.value = '';
        },

        onPick(event) {
            this.addFiles(event.target.files);
        },

        onDrop(event) {
            this.dragging = false;
            this.addFiles(event.dataTransfer?.files);
        },

        /**
         * Accept images pasted from the clipboard (e.g. a screenshot) while the
         * modal is open, queuing them like uploaded files. Plain-text pastes are
         * left alone so editing the review box still works normally.
         */
        onPaste(event) {
            if (!this.open || this.status !== 'idle') return;

            const images = Array.from(event.clipboardData?.items || [])
                .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
                .map((item) => item.getAsFile())
                .filter(Boolean);

            if (images.length) {
                event.preventDefault();
                this.addFiles(images);
            }
        },

        /**
         * Queue image files for reading. Non-images are skipped with a notice.
         * Shared entry point for every capture path (upload now; camera/QR later).
         */
        addFiles(fileList) {
            const incoming = Array.from(fileList || []);
            const images = incoming.filter((file) => file.type.startsWith('image/'));

            this.error = images.length < incoming.length
                ? 'Some files were skipped — only images can be scanned.'
                : '';

            this.files = [...this.files, ...images];
            // Allow re-picking the same file later (resets the input's selection).
            if (this.$refs.file) this.$refs.file.value = '';
        },

        removeFile(index) {
            this.files.splice(index, 1);
        },

        /**
         * OCR every queued image in order, then combine the cleaned text of each
         * into the review box (one line break between images) for edit + confirm.
         */
        async run() {
            if (!this.files.length) return;

            this.error = '';
            this.status = 'reading';
            this.total = this.files.length;

            try {
                const parts = [];
                for (let i = 0; i < this.files.length; i++) {
                    this.current = i + 1;
                    this.progress = 0;

                    // Clean the image up (grayscale/contrast/threshold/upscale)
                    // before OCR; fall back to the raw file if that ever fails.
                    let source = this.files[i];
                    try {
                        source = await preprocessImage(source);
                    } catch {
                        source = this.files[i];
                    }

                    const text = await recognizeImage(source, (percent) => {
                        this.progress = percent;
                    });

                    // Reflow each image's visual line breaks before combining.
                    parts.push(cleanOcrText(text));
                }

                // Combine in order, one line break between images.
                this.text = parts.filter((part) => part.length).join('\n');
                this.status = 'done';
            } catch (e) {
                this.error = 'Could not read text from one of the images. Try clearer photos.';
                this.status = 'idle';
            }
        },

        /**
         * Commit the reviewed text into the target field ('field' mode). Stays
         * editable there and saves through the normal form flow — not committed.
         */
        useText() {
            this.setField(this.targetId, this.text, { focus: true });
            this.closeModal();
        },

        /**
         * Parse the reviewed approval-page text and populate the matching form
         * fields ('approval' mode). Unmatched fields are left untouched; every
         * filled value remains editable in the form for review before saving.
         */
        applyApproval() {
            const parsed = parseApprovalPage(this.text);

            // Title-case the scanned Title and Authors at fill-time; everything
            // stays editable in the form for review (FR-5.3).
            this.setField('title', titleCaseTitle(parsed.title));
            this.setField('program', parsed.program);
            this.fillList('authors', parsed.authors.map(titleCaseAuthor));
            this.fillList('advisers', parsed.adviser ? [parsed.adviser] : []);
            this.fillList('panelists', parsed.panelists);

            this.closeModal();
        },

        /** Set a plain input/textarea by id and notify Alpine/validation. */
        setField(id, value, { focus = false } = {}) {
            if (!id || !value) return;

            const field = document.getElementById(id);
            if (!field) return;

            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            if (focus) field.focus();
        },

        /** Fill a repeatable-list field (authors/advisers/panelists) by name. */
        fillList(name, values) {
            if (!values || !values.length) return;

            window.dispatchEvent(new CustomEvent('ocr-fill-list', {
                detail: { name, values: [...values] },
            }));
        },
    };
}
