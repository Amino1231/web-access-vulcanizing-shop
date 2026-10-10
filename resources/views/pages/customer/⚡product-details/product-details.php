<?php

use App\Models\Product;
use App\Models\Review;
use App\Services\CartService;
use App\Services\ProductReviewService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public int $productId;

    public ?int $selectedVariantId = null;

    public int $quantity = 1;

    // Review form state
    public int $reviewRating = 5;

    public string $reviewText = '';

    public string $reviewMessage = '';

    public function mount(int $product): void
    {
        $this->productId = $product;
    }

    // ─────────────────────────────────────────
    // Product
    // ─────────────────────────────────────────

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

    // ─────────────────────────────────────────
    // Reviews
    // ─────────────────────────────────────────

    #[Computed]
    public function reviews()
    {
        return Review::query()
            ->where('product_id', $this->productId)
            ->with(['customer:id,name'])
            ->latest()
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function reviewStats(): array
    {
        $base = Review::query()
            ->where('product_id', $this->productId)
            ->visible();

        $count = (clone $base)->count();

        if ($count === 0) {
            return [
                'count' => 0,
                'average' => 0.0,
                'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            ];
        }

        $avg = round((float) (clone $base)->avg('rating'), 1);

        $breakdown = (clone $base)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        return [
            'count' => $count,
            'average' => $avg,
            'breakdown' => [
                5 => (int) ($breakdown[5] ?? 0),
                4 => (int) ($breakdown[4] ?? 0),
                3 => (int) ($breakdown[3] ?? 0),
                2 => (int) ($breakdown[2] ?? 0),
                1 => (int) ($breakdown[1] ?? 0),
            ],
        ];
    }

    #[Computed]
    public function canReview(): bool
    {
        $user = Auth::user();
        $product = $this->product;

        if (! $user || ! $product) {
            return false;
        }

        return app(ProductReviewService::class)->canReview($user, $product);
    }

    #[Computed]
    public function reviewBlockReason(): string
    {
        $user = Auth::user();
        $product = $this->product;

        if (! $user || ! $product) {
            return 'Sign in to leave a review.';
        }

        return app(ProductReviewService::class)->eligibilityReason($user, $product);
    }

    public function submitReview(ProductReviewService $service): void
    {
        $user = Auth::user();
        $product = $this->product;

        if (! $user || ! $product) {
            $this->reviewMessage = 'Please sign in to leave a review.';

            return;
        }

        if (! $service->canReview($user, $product)) {
            $this->reviewMessage = $service->eligibilityReason($user, $product);

            return;
        }

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewText' => ['nullable', 'string', 'min:3', 'max:1000'],
        ]);

        Review::create([
            'tenant_id' => $product->tenant_id,
            'customer_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $this->reviewRating,
            'review' => trim($this->reviewText) ?: null,
        ]);

        $this->reviewRating = 5;
        $this->reviewText = '';
        $this->reviewMessage = 'Thanks! Your review has been posted.';

        unset($this->reviews, $this->reviewStats, $this->canReview, $this->reviewBlockReason);
    }
};
