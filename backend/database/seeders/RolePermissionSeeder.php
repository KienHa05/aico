<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the role-permission relationships.
     */
    public function run(): void
    {
        $admin = Role::where('slug', 'admin')->firstOrFail();

        $permissions = Permission::query()->get();

        $admin->permissions()->sync($permissions->modelKeys());

        $customer = Role::where('slug', 'customer')->firstOrFail();

        $customer->permissions()->sync([]);
    }
}
