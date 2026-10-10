<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tires',        'description' => 'New and used tires for all vehicle types'],
            ['name' => 'Tubes',        'description' => 'Inner tubes for tires'],
            ['name' => 'Rims & Wheels','description' => 'Wheel rims and complete wheel sets'],
            ['name' => 'Accessories',  'description' => 'Valve stems, caps, and tire accessories'],
            ['name' => 'Tools',        'description' => 'Tire repair tools and equipment'],
        ];

        foreach (Tenant::all() as $tenant) {
            foreach ($categories as $cat) {
                ProductCategory::updateOrCreate(
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