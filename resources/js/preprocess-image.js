/**
 * Canvas-based image cleanup run BEFORE handing an image to Tesseract.js, to
 * lift OCR accuracy on photos/scans of printed approval pages. Entirely offline
 * (one <canvas>, no AI, no API, no network).
 *
 * Pipeline, in order:
 *   1. Upscale small/low-res captures toward a ~300 DPI equivalent (Tesseract
 *      reads cleanest when capital letters are ~30px tall). Large images are
 *      left as-is, and the scale is capped so we never blow up memory.
 *   2. Grayscale (luminance) — colour carries no signal for printed text.
 *   3. Boost contrast around mid-grey.
 *   4. Threshold to black-and-white with Otsu's method (a global threshold
 *      chosen from the image's own histogram), which removes paper tint and
 *      uneven exposure that confuse the recognizer.
 *
 * Pure-ish: it reads a File/Blob and returns a fresh canvas, touching no app
 * state. Tesseract.recognize() accepts an HTMLCanvasElement directly. If
 * anything fails the caller falls back to the original file, so OCR still runs.
 *
 * @param {Blob|File} file       the image to clean up.
 * @param {object}   [options]
 * @param {number}   [options.targetMinDim=1500]  upscale until the shorter side reaches this.
 * @param {number}   [options.maxScale=3]         never enlarge by more than this factor.
 * @param {number}   [options.maxDim=3500]        cap the longer side after scaling.
 * @param {number}   [options.contrast=1.4]       contrast multiplier around mid-grey.
 * @returns {Promise<HTMLCanvasElement>} a cleaned canvas ready for OCR.
 */
export default async function preprocessImage(file, options = {}) {
    const {
        targetMinDim = 1500,
        maxScale = 3,
        maxDim = 3500,
        contrast = 1.4,
    } = options;

    const bitmap = await createImageBitmap(file);

    try {
        const { width, height } = scaledSize(bitmap.width, bitmap.height, { targetMinDim, maxScale, maxDim });

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        // High-quality interpolation when upscaling small captures.
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(bitmap, 0, 0, width, height);

        binarize(ctx, width, height, contrast);

        return canvas;
    } finally {
        bitmap.close?.();
    }
}

/**
 * Compute the output dimensions: upscale only (never shrink) until the shorter
 * side reaches `targetMinDim`, capped by `maxScale` and an absolute `maxDim` on
 * the longer side so memory stays bounded.
 */
function scaledSize(width, height, { targetMinDim, maxScale, maxDim }) {
    const shortSide = Math.min(width, height);

    let scale = shortSide < targetMinDim ? targetMinDim / shortSide : 1;
    scale = Math.min(scale, maxScale);

    const longSide = Math.max(width, height) * scale;
    if (longSide > maxDim) scale *= maxDim / longSide;

    return {
        width: Math.max(1, Math.round(width * scale)),
        height: Math.max(1, Math.round(height * scale)),
    };
}

/**
 * Grayscale + contrast-boost the canvas in place, then threshold it to pure
 * black-and-white using a global Otsu threshold derived from the histogram.
 */
function binarize(ctx, width, height, contrast) {
    const image = ctx.getImageData(0, 0, width, height);
    const data = image.data;

    const gray = new Uint8ClampedArray(data.length / 4);
    const histogram = new Array(256).fill(0);

    // Pass 1 — luminance grayscale + contrast around mid-grey, building a histogram.
    for (let i = 0, p = 0; i < data.length; i += 4, p++) {
        const luma = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        const boosted = (luma - 128) * contrast + 128;
        const value = boosted < 0 ? 0 : boosted > 255 ? 255 : boosted | 0;

        gray[p] = value;
        histogram[value]++;
    }

    const threshold = otsuThreshold(histogram, gray.length);

    // Pass 2 — threshold to black or white, writing back over the RGB channels.
    for (let i = 0, p = 0; i < data.length; i += 4, p++) {
        const value = gray[p] >= threshold ? 255 : 0;
        data[i] = data[i + 1] = data[i + 2] = value;
        // Leave alpha (data[i + 3]) untouched.
    }

    ctx.putImageData(image, 0, 0);
}

/**
 * Otsu's method: pick the grayscale threshold that maximizes between-class
 * variance (the split that best separates ink from paper) from a 256-bin
 * histogram. Returns a value in [0, 255].
 */
function otsuThreshold(histogram, total) {
    let sumAll = 0;
    for (let t = 0; t < 256; t++) sumAll += t * histogram[t];

    let sumBackground = 0;
    let weightBackground = 0;
    let maxVariance = 0;
    let threshold = 127;

    for (let t = 0; t < 256; t++) {
        weightBackground += histogram[t];
        if (weightBackground === 0) continue;

        const weightForeground = total - weightBackground;
        if (weightForeground === 0) break;

        sumBackground += t * histogram[t];
        const meanBackground = sumBackground / weightBackground;
        const meanForeground = (sumAll - sumBackground) / weightForeground;

        const between = weightBackground * weightForeground
            * (meanBackground - meanForeground) * (meanBackground - meanForeground);

        if (between > maxVariance) {
            maxVariance = between;
            threshold = t;
        }
    }

    return threshold;
}
