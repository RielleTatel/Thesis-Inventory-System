<?php

namespace App\Actions\Concerns;

use App\Models\Thesis;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Shared logic for the thesis approval/signature page image on the private
 * 'local' disk.
 *
 * The approval page is the single allowed exception to the metadata-only rule
 * (FR-4.4) — keeping it in one place ensures old files are always deleted so the
 * disk never accumulates orphans. Disk + directory come from Thesis::APPROVAL_*
 * so this and the seeder can't drift. The stored path uses a generated filename
 * (UploadedFile::store), never the user's original name.
 */
trait HandlesApprovalPage
{
    /**
     * Apply an approval-page change from validated form data: replace the image
     * with a newly uploaded one, or remove the current one. Either way the
     * previous file (if any) is deleted from disk. No-op when neither is requested.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncApprovalPage(Thesis $thesis, array $data): void
    {
        $file = $data['approval_page'] ?? null;

        if ($file instanceof UploadedFile) {
            $path = $file->store(Thesis::APPROVAL_DIR, Thesis::APPROVAL_DISK);

            // A false/empty return means the disk write failed. Never persist a
            // falsy path like "0": surface the failure and leave any existing
            // image untouched. This is an infrastructure error, not input
            // validation — ValidationException is just the cleanest way to show
            // it on the form field.
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
     * Delete an approval-page file from the local disk, if a path is given.
     */
    protected function deleteApprovalPageFile(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk(Thesis::APPROVAL_DISK)->delete($path);
        }
    }
}
