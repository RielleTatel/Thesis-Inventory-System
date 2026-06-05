<?php

namespace App\Policies;

use App\Models\Thesis;
use App\Models\User;

/**
 * The department-can-only-touch-its-own-records boundary (FR-3.4/3.6, NFR-1.3).
 *
 * This is the single tested place that rule lives, so a forgotten endpoint
 * can't leak another department's data (coding standard #4 & #9).
 */
class ThesisPolicy
{
    /**
     * Any department user may create records (the route is role-gated; the new
     * record is bound to their own department by the controller/Action).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('department');
    }

    public function view(User $user, Thesis $thesis): bool
    {
        return $this->owns($user, $thesis);
    }

    public function update(User $user, Thesis $thesis): bool
    {
        return $this->owns($user, $thesis);
    }

    public function delete(User $user, Thesis $thesis): bool
    {
        return $this->owns($user, $thesis);
    }

    /**
     * True only when the thesis belongs to the user's own department.
     */
    private function owns(User $user, Thesis $thesis): bool
    {
        return $user->department_id !== null
            && $user->department_id === $thesis->department_id;
    }
}
