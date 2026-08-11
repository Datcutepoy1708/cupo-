<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::firstOrCreate(['name' => 'manage roles']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'approve products']);

        // Create admin user
        $this->adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminRole->givePermissionTo(Permission::all());

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->adminUser->assignRole($this->adminRole);
    }

    public function test_admin_can_access_roles_management_page_and_data_api(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/roles');
        $response->assertStatus(200);

        $apiResponse = $this->actingAs($this->adminUser)->getJson('/admin/roles/data');
        $apiResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_admin_can_create_a_new_custom_role_with_permissions(): void
    {
        $payload = [
            'name' => 'content-editor',
            'permissions' => ['approve products'],
        ];

        $response = $this->actingAs($this->adminUser)->postJson('/admin/roles', $payload);
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'content-editor');

        $this->assertDatabaseHas('roles', ['name' => 'content-editor']);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::create(['name' => 'support-staff']);

        $payload = [
            'name' => 'support-staff',
            'permissions' => ['manage users'],
        ];

        $response = $this->actingAs($this->adminUser)->putJson("/admin/roles/{$role->id}", $payload);
        $response->assertStatus(200);

        $this->assertTrue($role->fresh()->hasPermissionTo('manage users'));
    }

    public function test_admin_can_assign_role_to_an_admin_user(): void
    {
        $targetUser = User::factory()->create(['role' => 'admin']);
        Role::create(['name' => 'accountant']);

        $payload = [
            'user_id' => $targetUser->id,
            'role_name' => 'accountant',
        ];

        $response = $this->actingAs($this->adminUser)->postJson('/admin/roles/assign-user', $payload);
        $response->assertStatus(200);

        $this->assertTrue($targetUser->fresh()->hasRole('accountant'));
    }

    public function test_admin_can_create_staff_account(): void
    {
        Role::create(['name' => 'moderator']);

        $payload = [
            'name' => 'Nguyen Van Staff',
            'email' => 'staff_new@cupo.vn',
            'phone' => '0912345678',
            'password' => 'password123',
            'role_name' => 'moderator',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminUser)->postJson('/admin/staff', $payload);
        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'email' => 'staff_new@cupo.vn',
            'role' => 'moderator',
        ]);
    }

    public function test_admin_can_update_staff_profile(): void
    {
        Role::create(['name' => 'accountant']);
        $staff = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'Nguyen Van Updated',
            'email' => $staff->email,
            'phone' => '0988776655',
            'role_name' => 'accountant',
            'status' => 'blocked',
        ];

        $response = $this->actingAs($this->adminUser)->putJson("/admin/staff/{$staff->id}", $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Nguyen Van Updated',
            'status' => 'blocked',
            'role' => 'accountant',
        ]);
    }

    public function test_admin_can_delete_staff_account(): void
    {
        $staff = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($this->adminUser)->deleteJson("/admin/staff/{$staff->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }
}
