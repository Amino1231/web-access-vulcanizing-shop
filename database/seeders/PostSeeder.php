<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            // Post for each product
            foreach (Product::where('tenant_id', $tenant->id)->take(3)->get() as $product) {
                Post::updateOrCreate(
                    [
                        'tenant_id'  => $tenant->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'service_id'  => null,
                        'name'        => $product->name,
                        'slug'        => Str::slug($product->name) . '-post-' . $tenant->id,
                        'price'       => $product->selling_price,
                        'description' => $product->description,
                        'image'       => $product->ft_img,
                        'status'      => PostStatus::Published->value,
                    ]
                );
            }

            // Post for each service
            foreach (Service::where('tenant_id', $tenant->id)->take(3)->get() as $service) {
                Post::updateOrCreate(
                    [
                        'tenant_id'  => $tenant->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'product_id'  => null,
                        'name'        => $service->name,
                        'slug'        => Str::slug($service->name) . '-post-' . $tenant->id,
                        'price'       => $service->price,
                        'description' => $service->description,
                        'status'      => PostStatus::Published->value,
                    ]
                );
            }
        }
    }
}