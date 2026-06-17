import cleanOcrText from './ocr-clean';

/**
 * Reusable OCR capture + review controller (SRS FR-5.3 / NFR-3.3).
 *
 * UPLOAD path only for now, and it accepts MULTIPLE images for one field (e.g.
 * an abstract photographed across two pages). Images are read in the order
 * listed; the cleaned text of each is combined into one editable review box.
 *
 * The camera and QR capture paths added later reuse this controller as-is: they
 * only need to produce image File/Blob(s) and call `addFiles()` — the OCR run
 * and the mandatory review step stay identical, so text is never auto-committed.
 *
 * Registered as an Alpine component: `Alpine.data('ocrScanner', ocrScanner)`.
 *
 * @param {string} targetId  id of the textarea this scanner fills on confirm.
 */
export default function ocrScanner(targetId) {
    return {
        targetId,
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
                // Lazy-load Tesseract.js so the ~MB OCR engine stays out of the
                // main bundle until the user actually scans something.
                const tesseract = await import('tesseract.js');
                const recognize = tesseract.recognize ?? tesseract.default?.recognize;

                const parts = [];
                for (let i = 0; i < this.files.length; i++) {
                    this.current = i + 1;
                    this.progress = 0;

                    const { data } = await recognize(this.files[i], 'eng', {
                        logger: (m) => {
                            if (m.status === 'recognizing text') {
                                this.progress = Math.round((m.progress || 0) * 100);
                            }
                        },
                    });

                    // Reflow each image's visual line breaks before combining.
                    parts.push(cleanOcrText(data?.text ?? ''));
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
         * Commit the reviewed text into the target field. Stays editable there
         * and saves through the normal form flow — nothing is auto-committed.
         */
        useText() {
            const field = document.getElementById(this.targetId);
            if (field) {
                field.value = this.text;
                // Let Alpine / validation bound to the field react to the change.
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.focus();
            }
            this.closeModal();
        },
    };
}
