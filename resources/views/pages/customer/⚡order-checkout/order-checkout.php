<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderPlaced;
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

    /** Keys of cart rows the customer has selected to pay for. */
    public array $selectedKeys = [];

    public function mount(): void
    {
        // Default: everything selected
        $this->selectedKeys = $this->cart->pluck('key')->all();
    }

    #[Computed]
    public function cart()
    {
        return CartService::hydrated();
    }

    /** Just the rows that will be ordered. */
    #[Computed]
    public function selectedCart()
    {
        return $this->cart->filter(fn ($row) => in_array($row['key'], $this->selectedKeys, true));
    }

    #[Computed]
    public function totals(): array
    {
        $subtotal = round((float) $this->selectedCart->sum('line_total'), 2);
        $tax      = round($subtotal * 0.12, 2);

        return [
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'discount' => 0.0,
            'total'    => round($subtotal + $tax, 2),
            'count'    => $this->selectedCart->count(),
        ];
    }

    #[Computed]
    public function isAllSelected(): bool
    {
        $all = $this->cart->pluck('key')->all();

        return count($all) > 0 && count($this->selectedKeys) === count($all);
    }

    // ─────────────────────────────────────────
    // Selection actions
    // ─────────────────────────────────────────

    public function toggleSelect(string $key): void
    {
        if (in_array($key, $this->selectedKeys, true)) {
            $this->selectedKeys = array_values(array_diff($this->selectedKeys, [$key]));
        } else {
            $this->selectedKeys[] = $key;
        }

        unset($this->selectedCart, $this->totals, $this->isAllSelected);
    }

    public function toggleSelectAll(): void
    {
        if ($this->isAllSelected) {
            $this->selectedKeys = [];
        } else {
            $this->selectedKeys = $this->cart->pluck('key')->all();
        }

        unset($this->selectedCart, $this->totals, $this->isAllSelected);
    }

    // ─────────────────────────────────────────
    // Cart edits
    // ─────────────────────────────────────────

    public function updateQuantity(string $key, int $qty): void
    {
        CartService::update($key, max(1, $qty));
        $this->dispatch('cart-updated');

        unset($this->cart, $this->selectedCart, $this->totals, $this->isAllSelected);
    }

    public function removeItem(string $key): void
    {
        CartService::remove($key);
        $this->selectedKeys = array_values(array_diff($this->selectedKeys, [$key]));
        $this->dispatch('cart-updated');

        unset($this->cart, $this->selectedCart, $this->totals, $this->isAllSelected);
    }

    // ─────────────────────────────────────────
    // Place order
    // ─────────────────────────────────────────

    public function placeOrder()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $selected = $this->selectedCart;

        if ($selected->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'Please select at least one item to order.');
            return;
        }

        // Single-shop check (on selected items only)
        $tenantIds = $selected->pluck('product.tenant_id')->unique();

        if ($tenantIds->count() > 1) {
            $this->dispatch('notify', type: 'error', message: 'Please order from one shop at a time.');
            return;
        }

        // Stock check (on selected items only)
        foreach ($selected as $row) {
            if ($row['available_stock'] < $row['quantity']) {
                $this->dispatch('notify', type: 'error', message: "Not enough stock for {$row['product']->name}.");
                return;
            }
        }

        $totals   = $this->totals;
        $tenantId = $tenantIds->first();

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'Could not resolve shop for this order.');
            return;
        }

        try {
            $order = DB::transaction(function () use ($user, $selected, $totals, $tenantId) {
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

                $items = $selected->map(fn ($row) => [
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

            // ─── Notify the shop owner ─────────────
            $order->loadMissing('customer:id,name', 'tenant.user:id,name,email');
            $order->tenant?->user?->notify(new OrderPlaced($order));

            // Remove ONLY the ordered items from cart — keep the rest
            foreach ($this->selectedKeys as $key) {
                CartService::remove($key);
            }

            $this->selectedKeys = [];
            $this->dispatch('cart-updated');

            session()->flash('order_placed', $order->order_number);

            return redirect()->route('customer.orders');
        } catch (\Throwable $e) {
            \Log::error('CHECKOUT FAILED', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            session()->flash('checkout_error', $e->getMessage() . ' — ' . $e->getFile() . ':' . $e->getLine());

            return redirect()->route('customer.checkout');
        }
    }
};