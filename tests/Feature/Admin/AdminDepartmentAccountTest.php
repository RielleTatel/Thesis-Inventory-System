<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDepartmentAccountTest extends TestCase
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

    private function account(array $departmentAttributes = [], bool $active = true): Department
    {
        $department = Department::factory()->create($departmentAttributes);
        $user = User::factory()->create(['department_id' => $department->id, 'is_active' => $active]);
        $user->assignRole('department');

        return $department;
    }

    public function test_admin_can_create_a_department_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.accounts.store'), [
                'name' => 'College of Architecture',
                'code' => 'COA',
                'email' => 'architecture@univ.edu',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('departments', ['name' => 'College of Architecture', 'code' => 'COA']);

        $user = User::where('email', 'architecture@univ.edu')->firstOrFail();
        $department = Department::where('code', 'COA')->firstOrFail();
        $this->assertSame($department->id, $user->department_id);
        $this->assertTrue($user->hasRole('department'));
        $this->assertTrue($user->is_active);
    }

    public function test_create_validates_and_enforces_unique_code_and_email(): void
    {
        $this->account(['code' => 'CCS']);
        $existingEmail = Department::where('code', 'CCS')->first()->users()->value('email');

        $this->actingAs($this->admin())
            ->post(route('admin.accounts.store'), ['name' => '', 'code' => 'CCS', 'email' => $existingEmail])
            ->assertSessionHasErrors(['name', 'code', 'email', 'password']);
    }

    public function test_admin_can_edit_an_account(): void
    {
        $department = $this->account(['name' => 'Old Name', 'code' => 'OLD']);

        $this->actingAs($this->admin())
            ->put(route('admin.accounts.update', $department), [
                'name' => 'New Name',
                'code' => 'NEW',
                'email' => 'new@univ.edu',
            ])
            ->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'New Name', 'code' => 'NEW']);
        $this->assertDatabaseHas('users', ['department_id' => $department->id, 'email' => 'new@univ.edu']);
    }

    public function test_admin_can_toggle_account_status(): void
    {
        $department = $this->account(active: true);
        $login = $department->users()->firstOrFail();

        $this->actingAs($this->admin())->patch(route('admin.accounts.toggle', $department));
        $this->assertFalse($login->fresh()->is_active);

        $this->actingAs($this->admin())->patch(route('admin.accounts.toggle', $department));
        $this->assertTrue($login->fresh()->is_active);
    }

    public function test_admin_can_reset_a_department_password(): void
    {
        $department = $this->account(['code' => 'RST']);
        $login = $department->users()->firstOrFail();

        $this->actingAs($this->admin())
            ->patch(route('admin.accounts.reset-password', $department), [
                'password' => 'fresh-relay-secret',
                'password_confirmation' => 'fresh-relay-secret',
            ])
            ->assertRedirect(route('admin.accounts.index'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('fresh-relay-secret', $login->fresh()->password));
    }

    public function test_reset_validates_password_rules_and_confirmation(): void
    {
        $department = $this->account(['code' => 'RST2']);
        $original = $department->users()->firstOrFail()->password;

        $this->actingAs($this->admin())
            ->patch(route('admin.accounts.reset-password', $department), [
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ])
            ->assertSessionHasErrors('password');

        // Password is left unchanged when validation fails.
        $this->assertSame($original, $department->users()->firstOrFail()->password);
    }

    public function test_reset_leaves_role_and_active_status_untouched(): void
    {
        $department = $this->account(['code' => 'RST3'], active: true);
        $login = $department->users()->firstOrFail();

        $this->actingAs($this->admin())
            ->patch(route('admin.accounts.reset-password', $department), [
                'password' => 'another-relay-secret',
                'password_confirmation' => 'another-relay-secret',
            ]);

        $fresh = $login->fresh();
        $this->assertTrue($fresh->is_active);
        $this->assertTrue($fresh->hasRole('department'));
    }

    public function test_non_admins_cannot_reset_a_department_password(): void
    {
        $department = $this->account();
        $deptUser = $department->users()->firstOrFail();
        $original = $deptUser->password;

        $payload = ['password' => 'hijacked-secret', 'password_confirmation' => 'hijacked-secret'];

        // Guest → login.
        $this->patch(route('admin.accounts.reset-password', $department), $payload)
            ->assertRedirect(route('login'));

        // Department user → 403 (cannot reset even its own login this way).
        $this->actingAs($deptUser)
            ->patch(route('admin.accounts.reset-password', $department), $payload)
            ->assertForbidden();

        $this->assertSame($original, $department->users()->firstOrFail()->password);
    }

    public function test_deleting_account_keeping_records_retains_theses_and_removes_login(): void
    {
        $department = $this->account(['code' => 'KEEP']);
        $login = $department->users()->firstOrFail();
        $thesis = Thesis::factory()->create(['department_id' => $department->id]);
        $thesis->authors()->create(['name' => 'Author One', 'position' => 0]);

        $this->actingAs($this->admin())
            ->delete(route('admin.accounts.destroy', $department), ['mode' => 'keep'])
            ->assertRedirect(route('admin.accounts.index'));

        // Department + theses (and their children) stay; only the login is gone.
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('theses', ['id' => $thesis->id, 'department_id' => $department->id]);
        $this->assertDatabaseHas('thesis_authors', ['thesis_id' => $thesis->id]);
        $this->assertDatabaseMissing('users', ['id' => $login->id]);
    }

    public function test_deleting_account_with_records_removes_everything(): void
    {
        $department = $this->account(['code' => 'DEL']);
        $login = $department->users()->firstOrFail();
        $thesis = Thesis::factory()->create(['department_id' => $department->id]);
        $thesis->authors()->create(['name' => 'Author One', 'position' => 0]);

        $this->actingAs($this->admin())
            ->delete(route('admin.accounts.destroy', $department), ['mode' => 'delete'])
            ->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseMissing('theses', ['id' => $thesis->id]);
        $this->assertDatabaseMissing('thesis_authors', ['thesis_id' => $thesis->id]);
        $this->assertDatabaseMissing('users', ['id' => $login->id]);
    }

    public function test_delete_requires_a_valid_mode(): void
    {
        $department = $this->account();

        $this->actingAs($this->admin())
            ->delete(route('admin.accounts.destroy', $department), ['mode' => 'nonsense'])
            ->assertSessionHasErrors('mode');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_non_admins_cannot_access_admin_routes(): void
    {
        // Guest → login.
        $this->get(route('admin.accounts.index'))->assertRedirect(route('login'));

        // Department user → 403.
        $department = $this->account();
        $deptUser = $department->users()->firstOrFail();
        $this->actingAs($deptUser)->get(route('admin.accounts.index'))->assertForbidden();
        $this->actingAs($deptUser)->post(route('admin.accounts.store'), [])->assertForbidden();
    }

    public function test_deactivated_login_cannot_authenticate(): void
    {
        $department = Department::factory()->create();
        User::factory()->inactive()->create([
            'department_id' => $department->id,
            'email' => 'disabled@univ.edu',
        ]);

        $this->post('/login', ['email' => 'disabled@univ.edu', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
