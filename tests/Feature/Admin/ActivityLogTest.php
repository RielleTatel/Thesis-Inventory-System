<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Thesis;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('administrator', 'web');
        Role::findOrCreate('department', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        return $user;
    }

    private function departmentUser(Department $department): User
    {
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('department');

        return $user;
    }

    public function test_creating_a_thesis_logs_activity_with_causer_and_subject(): void
    {
        $department = Department::factory()->create();
        $user = $this->departmentUser($department);

        $this->actingAs($user)->post(route('department.theses.store'), [
            'title' => 'Logged Thesis',
            'year' => 2024,
            'program' => 'BS Computer Science',
            'abstract' => 'Abstract.',
            'authors' => ['A'],
        ]);

        $thesis = Thesis::where('title', 'Logged Thesis')->firstOrFail();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'thesis',
            'event' => 'created',
            'subject_type' => Thesis::class,
            'subject_id' => $thesis->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    public function test_updating_and_deleting_a_thesis_logs_activity(): void
    {
        $department = Department::factory()->create();
        $user = $this->departmentUser($department);
        $thesis = Thesis::factory()->create(['department_id' => $department->id]);

        $this->actingAs($user)->put(route('department.theses.update', $thesis), [
            'title' => 'Updated Title',
            'year' => 2023,
            'program' => 'BS Information Technology',
            'abstract' => 'New abstract.',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'thesis', 'event' => 'updated', 'subject_id' => $thesis->id, 'causer_id' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('department.theses.destroy', $thesis));
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'thesis', 'event' => 'deleted', 'subject_id' => $thesis->id, 'causer_id' => $user->id,
        ]);
    }

    public function test_creating_an_account_logs_activity(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'name' => 'College of Logging',
            'code' => 'LOG',
            'email' => 'logging@univ.edu',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $department = Department::where('code', 'LOG')->firstOrFail();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'account',
            'event' => 'created',
            'subject_id' => $department->id,
            'causer_id' => $admin->id,
        ]);
    }

    public function test_deleting_an_account_logs_activity_with_records_mode(): void
    {
        $admin = $this->admin();
        $department = Department::factory()->create();
        $this->departmentUser($department);

        $this->actingAs($admin)->delete(route('admin.accounts.destroy', $department), ['mode' => 'keep']);

        $log = Activity::where('log_name', 'account')->where('event', 'deleted')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->causer_id);
        $this->assertSame('keep', data_get($log->properties, 'records_mode'));
    }

    public function test_activity_log_page_is_admin_only(): void
    {
        $department = Department::factory()->create();

        // Guest → login.
        $this->get(route('admin.activity-log.index'))->assertRedirect(route('login'));

        // Department user → 403.
        $this->actingAs($this->departmentUser($department))
            ->get(route('admin.activity-log.index'))->assertForbidden();

        // Admin → ok.
        $this->actingAs($this->admin())
            ->get(route('admin.activity-log.index'))->assertOk();
    }

    public function test_filter_by_action_type_narrows_results(): void
    {
        $thesis = Thesis::factory()->create();   // logs a 'created' event
        $thesis->update(['title' => 'Changed']); // logs an 'updated' event

        $repo = app(ActivityLogRepository::class);

        $this->assertSame(1, $repo->filter(['action' => 'created'])->total());
        $this->assertSame(1, $repo->filter(['action' => 'updated'])->total());
        $this->assertSame(0, $repo->filter(['action' => 'deleted'])->total());
    }
}
