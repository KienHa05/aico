<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::insert([
            [
                'name' => 'View Dashboard',
                'slug' => 'view-dashboard',
                'description' => 'Access dashboard',
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'manage-users',
                'description' => 'Create, update and delete users',
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'manage-roles',
                'description' => 'Manage roles',
            ],
            [
                'name' => 'Manage Products',
                'slug' => 'manage-products',
                'description' => 'Create and update products',
            ],
            [
                'name' => 'Manage Categories',
                'slug' => 'manage-categories',
                'description' => 'Manage product categories',
            ],
            [
                'name' => 'Manage Orders',
                'slug' => 'manage-orders',
                'description' => 'Manage customer orders',
            ],
            [
                'name' => 'Manage Inventory',
                'slug' => 'manage-inventory',
                'description' => 'Manage inventory',
            ],
        ]);
    }
}
