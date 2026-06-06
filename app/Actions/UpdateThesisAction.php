<?php

namespace App\Actions;

use App\Actions\Concerns\SyncsOrderedThesisValues;
use App\Models\Thesis;
use Illuminate\Support\Facades\DB;

/**
 * Update a thesis's descriptive fields and its ordered multi-value rows.
 */
class UpdateThesisAction
{
    use SyncsOrderedThesisValues;

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

            return $thesis;
        });
    }
}
