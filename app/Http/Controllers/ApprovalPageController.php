<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a thesis's approval/signature page image inline for the modal viewer.
 *
 * Public (no auth) by design: the approval page is shown to public viewers as
 * proof the thesis is approved, matching the previous shareable presigned-URL
 * behavior. Serves ONLY image bytes, resolved strictly from the thesis's own
 * stored path (never user input, so no path traversal). Anything missing or
 * non-image is a plain 404 that leaks no storage path or filesystem detail.
 */
class ApprovalPageController extends Controller
{
    /** Image mimetypes we will stream, mapped to a public-facing extension. */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __invoke(Thesis $thesis): StreamedResponse
    {
        // Resolve strictly from the thesis's own stored path — never user input.
        $path = $thesis->approvalPagePath();

        if ($path === null) {
            abort(404);
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(Thesis::APPROVAL_DISK);

        if (! $disk->exists($path)) {
            abort(404);
        }

        // Only ever stream real image bytes; an undetectable or non-image type
        // 404s rather than being served.
        $mime = $disk->mimeType($path);

        if ($mime === false || ! isset(self::ALLOWED_MIMES[$mime])) {
            abort(404);
        }

        // Inline display under a generic public name — never expose the stored
        // (hashed) filename or any other filesystem detail.
        return $disk->response($path, 'approval-page.'.self::ALLOWED_MIMES[$mime], [
            'Content-Type' => $mime,
        ]);
    }
}
