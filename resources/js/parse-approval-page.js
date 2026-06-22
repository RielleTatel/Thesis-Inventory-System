/**
 * Parse the OCR text of a thesis approval / signature page into structured
 * fields — best-effort, Tesseract.js text only (no AI, no API, no network).
 *
 * Anchor phrases are matched case-insensitively. Any field whose anchor isn't
 * found is returned empty: this never guesses and never throws. The caller fills
 * the form with whatever was found and the user reviews/corrects everything
 * before saving (FR-5.3 — nothing is auto-committed).
 *
 * @param {string} text  combined OCR text of the approval page.
 * @returns {{title: string, authors: string[], program: string, adviser: string, panelists: string[]}}
 */
export default function parseApprovalPage(text) {
    const empty = { title: '', authors: [], program: '', adviser: '', panelists: [] };
    if (!text || typeof text !== 'string') return empty;

    const flat = text.replace(/\r\n?/g, '\n');

    return {
        // Title sits between "entitled[:]" and "submitted by".
        title: extractBetween(flat, /entitled:?/i, /submitted by/i),

        // Authors sit between "submitted by" and "in partial fulfil(l)ment",
        // joined by "and" / "&". Each name keeps its "Lastname, Firstname" form.
        authors: splitNames(extractBetween(flat, /submitted by/i, /in partial fulfill?ment/i)),

        // Program/degree follows "for the degree of" to the end of that line.
        program: extractProgram(flat),

        // Adviser + panelists are read from the signature blocks (see below).
        ...extractRoles(flat),
    };
}

/**
 * Return the text between the first match of `startRe` and the next match of
 * `endRe` after it, with internal whitespace/newlines collapsed to spaces.
 * Empty string if the start anchor is missing.
 */
function extractBetween(text, startRe, endRe) {
    const start = text.match(startRe);
    if (!start) return '';

    const after = text.slice(start.index + start[0].length);
    const end = after.match(endRe);
    const segment = end ? after.slice(0, end.index) : after;

    return collapse(segment);
}

/** Collapse all runs of whitespace (incl. newlines) into single spaces, trimmed. */
function collapse(value) {
    return value.replace(/\s+/g, ' ').trim();
}

/**
 * Split a "A and B & C" author segment into separate names. Each name is kept
 * verbatim (e.g. "Dela Cruz, Juan") — order and "Lastname, Firstname" preserved.
 */
function splitNames(segment) {
    if (!segment) return [];

    return segment
        .split(/\s+and\s+|\s*&\s*/i)
        .map((name) => collapse(name))
        .filter((name) => name.length > 1);
}

/** Degree text after "for the degree of", up to the end of the line. */
function extractProgram(text) {
    const match = text.match(/for the degree of\s+([^\n]+)/i);
    if (!match) return '';

    // Keep the line; drop a trailing period and surrounding noise.
    return collapse(match[1]).replace(/[.\s]+$/, '');
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
