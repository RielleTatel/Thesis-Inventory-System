<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Single home for activity-log query/filter logic (coding standard #5).
 */
class ActivityLogRepository
{
    /**
     * Filtered, paginated activity feed (newest first).
     *
     * @param  array<string, mixed>  $filters  q, action, from, to
     * @return LengthAwarePaginator<int, Activity>
     */
    public function filter(array $filters = []): LengthAwarePaginator
    {
        $query = Activity::query()->with('causer')->latest();

        if (! empty($filters['action'])) {
            $query->where('event', $filters['action']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', (string) $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', (string) $filters['to']);
        }

        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->where('description', 'like', $like)
                    ->orWhere('properties', 'like', $like)
                    ->orWhereHasMorph('causer', [User::class], fn (Builder $c) => $c->where('name', 'like', $like));
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Distinct action types (events) for the filter dropdown.
     *
     * @return Collection<int, string>
     */
    public function actionTypes(): Collection
    {
        return Activity::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');
    }
}
