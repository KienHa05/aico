<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware([
            'auth:api',
            SubstituteBindings::class,
        ])->group(function (): void {
            Route::get('/api/v1/test/policy/users/{user}', function (User $user) {
                Gate::authorize('view', $user);

                return response()->json([
                    'success' => true,
                    'message' => 'User view authorized.',
                ]);
            });

            Route::patch('/api/v1/test/policy/users/{user}', function (User $user) {
                Gate::authorize('update', $user);

                return response()->json([
                    'success' => true,
                    'message' => 'User update authorized.',
                ]);
            });
        });
    }

    public function test_guest_is_unauthorized(): void
    {
        $target = User::factory()->create();

        $response = $this->getJson(
            "/api/v1/test/policy/users/{$target->getKey()}"
        );

        $response->assertStatus(401);
    }

    public function test_customer_can_view_own_user_resource(): void
    {
        $customer = User::factory()->create();
        $token = $this->tokenFor($customer);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$customer->getKey()}"
            );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User view authorized.',
            ]);
    }

    public function test_customer_can_update_own_user_resource(): void
    {
        $customer = User::factory()->create();
        $token = $this->tokenFor($customer);

        $response = $this
            ->withToken($token)
            ->patchJson(
                "/api/v1/test/policy/users/{$customer->getKey()}"
            );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User update authorized.',
            ]);
    }

    public function test_customer_cannot_view_another_user_resource(): void
    {
        $customer = User::factory()->create();
        $target = User::factory()->create();
        $token = $this->tokenFor($customer);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_another_user_resource(): void
    {
        $customer = User::factory()->create();
        $target = User::factory()->create();
        $token = $this->tokenFor($customer);

        $response = $this
            ->withToken($token)
            ->patchJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response->assertStatus(403);
    }

    public function test_authenticated_user_without_permission_cannot_access_another_user(): void
    {
        $role = Role::create([
            'name' => 'Customer',
            'description' => 'Store customer',
            'slug' => 'customer',
        ]);

        Permission::create([
            'name' => 'Manage Users',
            'slug' => 'manage-users',
            'description' => 'Create, update and delete users',
        ]);

        $user = User::factory()->create();
        $target = User::factory()->create();

        $user->roles()->attach($role);

        $token = $this->tokenFor($user);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_permission_can_access_another_user(): void
    {
        $role = Role::create([
            'name' => 'User Manager',
            'description' => 'User manager',
            'slug' => 'user-manager',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Users',
            'description' => 'Create, update and delete users',
            'slug' => 'manage-users',
        ]);

        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $target = User::factory()->create();

        $user->roles()->attach($role);

        $token = $this->tokenFor($user);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User view authorized.',
            ]);
    }

    public function test_admin_can_access_another_user_through_existing_permission(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Store administrator',
            'slug' => 'admin',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Users',
            'description' => 'Create, update and delete users',
            'slug' => 'manage-users',
        ]);

        $role->permissions()->attach($permission);

        $admin = User::factory()->create();
        $target = User::factory()->create();

        $admin->roles()->attach($role);

        $token = $this->tokenFor($admin);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response->assertStatus(200);
    }

    public function test_superadmin_bypasses_policy_without_permission(): void
    {
        $role = Role::create([
            'name' => 'Superadmin',
            'description' => 'Unrestricted administrator',
            'slug' => 'superadmin',
        ]);

        $superadmin = User::factory()->create();
        $target = User::factory()->create();

        $superadmin->roles()->attach($role);

        $token = $this->tokenFor($superadmin);

        $response = $this
            ->withToken($token)
            ->getJson(
                "/api/v1/test/policy/users/{$target->getKey()}"
            );

        $response->assertStatus(200);
    }

    public function test_manage_users_gate_uses_existing_permission_system(): void
    {
        $role = Role::create([
            'name' => 'User Manager',
            'description' => 'User manager',
            'slug' => 'user-manager',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Users',
            'description' => 'Create, update and delete users',
            'slug' => 'manage-users',
        ]);

        $role->permissions()->attach($permission);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertTrue(
            Gate::forUser($user)->allows('manage-users')
        );
    }

    public function test_manage_users_gate_denies_user_without_permission(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(
            Gate::forUser($user)->allows('manage-users')
        );
    }

    private function tokenFor(User $user): string
    {
        return JWTAuth::fromUser($user);
    }
}
