/**
 * Title-case normalization for SCANNED Title and Author values, applied at
 * fill-time just before the OCR'd values are written into the form. The user
 * still reviews and edits everything before saving (FR-5.3) — this is only a
 * formatting aid, never an auto-commit.
 *
 * Pure: string in, string out, no DOM/network/side effects. Two entry points
 * (title vs. author) share one capitalization core so the rule lives in a single
 * place and is never duplicated. They stay separate because their rules differ:
 * a title lowercases minor words mid-string, but an author's last name is always
 * fully capitalized (e.g. "DE LA CRUZ, Juan" -> "De La Cruz, Juan").
 */

// Minor words that stay lowercase inside a title — articles, conjunctions, and
// short prepositions — but are always capitalized when they're the first word.
// "de"/"del"/"la" are included for Spanish/Filipino place and institution names.
const MINOR_WORDS = new Set([
    'de', 'del', 'la', 'of', 'the', 'and', 'or',
    'in', 'on', 'at', 'to', 'for', 'a', 'an', 'by',
]);

/**
 * Capitalize one word — first letter up, the rest down — applied to each part of
 * a hyphenated word ("WEB-BASED" -> "Web-Based", "CAGA-ANAN" -> "Caga-Anan").
 */
function capitalizeWord(word) {
    return word
        .split('-')
        .map((part) => (part ? part[0].toUpperCase() + part.slice(1).toLowerCase() : part))
        .join('-');
}

/** Capitalize every whitespace-separated word (no minor-word exceptions). */
function capitalizeWords(text) {
    return text.trim().split(/\s+/).map(capitalizeWord).join(' ');
}

/**
 * Title-case a thesis title: every word is capitalized except minor words, which
 * stay lowercase unless first. Each part of a hyphenated word is capitalized.
 * Empty/whitespace-only input is returned unchanged.
 *
 * @param {string} value  scanned title.
 * @returns {string}      normalized title.
 */
export function titleCaseTitle(value) {
    if (!value || !value.trim()) return value;

    return value
        .trim()
        .split(/\s+/)
        .map((word, index) => {
            // Minor words stay lowercase mid-title; the first word never does.
            const lower = word.toLowerCase();
            if (index > 0 && MINOR_WORDS.has(lower)) return lower;

            return capitalizeWord(word);
        })
        .join(' ');
}

/**
 * Title-case the last name of a "Lastname, Given Names" author, capitalizing each
 * part of a hyphenated last name. The comma and given names are kept exactly as
 * scanned, so single-letter middle initials (e.g. "M.") stay uppercase. With no
 * comma the whole value is treated as a name. Empty/whitespace-only input is
 * returned unchanged.
 *
 * @param {string} value  scanned author name.
 * @returns {string}      author with a normalized last name.
 */
export function titleCaseAuthor(value) {
    if (!value || !value.trim()) return value;

    const comma = value.indexOf(',');
    if (comma === -1) return capitalizeWords(value);

    // Re-case only the last name; keep the comma + given names verbatim.
    return capitalizeWords(value.slice(0, comma)) + value.slice(comma);
}
