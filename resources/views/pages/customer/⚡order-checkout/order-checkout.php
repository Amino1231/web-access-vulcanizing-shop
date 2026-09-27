<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public string $payment_method = 'cash_on_pickup';
    public string $notes = '';

    #[Computed]
    public function cart()
    {
        return CartService::hydrated();
    }

    #[Computed]
    public function totals(): array
    {
        $subtotal = round((float) $this->cart->sum('line_total'), 2);
        $tax = round($subtotal * 0.12, 2);

        return [
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'discount' => 0.0,
            'total'    => round($subtotal + $tax, 2),
        ];
    }

    public function updateQuantity(string $key, int $qty): void
    {
        CartService::update($key, $qty);
        $this->dispatch('cart-updated');
    }

    public function removeItem(string $key): void
    {
        CartService::remove($key);
        $this->dispatch('cart-updated');
    }

    public function placeOrder()
    {
         \Log::info('placeOrder invoked', [
        'user_id' => Auth::id(),
        'raw_cart_session' => session('cart'),
    ]);

    
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $cart = $this->cart;

        if ($cart->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'Your cart is empty.');
            return;
        }

        $tenantIds = $cart->pluck('product.tenant_id')->unique();

        if ($tenantIds->count() > 1) {
            $this->dispatch('notify', type: 'error', message: 'Please order from one shop at a time.');
            return;
        }

        foreach ($cart as $row) {
            if ($row['available_stock'] < $row['quantity']) {
                $this->dispatch('notify', type: 'error', message: "Not enough stock for {$row['product']->name}.");
                return;
            }
        }

        $totals = $this->totals;
        $tenantId = $tenantIds->first();

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'Could not resolve shop for this order.');
            return;
        }

        try {
            $order = DB::transaction(function () use ($user, $cart, $totals, $tenantId) {
                $order = Order::create([
                    'tenant_id'      => $tenantId,
                    'customer_id'    => $user->id,
                    'order_number'   => Order::generateOrderNumber(),
                    'status'         => OrderStatus::Pending,
                    'subtotal'       => $totals['subtotal'],
                    'discount'       => $totals['discount'],
                    'tax'            => $totals['tax'],
                    'total'          => $totals['total'],
                    'payment_method' => $this->payment_method,
                    'notes'          => $this->notes ?: null,
                ]);

                $items = $cart->map(fn ($row) => [
                    'tenant_id'     => $tenantId,
                    'order_id'      => $order->id,
                    'product_id'    => $row['product']->id,
                    'variant_id'    => $row['variant']?->id,
                    'product_name'  => $row['product']->name,
                    'variant_label' => $row['variant']?->sku,
                    'sku'           => $row['variant']?->sku ?? $row['product']->sku,
                    'unit_price'    => $row['unit_price'],
                    'quantity'      => $row['quantity'],
                    'line_total'    => $row['line_total'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ])->all();

                OrderItem::insert($items);

                return $order;
            });

            CartService::clear();
            $this->dispatch('cart-updated');

            session()->flash('order_placed', $order->order_number);

            return redirect()->route('customer.orders');
      } catch (\Throwable $e) {
    \Log::error('CHECKOUT FAILED', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
    ]);

    session()->flash('checkout_error', $e->getMessage() . ' — ' . $e->getFile() . ':' . $e->getLine());

    return redirect()->route('customer.checkout');
}
    }
};
?>

