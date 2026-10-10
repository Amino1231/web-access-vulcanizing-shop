<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Tire Mounting',        'category' => 'Tire Services',   'price' => 150,  'duration' => 20,  'vehicle' => true],
            ['name' => 'Wheel Balancing',      'category' => 'Tire Services',   'price' => 250,  'duration' => 30,  'vehicle' => true],
            ['name' => 'Vulcanizing (Hot)',    'category' => 'Repair Services', 'price' => 350,  'duration' => 45,  'vehicle' => true],
            ['name' => 'Vulcanizing (Cold)',   'category' => 'Repair Services', 'price' => 200,  'duration' => 30,  'vehicle' => true],
            ['name' => 'Puncture Patch',       'category' => 'Repair Services', 'price' => 100,  'duration' => 15,  'vehicle' => true],
            ['name' => 'Tire Rotation',        'category' => 'Maintenance',     'price' => 200,  'duration' => 25,  'vehicle' => true],
            ['name' => 'Wheel Alignment',      'category' => 'Maintenance',     'price' => 500,  'duration' => 60,  'vehicle' => true],
            ['name' => 'Air Pressure Check',   'category' => 'Maintenance',     'price' => 0,    'duration' => 5,   'vehicle' => true],
            ['name' => 'Roadside Emergency',   'category' => 'Roadside',        'price' => 800,  'duration' => 90,  'vehicle' => false],
        ];

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            foreach ($services as $item) {
                $cat = ServiceCategory::where('tenant_id', $tenant->id)
                    ->where('slug', Str::slug($item['category']))
                    ->first();

                if (! $cat) continue;

                Service::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name'      => $item['name'],
                    ],
                    [
                        'service_category_id' => $cat->id,
                        'description'         => "Professional {$item['name']} service performed by certified technicians.",
                        'duration_minutes'    => $item['duration'],
                        'price'               => $item['price'],
                        'is_vehicle_service'  => $item['vehicle'],
                        'is_active'           => true,
                    ]
                );
            }
        }
    }
}