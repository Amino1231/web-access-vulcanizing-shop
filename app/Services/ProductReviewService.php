<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ProductReviewService
{
    /**
     * Has this customer already reviewed this product?
     */
    public function hasReviewed(User $user, Product $product): bool
    {
        return Review::query()
            ->where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Has this customer purchased and completed an order for this product?
     */
    public function hasPurchased(User $user, Product $product): bool
    {
        return Order::query()
            ->where('customer_id', $user->id)
            ->where('status', 'completed')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }

    /**
     * Can this customer leave a review right now?
     */
    public function canReview(User $user, Product $product): bool
    {
        return $this->hasPurchased($user, $product)
            && ! $this->hasReviewed($user, $product);
    }

    /**
     * Return the "reason" a customer can't review — useful for UI messaging.
     */
    public function eligibilityReason(User $user, Product $product): string
    {
        if (! $this->hasPurchased($user, $product)) {
            return 'Only verified buyers can review this product.';
        }

        if ($this->hasReviewed($user, $product)) {
            return 'You have already reviewed this product.';
        }

        return '';
    }
}