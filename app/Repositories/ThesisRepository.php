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
     * All theses across all departments for the admin overview — drafts + published.
     * Admin-only; never call this from public or department contexts.
     *
     * Extra filters beyond the shared set: department_id, status.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Thesis>
     */
    public function allForAdmin(array $filters = []): LengthAwarePaginator
    {
        $query = Thesis::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        return $this->applyFilters($query, $filters)
            ->with(['department', 'authors'])
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Combined, paginated result set across ALL departments (FR-6.x).
     *
     * @param  array<string, mixed>  $filters  q, year_from, year_to, program, keyword
     * @return LengthAwarePaginator<int, Thesis>
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Thesis::published(), $filters)
            ->with(['department', 'authors', 'keywords'])
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * One department's own theses, searchable via the same shared filters
     * (FR-3.4 scoping). Reuses applyFilters so search can't diverge (rule #5).
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Thesis>
     */
    public function forDepartment(int $departmentId, array $filters = []): LengthAwarePaginator
    {
        $query = Thesis::query()->where('department_id', $departmentId);

        return $this->applyFilters($query, $filters)
            ->with(['authors'])
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Summary stats for a department's dashboard cards.
     *
     * @return array{total: int, published: int, drafts: int, latest_year: int|null, last_updated: string|null}
     */
    public function statsForDepartment(int $departmentId): array
    {
        $row = Thesis::query()
            ->where('department_id', $departmentId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = \'published\' THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) as drafts,
                MAX(year) as latest_year,
                MAX(updated_at) as last_updated
            ')
            ->toBase()
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'published' => (int) ($row->published ?? 0),
            'drafts' => (int) ($row->drafts ?? 0),
            'latest_year' => $row->latest_year !== null ? (int) $row->latest_year : null,
            'last_updated' => $row->last_updated !== null ? (string) $row->last_updated : null,
        ];
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
        return Thesis::published()
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
            ->whereHas('thesis', fn ($q) => $q->where('status', 'published'))
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
        return Thesis::published()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
