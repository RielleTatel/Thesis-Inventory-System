/**
 * Reflow raw OCR text into sentences/paragraphs (SRS FR-5.x).
 *
 * Tesseract preserves the printed page's visual line breaks, so a wrapped
 * sentence comes back with hard newlines mid-sentence. This normalizes that:
 *   - rejoins words hyphenated across a line break ("informa-\ntion" -> "information"),
 *   - keeps blank lines as paragraph breaks,
 *   - within a paragraph, joins line-wrap newlines into single spaces and
 *     collapses runs of whitespace.
 *
 * Pure and side-effect free so every capture path (upload now; camera/QR later)
 * can reuse it. The result still goes into the editable review box — it is a
 * formatting aid, never an auto-commit.
 *
 * @param {string} raw  Tesseract's `data.text`.
 * @returns {string}    reflowed text.
 */
export default function cleanOcrText(raw) {
    if (!raw) return '';

    return raw
        // Normalize line endings.
        .replace(/\r\n?/g, '\n')
        // Split into paragraphs on one or more blank lines.
        .split(/\n[ \t]*\n+/)
        .map((paragraph) =>
            paragraph
                // Rejoin words hyphenated across a line break.
                .replace(/(\w)-\n(\w)/g, '$1$2')
                // Join the remaining line-wrap newlines into single spaces.
                .replace(/\n+/g, ' ')
                // Collapse runs of spaces/tabs.
                .replace(/[ \t]+/g, ' ')
                .trim()
        )
        .filter((paragraph) => paragraph.length > 0)
        // Re-join paragraphs with a blank line between them.
        .join('\n\n');
}
