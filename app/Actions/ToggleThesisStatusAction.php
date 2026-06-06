<?php

namespace App\Actions;

use App\Models\Thesis;

/**
 * Flip a thesis between draft and published without re-saving the whole form.
 * Used by the toggle button on the department thesis list.
 */
class ToggleThesisStatusAction
{
    public function execute(Thesis $thesis): Thesis
    {
        $thesis->update([
            'status' => $thesis->status === 'draft' ? 'published' : 'draft',
        ]);

        return $thesis;
    }
}
