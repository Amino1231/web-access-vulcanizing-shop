<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public int $productId;
    public ?int $selectedVariantId = null;
    public int $quantity = 1;

    public function mount(int $product): void
    {
        $this->productId = $product;
    }

    #[Computed]
    public function product(): ?Product
    {
        return Product::query()
            ->withoutGlobalScope('tenant')
            ->with([
                'category:id,name',
                'tenant:id,name',
                'variants' => fn ($q) => $q->withoutGlobalScope('tenant')
                    ->select('id', 'product_id', 'sku', 'price', 'stock_quantity'),
            ])
            ->where('stock', '>', 0)
            ->find($this->productId);
    }

    #[Computed]
    public function related()
    {
        $current = $this->product;

        if (! $current) {
            return collect();
        }

        return Product::query()
            ->withoutGlobalScope('tenant')
            ->with('tenant:id,name')
            ->select(['id', 'tenant_id', 'name', 'selling_price', 'ft_img'])
            ->where('tenant_id', $current->tenant_id)
            ->where('id', '!=', $current->id)
            ->where('stock', '>', 0)
            ->whereNotNull('ft_img')
            ->limit(4)
            ->get();
    }

    public function selectVariant(int $variantId): void
    {
        $this->selectedVariantId = $this->selectedVariantId === $variantId ? null : $variantId;
        $this->quantity = 1;
    }

    public function addToCart(): void
    {
        $product = $this->product;

        if (! $product) {
            $this->dispatch('notify', type: 'error', message: 'Product unavailable.');
            return;
        }

        $available = $this->selectedVariantId
            ? ($product->variants->firstWhere('id', $this->selectedVariantId)?->stock_quantity ?? 0)
            : $product->stock;

        if ($available < $this->quantity) {
            $this->dispatch('notify', type: 'error', message: 'Not enough stock.');
            return;
        }

        CartService::add($product->id, $this->selectedVariantId, $this->quantity);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'success', message: 'Added to cart.');
    }

    public function buyNow(): void
    {
        $this->addToCart();
        $this->redirectRoute('customer.checkout');
    }
};
?>
