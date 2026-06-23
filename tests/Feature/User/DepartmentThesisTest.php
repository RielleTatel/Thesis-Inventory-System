<?php

namespace Tests\Feature\User;

use App\Models\Department;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentThesisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('department', 'web');
        Role::findOrCreate('administrator', 'web');
    }

    private function departmentUser(Department $department): User
    {
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('department');

        return $user;
    }

    private function thesisFor(Department $department, array $attributes = []): Thesis
    {
        return Thesis::factory()->create(['department_id' => $department->id, ...$attributes]);
    }

    public function test_index_lists_only_the_logged_in_departments_theses(): void
    {
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        $this->thesisFor($a, ['title' => 'Owned By A']);
        $this->thesisFor($b, ['title' => 'Owned By B']);

        $this->actingAs($this->departmentUser($a))
            ->get(route('department.theses.index'))
            ->assertOk()
            ->assertSee('Owned By A')
            ->assertDontSee('Owned By B');
    }

    public function test_department_can_create_thesis_with_ordered_values(): void
    {
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->post(route('department.theses.store'), [
                'status' => 'published',
                'title' => 'A Brand New Thesis',
                'year' => 2024,
                'program' => 'BS Computer Science',
                'abstract' => 'A short abstract.',
                'recommendations' => 'Some recommendations.',
                'authors' => ['Alice Reyes', '', 'Bob Santos'], // blank dropped
                'advisers' => ['Dr. Adviser'],
                'panelists' => ['Dr. P One', 'Dr. P Two'],
                'keywords' => ['machine-learning', 'nlp'],
            ])
            ->assertRedirect(route('department.theses.index'));

        $thesis = Thesis::where('title', 'A Brand New Thesis')->firstOrFail();
        $this->assertSame($a->id, $thesis->department_id);

        // Blank filtered, order preserved as positions.
        $this->assertDatabaseHas('thesis_authors', ['thesis_id' => $thesis->id, 'name' => 'Alice Reyes', 'position' => 0]);
        $this->assertDatabaseHas('thesis_authors', ['thesis_id' => $thesis->id, 'name' => 'Bob Santos', 'position' => 1]);
        $this->assertDatabaseMissing('thesis_authors', ['thesis_id' => $thesis->id, 'name' => '']);
        $this->assertDatabaseHas('thesis_keywords', ['thesis_id' => $thesis->id, 'name' => 'nlp', 'position' => 1]);
    }

    public function test_store_validates_required_fields(): void
    {
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->post(route('department.theses.store'), ['title' => '', 'abstract' => ''])
            ->assertSessionHasErrors(['title', 'year', 'program', 'abstract']);
    }

    public function test_department_can_update_its_own_thesis(): void
    {
        $a = Department::factory()->create();
        $thesis = $this->thesisFor($a, ['title' => 'Old Title']);
        $thesis->authors()->create(['name' => 'Original Author', 'position' => 0]);

        $this->actingAs($this->departmentUser($a))
            ->put(route('department.theses.update', $thesis), [
                'status' => 'published',
                'title' => 'Updated Title',
                'year' => 2023,
                'program' => 'BS Information Technology',
                'abstract' => 'Updated abstract.',
                'authors' => ['New Author'],
                'keywords' => ['updated'],
            ])
            ->assertRedirect(route('department.theses.index'));

        $this->assertDatabaseHas('theses', ['id' => $thesis->id, 'title' => 'Updated Title']);
        $this->assertDatabaseHas('thesis_authors', ['thesis_id' => $thesis->id, 'name' => 'New Author', 'position' => 0]);
        $this->assertDatabaseMissing('thesis_authors', ['thesis_id' => $thesis->id, 'name' => 'Original Author']);
    }

    public function test_department_can_delete_its_own_thesis(): void
    {
        $a = Department::factory()->create();
        $thesis = $this->thesisFor($a);

        $this->actingAs($this->departmentUser($a))
            ->delete(route('department.theses.destroy', $thesis))
            ->assertRedirect(route('department.theses.index'));

        $this->assertDatabaseMissing('theses', ['id' => $thesis->id]);
    }

    public function test_department_cannot_edit_another_departments_thesis(): void
    {
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        $foreign = $this->thesisFor($b);

        $userA = $this->departmentUser($a);

        $this->actingAs($userA)->get(route('department.theses.edit', $foreign))->assertForbidden();
        $this->actingAs($userA)->put(route('department.theses.update', $foreign), [
            'status' => 'published',
            'title' => 'Hijacked',
            'year' => 2024,
            'program' => 'BS Computer Science',
            'abstract' => 'x',
        ])->assertForbidden();

        $this->assertDatabaseMissing('theses', ['id' => $foreign->id, 'title' => 'Hijacked']);
    }

    public function test_department_cannot_delete_another_departments_thesis(): void
    {
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        $foreign = $this->thesisFor($b);

        $this->actingAs($this->departmentUser($a))
            ->delete(route('department.theses.destroy', $foreign))
            ->assertForbidden();

        $this->assertDatabaseHas('theses', ['id' => $foreign->id]);
    }

    public function test_department_area_root_redirects_to_theses(): void
    {
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->get('/department')
            ->assertRedirect('/department/theses');
    }

    public function test_department_users_are_redirected_from_dashboard_to_their_theses(): void
    {
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->get(route('dashboard'))
            ->assertRedirect(route('department.theses.index'));
    }

    public function test_guests_and_non_department_users_are_blocked(): void
    {
        $a = Department::factory()->create();

        // Guest → redirected to login.
        $this->get(route('department.theses.index'))->assertRedirect(route('login'));

        // Administrator role lacks the department role → 403.
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $this->actingAs($admin)->get(route('department.theses.index'))->assertForbidden();
    }

    /** Attach an existing approval-page file (on the fake s3 disk) to a thesis. */
    private function seedApprovalPage(Thesis $thesis, string $name = 'old.jpg'): string
    {
        $path = UploadedFile::fake()->image($name)->store('approval_pages', 's3');
        $thesis->forceFill(['approval_page_path' => $path])->save();

        return $path;
    }

    public function test_department_can_upload_an_approval_page_on_create(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->post(route('department.theses.store'), [
                'status' => 'published',
                'title' => 'Thesis With Approval Page',
                'year' => 2024,
                'program' => 'BS Computer Science',
                'abstract' => 'A short abstract.',
                'approval_page' => UploadedFile::fake()->image('approval.jpg'),
            ])
            ->assertRedirect(route('department.theses.index'));

        $thesis = Thesis::where('title', 'Thesis With Approval Page')->firstOrFail();

        $this->assertNotNull($thesis->approval_page_path);
        $this->assertStringStartsWith('approval_pages/', $thesis->approval_page_path);
        Storage::disk('s3')->assertExists($thesis->approval_page_path);
    }

    public function test_approval_page_must_be_a_valid_image(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();

        $this->actingAs($this->departmentUser($a))
            ->post(route('department.theses.store'), [
                'status' => 'published',
                'title' => 'Bad Upload',
                'year' => 2024,
                'program' => 'BS Computer Science',
                'abstract' => 'A short abstract.',
                'approval_page' => UploadedFile::fake()->create('notes.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHasErrors('approval_page');

        $this->assertEmpty(Storage::disk('s3')->allFiles());
    }

    public function test_uploading_a_new_approval_page_replaces_and_deletes_the_old_file(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();
        $thesis = $this->thesisFor($a);
        $oldPath = $this->seedApprovalPage($thesis);

        $this->actingAs($this->departmentUser($a))
            ->put(route('department.theses.update', $thesis), [
                'status' => 'published',
                'title' => $thesis->title,
                'year' => $thesis->year,
                'program' => $thesis->program,
                'abstract' => $thesis->abstract,
                'approval_page' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertRedirect(route('department.theses.index'));

        $thesis->refresh();
        Storage::disk('s3')->assertMissing($oldPath);
        $this->assertNotSame($oldPath, $thesis->approval_page_path);
        Storage::disk('s3')->assertExists($thesis->approval_page_path);
    }

    public function test_department_can_remove_the_approval_page(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();
        $thesis = $this->thesisFor($a);
        $path = $this->seedApprovalPage($thesis);

        $this->actingAs($this->departmentUser($a))
            ->put(route('department.theses.update', $thesis), [
                'status' => 'published',
                'title' => $thesis->title,
                'year' => $thesis->year,
                'program' => $thesis->program,
                'abstract' => $thesis->abstract,
                'remove_approval_page' => 1,
            ])
            ->assertRedirect(route('department.theses.index'));

        $thesis->refresh();
        $this->assertNull($thesis->approval_page_path);
        Storage::disk('s3')->assertMissing($path);
    }

    public function test_deleting_a_thesis_deletes_its_approval_page_file(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();
        $thesis = $this->thesisFor($a);
        $path = $this->seedApprovalPage($thesis);

        $this->actingAs($this->departmentUser($a))
            ->delete(route('department.theses.destroy', $thesis))
            ->assertRedirect(route('department.theses.index'));

        $this->assertDatabaseMissing('theses', ['id' => $thesis->id]);
        Storage::disk('s3')->assertMissing($path);
    }

    public function test_non_owner_department_cannot_upload_an_approval_page(): void
    {
        Storage::fake('s3');
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        $foreign = $this->thesisFor($b);

        $this->actingAs($this->departmentUser($a))
            ->put(route('department.theses.update', $foreign), [
                'status' => 'published',
                'title' => 'Hijacked',
                'year' => 2024,
                'program' => 'BS Computer Science',
                'abstract' => 'x',
                'approval_page' => UploadedFile::fake()->image('x.jpg'),
            ])
            ->assertForbidden();

        $foreign->refresh();
        $this->assertNull($foreign->approval_page_path);
        $this->assertEmpty(Storage::disk('s3')->allFiles());
    }
}
