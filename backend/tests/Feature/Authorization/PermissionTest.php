<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth:api', 'permission:manage-products'])
            ->get('/api/v1/test/permission/manage-products', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission granted.',
                ]);
            });

        Route::middleware(['auth:api', 'permission:manage-products,manage-orders'])
            ->get('/api/v1/test/permission/products-or-orders', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission granted.',
                ]);
            });
    }

    public function test_user_has_permission_through_role(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Store administrator',
            'slug' => 'admin',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $role->permissions()->attach($permission);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertTrue(
            $user->hasPermission('manage-products')
        );
    }

    public function test_user_does_not_have_permission_when_role_lacks_permission(): void
    {
        $role = Role::create([
            'name' => 'Customer',
            'description' => 'Store customer',
            'slug' => 'customer',
        ]);

        Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertFalse(
            $user->hasPermission('manage-products')
        );
    }

    public function test_user_does_not_have_unknown_permission(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(
            $user->hasPermission('does-not-exist')
        );
    }

    public function test_user_has_any_permission_when_user_has_one_matching_permission(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Store administrator',
            'slug' => 'admin',
        ]);

        $firstPermission = Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        Permission::create([
            'name' => 'Manage Orders',
            'slug' => 'manage-orders',
            'description' => 'Manage customer orders',
        ]);

        $role->permissions()->attach($firstPermission);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertTrue(
            $user->hasAnyPermission([
                'manage-products',
                'manage-orders',
            ])
        );

        $this->assertTrue(
            $user->hasAnyPermission([
                'manage-orders',
                'manage-products',
            ])
        );
    }

    public function test_user_does_not_have_any_permission_when_none_match(): void
    {
        $role = Role::create([
            'name' => 'Customer',
            'description' => 'Store customer',
            'slug' => 'customer',
        ]);

        Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $this->assertFalse(
            $user->hasAnyPermission([
                'manage-products',
                'manage-orders',
            ])
        );
    }

    public function test_authenticated_user_with_permission_can_access_protected_route(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Store administrator',
            'slug' => 'admin',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $role->permissions()->attach($permission);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/v1/test/permission/manage-products');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Permission granted.',
            ]);
    }

    public function test_authenticated_user_without_permission_is_forbidden(): void
    {
        $role = Role::create([
            'name' => 'Customer',
            'description' => 'Store customer',
            'slug' => 'customer',
        ]);

        Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/v1/test/permission/manage-products');

        $response
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden.',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/v1/test/permission/manage-products');

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_permission_middleware_allows_user_with_any_matching_permission(): void
    {
        $role = Role::create([
            'name' => 'Order Manager',
            'description' => 'Order manager',
            'slug' => 'order-manager',
        ]);

        $permission = Permission::create([
            'name' => 'Manage Orders',
            'slug' => 'manage-orders',
            'description' => 'Manage customer orders',
        ]);

        $role->permissions()->attach($permission);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/v1/test/permission/products-or-orders');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Permission granted.',
            ]);
    }

    public function test_permission_middleware_forbids_user_without_any_matching_permission(): void
    {
        $role = Role::create([
            'name' => 'Customer',
            'description' => 'Store customer',
            'slug' => 'customer',
        ]);

        $productPermission = Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
            'description' => 'Create and update products',
        ]);

        $orderPermission = Permission::create([
            'name' => 'Manage Orders',
            'slug' => 'manage-orders',
            'description' => 'Manage customer orders',
        ]);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        $role->permissions()->detach([
            $productPermission->getKey(),
            $orderPermission->getKey(),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)
            ->getJson('/api/v1/test/permission/products-or-orders');

        $response
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden.',
            ]);
    }
}
