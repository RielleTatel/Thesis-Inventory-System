<?php

namespace App\Actions;

use App\Actions\Concerns\HandlesApprovalPage;
use App\Models\Thesis;

/**
 * Delete a thesis. Its ordered multi-value rows are removed by the
 * cascading foreign keys on thesis_authors/advisers/panelists/keywords, and
 * its approval-page image is removed from s3 so nothing is orphaned.
 */
class DeleteThesisAction
{
    use HandlesApprovalPage;

    public function execute(Thesis $thesis): void
    {
        $path = $thesis->approval_page_path;

        $thesis->delete();

        $this->deleteApprovalPageFile($path);
    }
}
