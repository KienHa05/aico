<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'Full system access',
            ],
            [
                'name' => 'Admin',
                'description' => 'Store administrator',
            ],
            [
                'name' => 'Manager',
                'description' => 'Store manager',
            ],
        ];

        foreach ($roles as $role) {
            Role::create([
                ...$role,
                'slug' => Str::slug($role['name']),
            ]);
        }
    }
}
