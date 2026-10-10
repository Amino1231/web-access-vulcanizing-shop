<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        // ─── Admin ─────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Platform Admin',
                'password' => Hash::make('admin123'),
                'phone'    => '09170000001',
                'address'  => 'Manila, Philippines',
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // ─── Shop Owners ───────────────────────
        $owners = [
            ['name' => 'Juan Dela Cruz',   'email' => 'owner@gmail.com',    'phone' => '09170000002', 'address' => 'Quezon City, Metro Manila'],
            ['name' => 'Maria Santos',     'email' => 'owner2@gmail.com',   'phone' => '09170000003', 'address' => 'Cebu City, Cebu'],
            ['name' => 'Pedro Reyes',      'email' => 'owner3@gmail.com',   'phone' => '09170000004', 'address' => 'Davao City, Davao del Sur'],
        ];

        foreach ($owners as $owner) {
            $user = User::updateOrCreate(
                ['email' => $owner['email']],
                [
                    'name'     => $owner['name'],
                    'password' => Hash::make('owner123'),
                    'phone'    => $owner['phone'],
                    'address'  => $owner['address'],
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['owner']);
        }

        // ─── Customers ─────────────────────────
        $customers = [
            ['name' => 'Alice Reyes',   'email' => 'customer@gmail.com',   'phone' => '09171110001', 'address' => 'Makati, Metro Manila'],
            ['name' => 'Bob Villanueva','email' => 'customer2@gmail.com',  'phone' => '09171110002', 'address' => 'Pasig, Metro Manila'],
            ['name' => 'Carla Mendoza', 'email' => 'customer3@gmail.com',  'phone' => '09171110003', 'address' => 'Taguig, Metro Manila'],
            ['name' => 'Daniel Ong',    'email' => 'customer4@gmail.com',  'phone' => '09171110004', 'address' => 'Mandaluyong, Metro Manila'],
        ];

        foreach ($customers as $customer) {
            $user = User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name'     => $customer['name'],
                    'password' => Hash::make('customer123'),
                    'phone'    => $customer['phone'],
                    'address'  => $customer['address'],
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['customer']);
        }

        // ─── Employee users (still customers in Spatie, but linked to Employee rows) ───
        $employees = [
            ['name' => 'Rico Santos',   'email' => 'employee@gmail.com',   'phone' => '09172220001'],
            ['name' => 'Lito Cruz',     'email' => 'employee2@gmail.com',  'phone' => '09172220002'],
            ['name' => 'Nina Gomez',    'email' => 'employee3@gmail.com',  'phone' => '09172220003'],
        ];

        foreach ($employees as $emp) {
            $user = User::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name'     => $emp['name'],
                    'password' => Hash::make('employee123'),
                    'phone'    => $emp['phone'],
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['employee']);
        }
    }
}