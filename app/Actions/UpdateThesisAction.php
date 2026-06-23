<?php

namespace App\Actions;

use App\Actions\Concerns\HandlesApprovalPage;
use App\Actions\Concerns\SyncsOrderedThesisValues;
use App\Models\Thesis;
use Illuminate\Support\Facades\DB;

/**
 * Update a thesis's descriptive fields, its ordered multi-value rows, and its
 * approval-page image (replace or remove).
 */
class UpdateThesisAction
{
    use HandlesApprovalPage, SyncsOrderedThesisValues;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Thesis $thesis, array $data): Thesis
    {
        return DB::transaction(function () use ($thesis, $data): Thesis {
            $thesis->update([
                'title' => $data['title'],
                'year' => $data['year'],
                'program' => $data['program'],
                'abstract' => $data['abstract'],
                'recommendations' => $data['recommendations'] ?? null,
                'status' => $data['status'],
            ]);

            $this->syncOrderedValues($thesis, $data);
            $this->syncApprovalPage($thesis, $data);

            return $thesis;
        });
    }
}
