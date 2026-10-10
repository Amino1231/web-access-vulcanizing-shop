<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $customerUsers = User::role('customer')->get();

        $vehicleTypes = [
            ['type' => 'Sedan',  'brand' => 'Toyota',  'model' => 'Vios',       'year' => '2020'],
            ['type' => 'SUV',    'brand' => 'Honda',   'model' => 'CR-V',       'year' => '2019'],
            ['type' => 'Pickup', 'brand' => 'Ford',    'model' => 'Ranger',     'year' => '2021'],
            ['type' => 'Van',    'brand' => 'Toyota',  'model' => 'HiAce',      'year' => '2018'],
        ];

        $firstActiveTenant = \App\Models\Tenant::where('is_active', true)->first();
        if (! $firstActiveTenant) return;

        foreach ($customerUsers as $index => $customer) {
            $v = $vehicleTypes[$index % count($vehicleTypes)];

            Vehicle::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'plate_number'=> 'ABC-' . (1000 + $index),
                ],
                [
                    'tenant_id'    => $firstActiveTenant->id,
                    'vehicle_type' => $v['type'],
                    'brand'        => $v['brand'],
                    'model'        => $v['model'],
                    'year_model'   => $v['year'],
                ]
            );
        }
    }
}