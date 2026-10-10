<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & permissions first
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // 2. Users, tenants, and the pivot
        $this->call([
            UserSeeder::class,
            TenantSeeder::class,
        ]);

        // 3. Catalog (per tenant)
        $this->call([
            ProductCategorySeeder::class,
            ServiceCategorySeeder::class,
            BrandSeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            PostSeeder::class,
        ]);

        // 4. Operations
        $this->call([
            EmployeeSeeder::class,
            VehicleSeeder::class,
            AppointmentSeeder::class,
            RoadSideSeeder::class,
            OrderSeeder::class,
            SaleSeeder::class,
            InventorySeeder::class,
            ReviewSeeder::class,
        ]);
    }
}