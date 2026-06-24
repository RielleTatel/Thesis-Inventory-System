<?php

namespace App\Actions\Concerns;

use App\Models\Thesis;

/**
 * Shared logic for persisting the ordered multi-value rows
 * (authors, advisers, panelists, proofreaders, keywords) of a thesis.
 *
 * Each value becomes one row with a `position` reflecting its order in the
 * submitted list (coding standard #7). Blank entries are dropped and the
 * remaining values are re-numbered from zero.
 */
trait SyncsOrderedThesisValues
{
    /** @var list<string> */
    private array $orderedRelations = ['authors', 'advisers', 'panelists', 'proofreaders', 'keywords'];

    /**
     * Replace every ordered relation on the thesis from the given input data.
     *
     * @param  array<string, mixed>  $data
     */
    private function syncOrderedValues(Thesis $thesis, array $data): void
    {
        foreach ($this->orderedRelations as $relation) {
            $names = $this->cleanValues($data[$relation] ?? []);

            // Replace wholesale so removed rows disappear and positions stay contiguous.
            $thesis->{$relation}()->delete();

            foreach ($names as $position => $name) {
                $thesis->{$relation}()->create(['name' => $name, 'position' => $position]);
            }
        }
    }

    /**
     * Trim, drop blanks, and re-index to a 0-based ordered list.
     *
     * @return list<string>
     */
    private function cleanValues(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $trimmed = array_map(fn ($value) => trim((string) $value), $values);

        return array_values(array_filter($trimmed, fn (string $value) => $value !== ''));
    }
}
