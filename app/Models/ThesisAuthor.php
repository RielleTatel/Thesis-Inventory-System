<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'position'])]
class ThesisAuthor extends Model
{
    /**
     * @return BelongsTo<Thesis, $this>
     */
    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }
}
