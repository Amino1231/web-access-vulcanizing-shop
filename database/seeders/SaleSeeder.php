<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->take(3)->get();
            $services = Service::where('tenant_id', $tenant->id)->take(2)->get();

            // Resolve commission rate for this shop (defaults to 10%)
            $commissionRate = $tenant->commissionRate();

            for ($i = 0; $i < 5; $i++) {
                $createdAt = now()->subMonths(rand(0, 5))->subDays(rand(1, 20));
                $customer  = $customers->random();

                $subtotal  = 0;
                $lineItems = [];

                if ($products->isNotEmpty()) {
                    $p   = $products->random();
                    $qty = rand(1, 3);

                    $lineItems[] = [
                        'product_id' => $p->id,
                        'service_id' => null,
                        'item_type'  => 'product',
                        'quantity'   => $qty,
                        'price'      => $p->selling_price,
                        'subtotal'   => $p->selling_price * $qty,
                    ];
                    $subtotal += $p->selling_price * $qty;
                }

                if ($services->isNotEmpty()) {
                    $s = $services->random();

                    $lineItems[] = [
                        'product_id' => null,
                        'service_id' => $s->id,
                        'item_type'  => 'service',
                        'quantity'   => 1,
                        'price'      => $s->price,
                        'subtotal'   => $s->price,
                    ];
                    $subtotal += $s->price;
                }

                $discount = 0;
                $tax      = 0;
                $total    = $subtotal - $discount + $tax;

                // ─── Commission split ───────────────────
                $platformCommission = round($total * ($commissionRate / 100), 2);
                $shopNetRevenue     = round($total - $platformCommission, 2);

                $sale = Sale::updateOrCreate(
                    ['invoice_number' => 'INV-SEED-' . $tenant->id . '-' . $i],
                    [
                        'tenant_id'      => $tenant->id,
                        'customer_id'    => $customer->id,
                        'subtotal'       => $subtotal,
                        'discount'       => $discount,
                        'tax'            => $tax,
                        'final_amount'   => $total,

                        // ─── Commission columns ────
                        'platform_commission'      => $platformCommission,
                        'shop_net_revenue'         => $shopNetRevenue,
                        'commission_rate_applied'  => $commissionRate,

                        'payment_method' => collect(['cash', 'gcash', 'card'])->random(),
                        'status'         => 'paid',
                        'created_at'     => $createdAt,
                        'updated_at'     => $createdAt,
                    ]
                );

                // Recreate sale items
                $sale->items()->delete();

                foreach ($lineItems as $li) {
                    SaleItem::create(array_merge($li, [
                        'sale_id'    => $sale->id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]));
                }
            }
        }
    }
}