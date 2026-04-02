<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_user_index(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $listedUser = User::factory()->create([
            'name' => 'Staff User',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('auth.user.is_super_admin', true)
                ->has('users', 2)
                ->where('users.0.name', fn (string $name) => in_array($name, [$superAdmin->name, $listedUser->name], true))
                ->where('users.1.name', fn (string $name) => in_array($name, [$superAdmin->name, $listedUser->name], true))
            );
    }
}
