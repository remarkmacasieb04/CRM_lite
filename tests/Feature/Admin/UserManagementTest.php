<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_user_management_page(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Person',
            'email' => 'admin@example.com',
        ]);
        User::factory()->create([
            'name' => 'Workspace User',
            'email' => 'user@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/Users')
                ->has('users', 2)
                ->has('roleOptions', 2));
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_another_users_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'role' => UserRole::User,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'role' => UserRole::Admin->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(UserRole::Admin, $user->fresh()->role);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $admin), [
                'role' => UserRole::User->value,
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }
}
