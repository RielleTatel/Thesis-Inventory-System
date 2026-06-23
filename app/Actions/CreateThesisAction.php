<?php

namespace App\Actions;

use App\Actions\Concerns\HandlesApprovalPage;
use App\Actions\Concerns\SyncsOrderedThesisValues;
use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Support\Facades\DB;

/**
 * Create a thesis owned by a department, including its ordered multi-value rows
 * and an optional approval-page image.
 */
class CreateThesisAction
{
    use HandlesApprovalPage, SyncsOrderedThesisValues;

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
                'status' => $data['status'],
            ]);

            $this->syncOrderedValues($thesis, $data);
            $this->syncApprovalPage($thesis, $data);

            return $thesis;
        });
    }
}
