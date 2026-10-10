<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tire Services',    'description' => 'Mounting, balancing, vulcanizing'],
            ['name' => 'Repair Services',  'description' => 'Puncture repair, patch jobs'],
            ['name' => 'Maintenance',      'description' => 'Rotation, alignment, pressure checks'],
            ['name' => 'Roadside',         'description' => 'Emergency roadside assistance'],
        ];

        foreach (Tenant::all() as $tenant) {
            foreach ($categories as $cat) {
                ServiceCategory::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'slug'      => Str::slug($cat['name']),
                    ],
                    [
                        'name'        => $cat['name'],
                        'description' => $cat['description'],
                    ]
                );
            }
        }
    }
}