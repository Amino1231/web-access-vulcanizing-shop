<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    protected array $defaultPermission = [
        'can_view',
        'can_view_any',
        'can_create',
        'can_update',
        'can_delete',
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach($this->defaultPermission as $permission ){
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
