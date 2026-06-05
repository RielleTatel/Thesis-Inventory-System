<?php

namespace App\Models;

use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['department_id', 'title', 'program', 'year', 'abstract', 'recommendations'])]
class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
    use HasFactory;

    // "thesis" → "theses"; Laravel would otherwise guess "thesis".
    protected $table = 'theses';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    /**
     * Owning department (FR-3.4/3.6).
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<ThesisAuthor, $this>
     */
    public function authors(): HasMany
    {
        return $this->hasMany(ThesisAuthor::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisAdviser, $this>
     */
    public function advisers(): HasMany
    {
        return $this->hasMany(ThesisAdviser::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisPanelist, $this>
     */
    public function panelists(): HasMany
    {
        return $this->hasMany(ThesisPanelist::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisKeyword, $this>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(ThesisKeyword::class)->orderBy('position');
    }
}
