/**
 * Tiny offline fuzzy substring matcher (Levenshtein edit distance), used by the
 * approval-page parser to locate template anchor phrases — "entitled",
 * "submitted by", "in partial…" — even when OCR garbles them ("tiled:",
 * "in paral lflment"). No AI, no API; pure string maths.
 *
 * Matching is case- AND whitespace-insensitive: spaces are stripped before
 * scoring (so OCR's spurious gaps like "in paral lflment" still line up), but
 * the returned positions map back to the ORIGINAL text so callers can slice it.
 */

/**
 * Levenshtein edit distance between two strings (insert/delete/substitute = 1).
 * Two-row dynamic programming, O(a·b) time, O(min) space.
 *
 * @param {string} a
 * @param {string} b
 * @returns {number} number of single-character edits to turn `a` into `b`.
 */
export function levenshtein(a, b) {
    if (a === b) return 0;
    if (!a.length) return b.length;
    if (!b.length) return a.length;

    let prev = Array.from({ length: b.length + 1 }, (_, i) => i);
    let curr = new Array(b.length + 1);

    for (let i = 1; i <= a.length; i++) {
        curr[0] = i;
        for (let j = 1; j <= b.length; j++) {
            const cost = a[i - 1] === b[j - 1] ? 0 : 1;
            curr[j] = Math.min(
                prev[j] + 1, // deletion
                curr[j - 1] + 1, // insertion
                prev[j - 1] + cost, // match / substitution
            );
        }
        [prev, curr] = [curr, prev];
    }

    return prev[b.length];
}

/**
 * Find the best fuzzy occurrence of `needle` inside `haystack`.
 *
 * Uses approximate substring matching (Sellers' variant of Levenshtein, where a
 * match may begin anywhere for free) over a whitespace-stripped, lower-cased
 * copy of the text, then maps the result back to original indices. Returns the
 * match only if its per-character error stays within `maxErrorRatio`.
 *
 * @param {string} haystack  text to search (e.g. the OCR'd page).
 * @param {string} needle    anchor phrase to look for.
 * @param {object} [options]
 * @param {number} [options.maxErrorRatio=0.4]  max edits ÷ needle length to accept.
 * @returns {{index: number, end: number, score: number}|null}
 *          original-text start index, end index (exclusive), and normalized score,
 *          or null when nothing matches confidently.
 */
export function findFuzzy(haystack, needle, { maxErrorRatio = 0.4 } = {}) {
    if (!haystack || !needle) return null;

    // Compact = haystack minus whitespace, lower-cased; map[k] is the original
    // index of compact[k] so we can translate positions back afterwards.
    const compact = [];
    const map = [];
    for (let i = 0; i < haystack.length; i++) {
        if (/\s/.test(haystack[i])) continue;
        compact.push(haystack[i].toLowerCase());
        map.push(i);
    }

    const pattern = needle.toLowerCase().replace(/\s+/g, '');
    const n = compact.length;
    const m = pattern.length;
    if (n === 0 || m === 0) return null;

    // dp[i][j] = min edits to match pattern[0..i) against a substring of compact
    // ending at j. Row 0 is all-zero: a match may start at any column for free.
    const dp = Array.from({ length: m + 1 }, () => new Array(n + 1).fill(0));
    for (let i = 1; i <= m; i++) dp[i][0] = i;

    for (let i = 1; i <= m; i++) {
        for (let j = 1; j <= n; j++) {
            const cost = pattern[i - 1] === compact[j - 1] ? 0 : 1;
            dp[i][j] = Math.min(
                dp[i - 1][j] + 1, // skip a pattern char
                dp[i][j - 1] + 1, // skip a haystack char
                dp[i - 1][j - 1] + cost, // match / substitute
            );
        }
    }

    // Best match = the end column with the smallest distance on the last row.
    let bestEnd = 1;
    let bestScore = dp[m][1];
    for (let j = 2; j <= n; j++) {
        if (dp[m][j] < bestScore) {
            bestScore = dp[m][j];
            bestEnd = j;
        }
    }

    if (bestScore / m > maxErrorRatio) return null;

    // Backtrack from (m, bestEnd) to row 0 to recover where the match started.
    let i = m;
    let j = bestEnd;
    while (i > 0) {
        if (dp[i][j] === dp[i - 1][j] + 1) {
            i--;
        } else if (j > 0 && dp[i][j] === dp[i][j - 1] + 1) {
            j--;
        } else {
            i--;
            j--;
        }
    }

    const startCompact = j; // first matched compact char
    const endCompact = bestEnd - 1; // last matched compact char
    if (endCompact < startCompact) return null;

    return {
        index: map[startCompact],
        end: map[endCompact] + 1,
        score: bestScore / m,
    };
}
