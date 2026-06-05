<?php

namespace App\Actions;

use App\Actions\Concerns\SyncsOrderedThesisValues;
use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Support\Facades\DB;

/**
 * Create a thesis owned by a department, including its ordered multi-value rows.
 */
class CreateThesisAction
{
    use SyncsOrderedThesisValues;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Department $department, array $data): Thesis
    {
        return DB::transaction(function () use ($department, $data): Thesis {
            $thesis = $department->theses()->create([
                'title' => $data['title'],
                'year' => $data['year'],
                'program' => $data['program'],
                'abstract' => $data['abstract'],
                'recommendations' => $data['recommendations'] ?? null,
            ]);

            $this->syncOrderedValues($thesis, $data);

            return $thesis;
        });
    }
}
