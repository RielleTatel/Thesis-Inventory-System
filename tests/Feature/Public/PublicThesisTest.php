<?php

namespace Tests\Feature\Public;

use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicThesisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<string>  $authors
     * @param  list<string>  $keywords
     * @param  list<string>  $advisers
     * @param  list<string>  $panelists
     * @param  array<string, mixed>  $attributes
     */
    private function makeThesis(
        array $attributes = [],
        array $authors = [],
        array $keywords = [],
        array $advisers = [],
        array $panelists = [],
    ): Thesis {
        $thesis = Thesis::factory()->create($attributes);

        foreach (['authors' => $authors, 'keywords' => $keywords, 'advisers' => $advisers, 'panelists' => $panelists] as $relation => $names) {
            foreach (array_values($names) as $position => $name) {
                $thesis->{$relation}()->create(['name' => $name, 'position' => $position]);
            }
        }

        return $thesis;
    }

    public function test_browse_page_is_public_and_lists_theses(): void
    {
        $this->makeThesis(['title' => 'Quantum Bridges in Practice']);
        $this->makeThesis(['title' => 'Solar Roof Economics']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Quantum Bridges in Practice')
            ->assertSee('Solar Roof Economics');
    }

    public function test_search_matches_title(): void
    {
        $this->makeThesis(['title' => 'Quantum Bridges in Practice']);
        $this->makeThesis(['title' => 'Solar Roof Economics']);

        $this->get('/?q=Quantum')
            ->assertSee('Quantum Bridges in Practice')
            ->assertDontSee('Solar Roof Economics');
    }

    public function test_search_matches_author_name(): void
    {
        $this->makeThesis(['title' => 'Findable By Author'], authors: ['Juan dela Cruz']);
        $this->makeThesis(['title' => 'Other Record'], authors: ['Maria Santos']);

        $this->get('/?q=dela+Cruz')
            ->assertSee('Findable By Author')
            ->assertDontSee('Other Record');
    }

    public function test_filter_by_keyword(): void
    {
        $this->makeThesis(['title' => 'Robotics Study'], keywords: ['robotics']);
        $this->makeThesis(['title' => 'Biology Study'], keywords: ['biology']);

        $this->get('/?keyword=robotics')
            ->assertSee('Robotics Study')
            ->assertDontSee('Biology Study');
    }

    public function test_filter_by_year_range(): void
    {
        $this->makeThesis(['title' => 'Old Work', 'year' => 2020]);
        $this->makeThesis(['title' => 'Recent Work', 'year' => 2024]);

        $this->get('/?year_from=2023')
            ->assertSee('Recent Work')
            ->assertDontSee('Old Work');
    }

    public function test_filter_by_program(): void
    {
        $this->makeThesis(['title' => 'CS Paper', 'program' => 'BS Computer Science']);
        $this->makeThesis(['title' => 'CE Paper', 'program' => 'BS Civil Engineering']);

        $this->get('/?program='.urlencode('BS Computer Science'))
            ->assertSee('CS Paper')
            ->assertDontSee('CE Paper');
    }

    public function test_results_span_all_departments(): void
    {
        $ccs = Department::factory()->create();
        $coe = Department::factory()->create();
        $this->makeThesis(['title' => 'From CCS', 'department_id' => $ccs->id]);
        $this->makeThesis(['title' => 'From COE', 'department_id' => $coe->id]);

        $this->get('/')
            ->assertSee('From CCS')
            ->assertSee('From COE');
    }

    public function test_no_results_shows_empty_state(): void
    {
        $this->makeThesis(['title' => 'Quantum Bridges']);

        $this->get('/?q=nonexistent-topic-xyz')
            ->assertSee('No theses match your search')
            ->assertDontSee('Quantum Bridges');
    }

    public function test_detail_page_shows_all_fields(): void
    {
        $thesis = $this->makeThesis(
            ['title' => 'Detailed Thesis', 'abstract' => 'A unique abstract sentence.', 'recommendations' => 'A unique recommendation sentence.'],
            authors: ['Author One'],
            keywords: ['signal-processing'],
            advisers: ['Dr. Adviser'],
            panelists: ['Dr. Panelist'],
        );

        $this->get(route('public.thesis.show', $thesis))
            ->assertOk()
            ->assertSee('Detailed Thesis')
            ->assertSee('A unique abstract sentence.')
            ->assertSee('A unique recommendation sentence.')
            ->assertSee('Author One')
            ->assertSee('Dr. Adviser')
            ->assertSee('Dr. Panelist')
            ->assertSee('signal-processing');
    }

    public function test_search_rejects_invalid_inputs(): void
    {
        // SearchThesisRequest: years must be integers, q is length-capped.
        $this->get('/?year_from=not-a-year')->assertSessionHasErrors('year_from');
        $this->get('/?q='.str_repeat('a', 200))->assertSessionHasErrors('q');
    }
}
