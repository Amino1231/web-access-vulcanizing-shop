<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->take(4)->get();
            if ($products->isEmpty()) continue;

            $statuses = [
                OrderStatus::Pending->value,
                OrderStatus::Processing->value,
                OrderStatus::Ready->value,
                OrderStatus::Completed->value,
                OrderStatus::Cancelled->value,
            ];

            foreach ($customers as $customer) {
                foreach ($statuses as $status) {
                    $p = $products->random();
                    $qty = rand(1, 2);
                    $lineTotal = $p->selling_price * $qty;

                    $order = Order::create([
                        'tenant_id'    => $tenant->id,
                        'customer_id'  => $customer->id,
                        'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                        'status'       => $status,
                        'subtotal'     => $lineTotal,
                        'discount'     => 0,
                        'tax'          => 0,
                        'total'        => $lineTotal,
                        'payment_method' => 'cash_on_pickup',
                        'notes'        => 'Customer will pick up in store.',
                        'processed_at' => in_array($status, [OrderStatus::Processing->value, OrderStatus::Ready->value, OrderStatus::Completed->value]) ? now()->subDays(rand(1, 5)) : null,
                        'completed_at' => $status === OrderStatus::Completed->value ? now()->subDays(rand(1, 3)) : null,
                        'cancelled_at' => $status === OrderStatus::Cancelled->value ? now()->subDays(rand(1, 3)) : null,
                        'cancellation_reason' => $status === OrderStatus::Cancelled->value ? 'Customer cancelled.' : null,
                    ]);

                    OrderItem::create([
                        'tenant_id'     => $tenant->id,
                        'order_id'      => $order->id,
                        'product_id'    => $p->id,
                        'variant_id'    => null,
                        'product_name'  => $p->name,
                        'variant_label' => null,
                        'sku'           => $p->sku,
                        'unit_price'    => $p->selling_price,
                        'quantity'      => $qty,
                        'line_total'    => $lineTotal,
                    ]);
                }
            }
        }
    }
}