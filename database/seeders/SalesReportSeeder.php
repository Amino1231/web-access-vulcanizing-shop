<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();

        if ($customers->isEmpty()) {
            $this->command?->warn('No customers found. Run your customer seeder first.');
            return;
        }

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->get();
            $services = Service::where('tenant_id', $tenant->id)->get();

            if ($products->isEmpty() && $services->isEmpty()) {
                $this->command?->warn("Tenant {$tenant->id} ({$tenant->name}) has no products or services — skipping.");
                continue;
            }

            $commissionRate = $tenant->commissionRate();

            Sale::where('tenant_id', $tenant->id)
                ->where('invoice_number', 'like', 'INV-RPT-%')
                ->delete();

            $start = now()->copy()->subYear()->startOfDay();
            $end   = now()->copy()->endOfDay();

            $cursor     = $start->copy();
            $invoiceSeq = 1;
            $totalSales = 0;

            while ($cursor->lte($end)) {
                $salesToday = $this->salesCountForDay($cursor);

                for ($i = 0; $i < $salesToday; $i++) {
                    $sold = $this->createSale(
                        tenant:         $tenant,
                        customers:      $customers,
                        products:       $products,
                        services:       $services,
                        commissionRate: $commissionRate,
                        at:             $cursor->copy()->setTime(rand(8, 19), rand(0, 59), rand(0, 59)),
                        invoiceNumber:  sprintf('INV-RPT-%d-%05d', $tenant->id, $invoiceSeq++),
                    );

                    if ($sold) {
                        $totalSales++;
                    }
                }

                $cursor->addDay();
            }

            $this->command?->info("Tenant {$tenant->id} ({$tenant->name}): seeded {$totalSales} sales.");
        }
    }

    protected function salesCountForDay(Carbon $day): int
    {
        if ($day->isToday()) {
            return rand(4, 8);
        }

        if ($day->isCurrentWeek()) {
            return rand(3, 6);
        }

        if ($day->isCurrentMonth()) {
            return rand(2, 5);
        }

        return rand(1, 4);
    }

    protected function createSale(
        Tenant $tenant,
        $customers,
        $products,
        $services,
        float $commissionRate,
        Carbon $at,
        string $invoiceNumber,
    ): bool {
        $customer = rand(1, 100) <= 85 ? $customers->random() : null;

        $subtotal  = 0;
        $lineItems = [];

        if ($products->isNotEmpty()) {
            $pickCount = min(rand(1, 3), $products->count());
            $picked    = $products->count() === 1
                ? collect([$products->first()])
                : $products->random($pickCount);

            foreach ($picked as $p) {
                $qty       = rand(1, 3);
                $lineTotal = $p->selling_price * $qty;

                $lineItems[] = [
                    'product_id' => $p->id,
                    'service_id' => null,
                    'item_type'  => 'product',
                    'quantity'   => $qty,
                    'price'      => $p->selling_price,
                    'subtotal'   => $lineTotal,
                ];
                $subtotal += $lineTotal;
            }
        }

        if ($services->isNotEmpty() && rand(0, 1) === 1) {
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

        if (empty($lineItems)) {
            return false;
        }

        $discount = rand(1, 100) <= 20
            ? round($subtotal * (rand(5, 15) / 100), 2)
            : 0.0;

        $tax   = round(($subtotal - $discount) * 0.12, 2);
        $total = round($subtotal - $discount + $tax, 2);

        $platformCommission = round($total * ($commissionRate / 100), 2);
        $shopNetRevenue     = round($total - $platformCommission, 2);

        $sale = Sale::updateOrCreate(
            ['invoice_number' => $invoiceNumber],
            [
                'tenant_id'    => $tenant->id,
                'customer_id'  => $customer?->id,
                'subtotal'     => $subtotal,
                'discount'     => $discount,
                'tax'          => $tax,
                'final_amount' => $total,

                'platform_commission'     => $platformCommission,
                'shop_net_revenue'        => $shopNetRevenue,
                'commission_rate_applied' => $commissionRate,

                'payment_method' => collect(['cash', 'gcash', 'card'])->random(),
                'status'         => 'paid',
                'created_at'     => $at,
                'updated_at'     => $at,
            ]
        );

        $sale->items()->delete();

        foreach ($lineItems as $li) {
            SaleItem::create(array_merge($li, [
                'sale_id'    => $sale->id,
                'created_at' => $at,
                'updated_at' => $at,
            ]));
        }

        return true;
    }
}