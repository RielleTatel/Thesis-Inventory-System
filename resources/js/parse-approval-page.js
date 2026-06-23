import { findFuzzy } from './fuzzy-match';

/**
 * Parse the OCR text of a thesis approval / signature page into structured
 * fields — best-effort, Tesseract.js text only (no AI, no API, no network).
 *
 * Anchor phrases ("entitled", "submitted by", "in partial…", "for the degree
 * of") are located with FUZZY matching (Levenshtein edit distance) so OCR
 * garble like "tiled:" or "in paral lflment" still anchors correctly. Any field
 * whose anchor isn't found confidently is returned empty: this never guesses and
 * never throws. The caller fills the form with whatever was found and the user
 * reviews/corrects everything before saving (FR-5.3 — nothing is auto-committed).
 *
 * @param {string} text  combined OCR text of the approval page.
 * @returns {{title: string, authors: string[], program: string, adviser: string, panelists: string[]}}
 */
export default function parseApprovalPage(text) {
    const empty = { title: '', authors: [], program: '', adviser: '', panelists: [] };
    if (!text || typeof text !== 'string') return empty;

    const flat = text.replace(/\r\n?/g, '\n');

    // Title sits between "entitled[:]" and "submitted by". Prefer the quoted
    // span if the page wraps the title in quotes; otherwise take the whole region.
    const titleRegion = extractBetween(flat, 'entitled', 'submitted by');

    return {
        title: extractQuoted(titleRegion) || stripEdges(titleRegion),

        // Authors sit between "submitted by" and "in partial fulfil(l)ment",
        // joined by "and" / "&". Each name keeps its "Lastname, Firstname" form
        // and is trimmed to its name-shaped head (trailing OCR noise dropped).
        authors: splitNames(extractBetween(flat, 'submitted by', 'in partial')),

        // Program/degree follows "for the degree of" to the end of that line.
        program: extractProgram(flat),

        // Adviser + panelists are read from the signature blocks (see below).
        ...extractRoles(flat),
    };
}

/**
 * Return the text between the first fuzzy match of `startNeedle` and the next
 * fuzzy match of `endNeedle` after it, with internal whitespace/newlines
 * collapsed to spaces. Empty string if the start anchor isn't found.
 */
function extractBetween(text, startNeedle, endNeedle) {
    const start = findFuzzy(text, startNeedle);
    if (!start) return '';

    const after = text.slice(start.end);
    const end = findFuzzy(after, endNeedle);
    const segment = end ? after.slice(0, end.index) : after;

    return collapse(segment);
}

/**
 * If `region` contains a quoted span (straight or curly quotes), return its
 * contents; otherwise return '' so the caller falls back to the whole region.
 */
function extractQuoted(region) {
    if (!region) return '';

    const match = region.match(/[“”"'‘’]([^“”"'‘’]+)[“”"'‘’]/);
    return match ? collapse(match[1]) : '';
}

/**
 * Trim leading separator noise (a stray ":", "-", quote, etc. left by the anchor)
 * and trailing punctuation from an unquoted title region.
 */
function stripEdges(value) {
    return value.replace(/^[^A-Za-z0-9]+/, '').replace(/[\s:;,.\-]+$/, '');
}

/** Collapse all runs of whitespace (incl. newlines) into single spaces, trimmed. */
function collapse(value) {
    return value.replace(/\s+/g, ' ').trim();
}

/**
 * Split a "A and B & C" author segment into separate names, each trimmed to its
 * name-shaped head. Order and "Lastname, Firstname" form are preserved.
 */
function splitNames(segment) {
    if (!segment) return [];

    return segment
        .split(/\s+and\s+|\s*&\s*/i)
        .map(trimToName)
        .filter((name) => name.length > 1);
}

/**
 * Keep the leading run of name-shaped characters (letters, spaces, and the
 * comma/period/hyphen/apostrophe that appear in real names), dropping any
 * trailing OCR noise such as digits or stray symbols.
 */
function trimToName(raw) {
    const match = collapse(raw).match(/[A-Za-z][A-Za-z.,'’\- ]*/);
    if (!match) return '';

    return match[0].replace(/[\s.,]+$/, '').trim();
}

/** Degree text after "for the degree of", up to the end of that line. */
function extractProgram(text) {
    const anchor = findFuzzy(text, 'for the degree of');
    if (!anchor) return '';

    // Take the remainder of the anchor's line, dropping trailing punctuation.
    const line = text.slice(anchor.end).split('\n')[0];
    return collapse(line).replace(/[.\s]+$/, '');
}

/**
 * Read the adviser and panelists from the signature area. Approval pages list a
 * name with its role label below it ("Adviser", "Panelist"), often surrounded by
 * signature lines and laid out in two columns — so this is intentionally lenient:
 * for each role label, it takes the nearest plausible name line directly above,
 * skipping blank/garbage (underscores, dates, other labels).
 */
function extractRoles(text) {
    const lines = text.split('\n').map((line) => line.trim());
    let adviser = '';
    const panelists = [];

    for (let i = 0; i < lines.length; i++) {
        const isAdviser = /^(thesis\s+)?advise?r\b/i.test(lines[i]);
        const isPanelist = /^panel(ist|\s*member)?\b/i.test(lines[i]);
        if (!isAdviser && !isPanelist) continue;

        const name = nameAbove(lines, i);
        if (!name) continue;

        if (isAdviser && !adviser) {
            adviser = name;
        } else if (isPanelist) {
            panelists.push(name);
        }
    }

    return { adviser, panelists };
}

/**
 * Nearest line above index `i` that looks like a person's name, skipping blanks
 * and signature garbage. Bounded so it can't reach back into unrelated text, and
 * stops if it hits another role label.
 */
function nameAbove(lines, i) {
    let scanned = 0;

    for (let j = i - 1; j >= 0 && scanned < 4; j--) {
        const line = lines[j];
        if (line === '') continue;

        scanned++;
        if (/^(thesis\s+)?advise?r\b|^panel(ist|\s*member)?\b/i.test(line)) break;
        if (looksLikeName(line)) return line;
    }

    return '';
}

/** Heuristic: a real name line has enough letters and isn't a label/date/rule. */
function looksLikeName(line) {
    const letters = (line.match(/[A-Za-z]/g) || []).length;

    if (letters < 3) return false; // underscores, dashes, page numbers
    if (letters / line.length < 0.5) return false; // mostly punctuation = signature rule
    if (/^\d/.test(line)) return false; // starts with a number (dates, etc.)
    if (/(advise?r|panel|member|signature|date|approved|noted|chair|dean)/i.test(line)) return false;

    return true;
}
