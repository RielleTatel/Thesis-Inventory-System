<?php

namespace App\Repositories;

use App\Models\Thesis;
use App\Models\ThesisKeyword;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Single home for thesis query/search logic (coding standard #5).
 *
 * The public viewer, the department list, and admin views all need the same
 * filters — keeping them here stops search from silently diverging per screen.
 */
class ThesisRepository
{
    /**
     * Combined, paginated result set across ALL departments (FR-6.x).
     *
     * @param  array<string, mixed>  $filters  q, year_from, year_to, program, keyword
     * @return LengthAwarePaginator<int, Thesis>
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Thesis::query(), $filters)
            ->with(['department', 'authors', 'keywords'])
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Apply the shared filter set to a query (reusable by other scopes/screens).
     *
     * @param  Builder<Thesis>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Thesis>
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->where('title', 'like', $like)
                    ->orWhere('abstract', 'like', $like)
                    ->orWhere('program', 'like', $like)
                    ->orWhereHas('authors', fn (Builder $a) => $a->where('name', 'like', $like))
                    ->orWhereHas('advisers', fn (Builder $a) => $a->where('name', 'like', $like))
                    ->orWhereHas('keywords', fn (Builder $a) => $a->where('name', 'like', $like));
            });
        }

        if (! empty($filters['year_from'])) {
            $query->where('year', '>=', (int) $filters['year_from']);
        }

        if (! empty($filters['year_to'])) {
            $query->where('year', '<=', (int) $filters['year_to']);
        }

        if (! empty($filters['program'])) {
            $query->where('program', $filters['program']);
        }

        if (! empty($filters['keyword'])) {
            $query->whereHas('keywords', fn (Builder $k) => $k->where('name', $filters['keyword']));
        }

        return $query;
    }

    /**
     * Distinct programs for the filter dropdown.
     *
     * @return Collection<int, string>
     */
    public function programs(): Collection
    {
        return Thesis::query()
            ->whereNotNull('program')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');
    }

    /**
     * Distinct keyword names for the filter dropdown.
     *
     * @return Collection<int, string>
     */
    public function keywords(): Collection
    {
        return ThesisKeyword::query()
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
    }

    /**
     * Distinct years (newest first) for the year-range dropdowns.
     *
     * @return Collection<int, int>
     */
    public function years(): Collection
    {
        return Thesis::query()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
