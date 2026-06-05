<?php

namespace App\Actions;

use App\Models\Thesis;

/**
 * Delete a thesis. Its ordered multi-value rows are removed by the
 * cascading foreign keys on thesis_authors/advisers/panelists/keywords.
 */
class DeleteThesisAction
{
    public function execute(Thesis $thesis): void
    {
        $thesis->delete();
    }
}
