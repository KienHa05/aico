<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Authentication
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,

            // Catalog
            CategorySeeder::class,
            TagSeeder::class,

            // Product Attributes
            AttributeSeeder::class,
            AttributeOptionSeeder::class,

            // Products
            ProductSeeder::class,
            ProductTagSeeder::class,
            ProductVariantSeeder::class,
            VariantAttributeValueSeeder::class,
            ProductImageSeeder::class,
            InventorySeeder::class,

            // Customer
            AddressSeeder::class,

            // Cart
            CartSeeder::class,
            CartItemSeeder::class,

            // Orders
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}
