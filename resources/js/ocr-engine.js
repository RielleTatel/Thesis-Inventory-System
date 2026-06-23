/**
 * Thin wrapper around Tesseract.js that runs OCR with the higher-accuracy
 * English model. Everything stays in the browser — Tesseract.js + WASM — and
 * never touches an AI service or recognition API.
 *
 * Tesseract.js's simple `recognize()` already runs LSTM-only and pulls the
 * integer "best" model by default; here we point it at the FULL "best" English
 * model (more accurate, slightly larger) and quietly fall back to the library
 * default if that model can't be fetched (offline cache miss, blocked CDN…).
 * The fallback is remembered for the rest of the session so we don't re-attempt
 * a download we already know will fail.
 *
 * The engine itself (~MB of WASM) is import()'d lazily on first use so it stays
 * out of the main bundle until the user actually scans something.
 */

// Full-precision "best" English traineddata — same model host Tesseract.js uses
// by default, just the higher-accuracy variant. Not an API/AI endpoint.
const BEST_MODEL_PATH = 'https://cdn.jsdelivr.net/npm/@tesseract.js-data/eng/4.0.0_best';

let useBestModel = true;

/**
 * Recognize the text in one image with the best available English model.
 *
 * @param {Blob|File|HTMLCanvasElement|HTMLImageElement|string} image  source to OCR.
 * @param {(percent: number) => void} [onProgress]  recognition progress, 0–100.
 * @returns {Promise<string>} the recognized text (empty string if none).
 */
export default async function recognizeImage(image, onProgress) {
    const tesseract = await import('tesseract.js');
    const recognize = tesseract.recognize ?? tesseract.default?.recognize;

    const logger = (message) => {
        if (message.status === 'recognizing text') {
            onProgress?.(Math.round((message.progress || 0) * 100));
        }
    };

    if (useBestModel) {
        try {
            const { data } = await recognize(image, 'eng', { logger, langPath: BEST_MODEL_PATH });
            return data?.text ?? '';
        } catch {
            // Best model unavailable — stop trying it and use the library default.
            useBestModel = false;
        }
    }

    const { data } = await recognize(image, 'eng', { logger });
    return data?.text ?? '';
}
