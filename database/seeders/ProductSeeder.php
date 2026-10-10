<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            [
                'name'         => 'Michelin Pilot Sport 4',
                'category'     => 'Tires',
                'brand'        => 'Michelin',
                'cost_price'   => 6500,
                'selling_price'=> 8200,
                'has_variants' => true,
                'variants'     => [
                    ['size' => '175/65 R14', 'price' => 7800, 'stock' => 12],
                    ['size' => '185/65 R15', 'price' => 8200, 'stock' => 8],
                    ['size' => '195/55 R16', 'price' => 8900, 'stock' => 5],
                ],
            ],
            [
                'name'         => 'Bridgestone Turanza',
                'category'     => 'Tires',
                'brand'        => 'Bridgestone',
                'cost_price'   => 5800,
                'selling_price'=> 7200,
                'has_variants' => false,
                'stock'        => 20,
            ],
            [
                'name'         => 'Goodyear Eagle F1',
                'category'     => 'Tires',
                'brand'        => 'Goodyear',
                'cost_price'   => 7000,
                'selling_price'=> 9200,
                'has_variants' => false,
                'stock'        => 15,
            ],
            [
                'name'         => 'Heavy Duty Inner Tube',
                'category'     => 'Tubes',
                'brand'        => 'Michelin',
                'cost_price'   => 350,
                'selling_price'=> 550,
                'has_variants' => false,
                'stock'        => 50,
            ],
            [
                'name'         => 'Alloy Rim 15"',
                'category'     => 'Rims & Wheels',
                'brand'        => 'Bridgestone',
                'cost_price'   => 3200,
                'selling_price'=> 4500,
                'has_variants' => false,
                'stock'        => 8,
            ],
            [
                'name'         => 'Valve Stem Set',
                'category'     => 'Accessories',
                'brand'        => 'Continental',
                'cost_price'   => 50,
                'selling_price'=> 100,
                'has_variants' => false,
                'stock'        => 200,
            ],
        ];

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            foreach ($catalog as $item) {
                $category = ProductCategory::where('tenant_id', $tenant->id)
                    ->where('slug', Str::slug($item['category']))
                    ->first();

                $brand = Brand::where('tenant_id', $tenant->id)
                    ->where('brand_slug', Str::slug($item['brand']))
                    ->first();

                if (! $category) continue;

                $product = Product::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'sku'       => 'SKU-' . strtoupper(Str::slug($item['name'])) . '-' . $tenant->id,
                    ],
                    [
                        'category_id'    => $category->id,
                        'brand_id'       => $brand?->id,
                        'name'           => $item['name'],
                        'slug'           => Str::slug($item['name']) . '-' . $tenant->id,
                        'barcode'        => 'BC-' . rand(100000, 999999),
                        'cost_price'     => $item['cost_price'],
                        'selling_price'  => $item['selling_price'],
                        'stock'          => $item['has_variants'] ? 0 : $item['stock'],
                        'low_stock_alert'=> 5,
                        'description'    => "High-quality {$item['name']} suitable for a wide range of vehicles.",
                    ]
                );

                // Variants
                if ($item['has_variants'] ?? false) {
                    $sizeAttr = AttributeValue::whereHas('attribute', fn ($q) =>
                        $q->where('tenant_id', $tenant->id)->where('name', 'Size')
                    )->get()->keyBy('value');

                    foreach ($item['variants'] as $v) {
                        $variant = Variant::updateOrCreate(
                            [
                                'tenant_id' => $tenant->id,
                                'sku'       => 'VAR-' . strtoupper(Str::slug($v['size'])) . '-' . $product->id,
                            ],
                            [
                                'product_id'     => $product->id,
                                'price'          => $v['price'],
                                'stock_quantity' => $v['stock'],
                            ]
                        );

                        if (isset($sizeAttr[$v['size']])) {
                            $variant->attributeValues()->syncWithoutDetaching([$sizeAttr[$v['size']]->id]);
                        }
                    }

                    // Observer will sync products.stock from variants
                    $product->refresh()->syncStockFromVariants();
                }
            }
        }
    }
}