<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Review;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            5 => [
                'Fast and professional service. Highly recommended!',
                'Great work on my tires, will definitely come back.',
                'Best vulcanizing shop in town. Five stars!',
            ],
            4 => [
                'Good work on my tires, will come back.',
                'Reasonable prices and friendly staff.',
                'Quick turnaround time, no complaints.',
            ],
            3 => [
                'Service was fine but waited longer than expected.',
                'Okay quality — nothing special.',
            ],
            2 => [
                'Slow service. Had to wait almost an hour.',
                'Overpriced for what I got.',
            ],
            1 => [
                'Terrible experience. Damaged my rim and denied it.',
                'Never coming back. Rude staff and bad work.',
            ],
        ];

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $sales     = Sale::where('tenant_id', $tenant->id)->get();
            $services  = Service::where('tenant_id', $tenant->id)->get();
            $employees = Employee::where('tenant_id', $tenant->id)->get();

            // Sale-based reviews
            foreach ($sales as $saleIndex => $sale) {
                if (! $sale->customer_id) continue;

                $rating = match ($saleIndex % 5) {
                    0 => 5,
                    1 => 4,
                    2 => 5,
                    3 => 2,
                    4 => 1,
                };

                $review = Review::updateOrCreate(
                    [
                        'tenant_id'   => $tenant->id,
                        'customer_id' => $sale->customer_id,
                        'sale_id'     => $sale->id,
                    ],
                    [
                        'service_id'  => $services->random()?->id,
                        'employee_id' => $employees->random()?->id,
                        'rating'      => $rating,
                        'review'      => collect($messages[$rating])->random(),
                        'is_hidden'   => false,
                        'flagged_at'  => null,
                        'hidden_at'   => null,
                    ]
                );

                if ($saleIndex === 3) {
                    $review->update(['flagged_at' => now()->subDays(2)]);
                }

                if ($saleIndex === 4) {
                    $review->update([
                        'is_hidden'  => true,
                        'hidden_at'  => now()->subDays(1),
                        'flagged_at' => now()->subDays(2),
                    ]);
                }
            }

            // Product reviews (verified buyers)
            foreach ($sales->take(3) as $sale) {
                foreach ($sale->items->whereNotNull('product_id') as $item) {
                    $exists = Review::where('customer_id', $sale->customer_id)
                        ->where('product_id', $item->product_id)
                        ->exists();

                    if ($exists) continue;

                    $rating = rand(3, 5);

                    Review::create([
                        'tenant_id'   => $tenant->id,
                        'customer_id' => $sale->customer_id,
                        'product_id'  => $item->product_id,
                        'rating'      => $rating,
                        'review'      => collect($messages[$rating])->random(),
                    ]);
                }
            }
        }
    }
}