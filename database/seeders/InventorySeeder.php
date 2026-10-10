<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            foreach (Product::where('tenant_id', $tenant->id)->get() as $product) {
                $current = $product->stock;
                $before  = max(0, $current - 20);
                $change  = $current - $before;

                Inventory::create([
                    'tenant_id'    => $tenant->id,
                    'product_id'   => $product->id,
                    'type'         => 'in',
                    'quantity'     => $change,
                    'before_stock' => $before,
                    'after_stock'  => $current,
                    'remarks'      => 'Initial stock on seed',
                ]);
            }
        }
    }
}