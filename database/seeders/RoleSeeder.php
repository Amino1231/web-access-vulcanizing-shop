<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['admin', 'owner', 'customer', 'employee'];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role, 'guard_name' => 'web'],
                []
            );
        }

        // Also reset cache after creating
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}