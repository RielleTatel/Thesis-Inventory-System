<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code'])]
class Department extends Model
{
    /**
     * Thesis records owned by this department (FR-3.4 scoping anchor).
     *
     * @return HasMany<Thesis, $this>
     */
    public function theses(): HasMany
    {
        return $this->hasMany(Thesis::class);
    }

    /**
     * Department-role user accounts attached to this department.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
