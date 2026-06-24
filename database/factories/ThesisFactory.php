<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Thesis>
 */
class ThesisFactory extends Factory
{
    protected $model = Thesis::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'title' => Str::title(rtrim(fake()->sentence(6), '.')),
            'program' => fake()->randomElement([
                'BS Computer Science',
                'BS Information Technology',
                'BS Civil Engineering',
                'BS Environmental Engineering',
            ]),
            'year' => fake()->numberBetween(2018, 2025),
            'abstract' => fake()->paragraph(5),
            'recommendations' => fake()->paragraph(3),
            'status' => 'published',
        ];
    }

    /**
     * Attach a realistic spread of ordered multi-value rows
     * (authors, advisers, panelists, proofreaders, keywords) after creation.
     */
    public function withRelations(): static
    {
        return $this->afterCreating(function (Thesis $thesis): void {
            $this->fillOrdered($thesis, 'authors', fn: fn () => fake()->name(), count: fake()->numberBetween(1, 3));
            $this->fillOrdered($thesis, 'advisers', fn: fn () => 'Dr. '.fake()->name(), count: fake()->numberBetween(1, 2));
            $this->fillOrdered($thesis, 'panelists', fn: fn () => 'Dr. '.fake()->name(), count: fake()->numberBetween(2, 3));
            // Optional: 0 means this thesis has none (displayed as "N/A").
            $this->fillOrdered($thesis, 'proofreaders', fn: fn () => fake()->name(), count: fake()->numberBetween(0, 2));
            $this->fillOrdered($thesis, 'keywords', fn: fn () => fake()->unique()->word(), count: fake()->numberBetween(3, 5));
        });
    }

    /**
     * @param  callable():string  $fn
     */
    private function fillOrdered(Thesis $thesis, string $relation, callable $fn, int $count): void
    {
        for ($position = 0; $position < $count; $position++) {
            $thesis->{$relation}()->create(['name' => $fn(), 'position' => $position]);
        }
    }
}
