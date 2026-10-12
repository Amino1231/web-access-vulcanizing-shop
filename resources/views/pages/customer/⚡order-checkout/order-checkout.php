<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderPlaced;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Luigel\Paymongo\Facades\Paymongo;

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
        $tax = round($subtotal * 0.12, 2);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0.0,
            'total' => round($subtotal + $tax, 2),
            'count' => $this->selectedCart->count(),
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

        // Stock check on every selected item before we do anything
        foreach ($selected as $row) {
            if ($row['available_stock'] < $row['quantity']) {
                $this->dispatch('notify', type: 'error', message: "Not enough stock for {$row['product']->name}.");

                return;
            }
        }

        // Group selected items by shop
        $grouped = $selected->groupBy(fn ($row) => $row['product']->tenant_id);

        if ($grouped->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'Could not resolve shop for this order.');

            return;
        }

        $isOnlinePayment = str_starts_with($this->payment_method, 'paymongo_');

        $initialPaymentStatus = OrderStatus::initialPaymentStatus($this->payment_method);
        $initialPaidAt = $initialPaymentStatus === 'paid' ? now() : null;

        try {
            // ── Create one order per shop, in one transaction ──
            $orders = DB::transaction(function () use ($user, $grouped, $initialPaymentStatus, $initialPaidAt) {
                $created = collect();

                foreach ($grouped as $tenantId => $rows) {
                    $subtotal = round((float) $rows->sum('line_total'), 2);
                    $tax = round($subtotal * 0.12, 2);
                    $total = round($subtotal + $tax, 2);

                    $order = Order::create([
                        'tenant_id' => $tenantId,
                        'customer_id' => $user->id,
                        'order_number' => Order::generateOrderNumber(),
                        'status' => OrderStatus::Pending,
                        'subtotal' => $subtotal,
                        'discount' => 0,
                        'tax' => $tax,
                        'total' => $total,
                        'payment_method' => $this->payment_method,
                        'payment_status' => $initialPaymentStatus,
                        'paid_at' => $initialPaidAt,
                        'notes' => $this->notes ?: null,
                    ]);

                    $items = $rows->map(fn ($row) => [
                        'tenant_id' => $tenantId,
                        'order_id' => $order->id,
                        'product_id' => $row['product']->id,
                        'variant_id' => $row['variant']?->id,
                        'product_name' => $row['product']->name,
                        'variant_label' => $row['variant']?->sku,
                        'sku' => $row['variant']?->sku ?? $row['product']->sku,
                        'unit_price' => $row['unit_price'],
                        'quantity' => $row['quantity'],
                        'line_total' => $row['line_total'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all();

                    OrderItem::insert($items);

                    $created->push($order);
                }

                return $created;
            });

            // ── Notify each shop owner ──
            foreach ($orders as $order) {
                $order->loadMissing('customer:id,name', 'tenant.user:id,name,email');
                $order->tenant?->user?->notify(new OrderPlaced($order));
            }

            // ── Clear the ordered items from cart ──
            foreach ($this->selectedKeys as $key) {
                CartService::remove($key);
            }
            $this->selectedKeys = [];
            $this->dispatch('cart-updated');

            // ── Online payment: only if there's exactly one order ──
            // PayMongo checkout sessions support one line-item group; sending
            // multiple shops to one session isn't feasible, so multi-shop
            // orders go straight to the "Orders placed" summary and the shop
            // owners reach out / the customer pays later.
            if ($isOnlinePayment && $orders->count() === 1) {
                return $this->redirectToPayMongo($orders->first());
            }

            // ── Multiple orders or cash/bank transfer → success page ──
            session()->flash('order_placed_count', $orders->count());
            session()->flash('order_placed_numbers', $orders->pluck('order_number')->all());

            // If there's only one order, still take them to its receipt
            if ($orders->count() === 1) {
                return redirect()->route('customer.order_receipt', $orders->first()->id);
            }

            // Multiple orders → send to orders list with a flash
            return redirect()->route('customer.orders');
        } catch (Throwable $e) {
            Log::error('CHECKOUT FAILED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            session()->flash('checkout_error', $e->getMessage().' — '.$e->getFile().':'.$e->getLine());

            return redirect()->route('customer.checkout');
        }
    }

    /**
     * Create a PayMongo checkout session and redirect the customer there.
     * The cart is cleared here BEFORE redirecting — the order already exists
     * as unpaid, so if the customer abandons checkout they can still pay later.
     */
    protected function redirectToPayMongo(Order $order)
    {
        try {
            $channel = match ($this->payment_method) {
                'paymongo_gcash' => 'gcash',
                'paymongo_card' => 'card',
                'paymongo_maya' => 'maya',
                default => 'gcash',
            };

            $session = Paymongo::checkout()->create([
                'line_items' => [[
                    'name' => 'Order '.$order->order_number,
                    'amount' => (int) round($order->total * 100), // centavos
                    'currency' => 'PHP',
                    'quantity' => 1,
                    'description' => 'Payment for order '.$order->order_number,
                ]],
                'payment_method_types' => [$channel],
                'success_url' => route('customer.checkout.success', $order->id),
                'cancel_url' => route('customer.checkout.cancel', $order->id),
                'reference_number' => $order->order_number,
            ]);

            $checkoutUrl = $session->getCheckoutUrl();
            $sessionId = $session->getData()['id'] ?? null;

            $order->update([
                'payment_reference' => $sessionId,
                'payment_provider' => 'paymongo',
                'payment_channel' => $channel,
            ]);

            // ─── Clear the ordered items from the cart now ───
            foreach ($this->selectedKeys as $key) {
                CartService::remove($key);
            }
            $this->selectedKeys = [];
            $this->dispatch('cart-updated');

            return redirect()->to($checkoutUrl);
        } catch (Throwable $e) {
            Log::error('PAYMONGO SESSION FAILED', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            session()->flash('checkout_error', 'Order #'.$order->order_number.' was created, but we could not start the payment session. Please contact the shop.');

            return redirect()->route('customer.order_receipt', $order->id);
        }
    }
};
