<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['Michelin', 'Bridgestone', 'Goodyear', 'Continental', 'Pirelli'];

        foreach (Tenant::all() as $tenant) {
            foreach ($brands as $brand) {
                Brand::updateOrCreate(
                    [
                        'tenant_id'   => $tenant->id,
                        'brand_slug'  => Str::slug($brand),
                    ],
                    [
                        'brand_name' => $brand,
                    ]
                );
            }
        }
    }
}