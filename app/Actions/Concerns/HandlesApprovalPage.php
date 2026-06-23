<?php

namespace App\Actions\Concerns;

use App\Models\Thesis;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Shared logic for the thesis approval/signature page image on the s3 disk.
 *
 * The approval page is the single allowed exception to the metadata-only rule
 * (FR-4.4) — keeping it in one place ensures old files are always deleted so the
 * bucket never accumulates orphans. The stored path uses a generated filename
 * (UploadedFile::store), never the user's original name.
 */
trait HandlesApprovalPage
{
    /**
     * Apply an approval-page change from validated form data: replace the image
     * with a newly uploaded one, or remove the current one. Either way the
     * previous file (if any) is deleted from s3. No-op when neither is requested.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncApprovalPage(Thesis $thesis, array $data): void
    {
        $file = $data['approval_page'] ?? null;

        if ($file instanceof UploadedFile) {
            $path = $file->store('approval_pages', 's3');

            // A false/empty return means the s3 write failed (bad creds, network,
            // bucket). Never persist a falsy path like "0": surface the failure
            // and leave any existing image untouched. This is an infrastructure
            // error, not input validation — ValidationException is just the
            // cleanest way to show it on the form field.
            if (! $path) {
                throw ValidationException::withMessages([
                    'approval_page' => 'The approval page could not be uploaded. Please check your connection and try again.',
                ]);
            }

            // The new file is safely stored — only now drop the previous one.
            $this->deleteApprovalPageFile($thesis->approval_page_path);
            $thesis->approval_page_path = $path;
            $thesis->save();

            return;
        }

        if (! empty($data['remove_approval_page']) && $thesis->hasApprovalPage()) {
            $this->deleteApprovalPageFile($thesis->approval_page_path);
            $thesis->approval_page_path = null;
            $thesis->save();
        }
    }

    /**
     * Delete an approval-page file from the s3 disk, if a path is given.
     */
    protected function deleteApprovalPageFile(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('s3')->delete($path);
        }
    }
}
