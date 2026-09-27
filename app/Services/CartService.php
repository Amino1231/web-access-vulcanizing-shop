<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    public const SESSION_KEY = 'cart';

    public static function all(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function add(int $productId, ?int $variantId = null, int $qty = 1): void
    {
        $cart = self::all();
        $key = self::key($productId, $variantId);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => $qty,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    public static function update(string $key, int $qty): void
    {
        $cart = self::all();

        if ($qty <= 0) {
            unset($cart[$key]);
        } elseif (isset($cart[$key])) {
            $cart[$key]['quantity'] = $qty;
        }

        session([self::SESSION_KEY => $cart]);
    }

    public static function remove(string $key): void
    {
        $cart = self::all();
        unset($cart[$key]);
        session([self::SESSION_KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function count(): int
    {
        return (int) array_sum(array_column(self::all(), 'quantity'));
    }

    public static function hydrated(): Collection
    {
        $cart = self::all();

        if (empty($cart)) {
            return collect();
        }

        $productIds = array_values(array_unique(array_column($cart, 'product_id')));
        
        $products = Product::query()
            ->withoutGlobalScope('tenant')
            ->with([
                'tenant:id,name',
                'variants' => fn ($q) => $q->withoutGlobalScope('tenant')
                    ->select('id', 'product_id', 'sku', 'price', 'stock_quantity'),
            ])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function ($row, $key) use ($products) {
            $product = $products->get($row['product_id']);

            if (! $product) {
                return null;
            }

            $variant = $row['variant_id']
                ? $product->variants->firstWhere('id', $row['variant_id'])
                : null;

            $unitPrice      = (float) ($variant?->price ?? $product->selling_price);
            $availableStock = (int) ($variant?->stock_quantity ?? $product->stock);
            $qty            = min((int) $row['quantity'], max($availableStock, 0));

            return [
                'key'             => $key,
                'product'         => $product,
                'variant'         => $variant,
                'quantity'        => $qty,
                'unit_price'      => $unitPrice,
                'line_total'      => round($unitPrice * $qty, 2),
                'available_stock' => $availableStock,
            ];
        })->filter()->values();
    }

    public static function key(int $productId, ?int $variantId): string
    {
        return $variantId ? "p{$productId}-v{$variantId}" : "p{$productId}";
    }
}