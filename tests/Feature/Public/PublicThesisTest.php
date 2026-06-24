<?php

namespace Tests\Feature\Public;

use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->get('/?'.http_build_query(['keyword' => ['robotics']]))
            ->assertSee('Robotics Study')
            ->assertDontSee('Biology Study');
    }

    public function test_filter_by_multiple_keywords_matches_any(): void
    {
        $this->makeThesis(['title' => 'Robotics Study'], keywords: ['robotics']);
        $this->makeThesis(['title' => 'Biology Study'], keywords: ['biology']);
        $this->makeThesis(['title' => 'Chemistry Study'], keywords: ['chemistry']);

        // Selecting two keywords returns theses carrying ANY of them (OR), not both.
        $this->get('/?'.http_build_query(['keyword' => ['robotics', 'biology']]))
            ->assertSee('Robotics Study')
            ->assertSee('Biology Study')
            ->assertDontSee('Chemistry Study');
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

    public function test_detail_page_shows_proofreaders_in_order(): void
    {
        $thesis = $this->makeThesis(['title' => 'Proofread Thesis']);
        $thesis->proofreaders()->create(['name' => 'Pat Reader', 'position' => 0]);
        $thesis->proofreaders()->create(['name' => 'Sam Editor', 'position' => 1]);

        $response = $this->get(route('public.thesis.show', $thesis))->assertOk();

        $response->assertSee('Proofreader')->assertSee('Pat Reader')->assertSee('Sam Editor');
        $response->assertSeeInOrder(['Pat Reader', 'Sam Editor']);
    }

    public function test_detail_page_shows_na_when_a_thesis_has_no_proofreaders(): void
    {
        // makeThesis attaches no proofreaders — an empty relationship reads "N/A"
        // (display concern only; nothing is stored).
        $thesis = $this->makeThesis(['title' => 'No Proofreaders Thesis']);

        $this->get(route('public.thesis.show', $thesis))
            ->assertOk()
            ->assertSee('Proofreader')
            ->assertSee('N/A');
    }

    public function test_approval_page_route_streams_the_stored_image_inline(): void
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->image('approval.jpg')->store('approval_pages', 'local');
        $thesis = $this->makeThesis(['title' => 'Has Approval']);
        $thesis->forceFill(['approval_page_path' => $path])->save();

        // Public (no auth) and served inline with the stored image's content type.
        $response = $this->get(route('public.thesis.approval-page', $thesis));

        $response->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_approval_page_route_404s_when_no_image_is_stored(): void
    {
        $thesis = $this->makeThesis(['title' => 'No Approval']);

        $this->get(route('public.thesis.approval-page', $thesis))->assertNotFound();
    }

    public function test_approval_page_route_404s_for_a_non_image_file(): void
    {
        Storage::fake('local');
        // A poisoned path pointing at a non-image must never be streamed.
        $path = UploadedFile::fake()->create('notes.pdf', 40, 'application/pdf')->store('approval_pages', 'local');
        $thesis = $this->makeThesis(['title' => 'Bad File']);
        $thesis->forceFill(['approval_page_path' => $path])->save();

        $this->get(route('public.thesis.approval-page', $thesis))->assertNotFound();
    }
}
