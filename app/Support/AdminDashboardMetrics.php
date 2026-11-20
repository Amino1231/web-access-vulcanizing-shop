<?php

namespace App\Support;

use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;

class AdminDashboardMetrics
{
    public function kpis(): array
    {
        $totalUsers = User::count();
        $totalOwners = Tenant::distinct('user_id')->count('user_id');
        $totalCustomers = max(0, $totalUsers - $totalOwners); // Assuming anyone not an owner is a customer
        $totalShops = Tenant::count();
        $totalReviews = Review::count();

        return [
            [
                'label' => 'Total Users',
                'value' => number_format($totalUsers),
                'tone'  => 'blue',
            ],
            [
                'label' => 'Customers',
                'value' => number_format($totalCustomers),
                'tone'  => 'orange',
            ],
            [
                'label' => 'Shop Owners',
                'value' => number_format($totalOwners),
                'tone'  => 'emerald',
            ],
            [
                'label' => 'Total Shops',
                'value' => number_format($totalShops),
                'tone'  => 'amber',
            ],
            [
                'label' => 'Total Reviews',
                'value' => number_format($totalReviews),
                'tone'  => 'red',
            ],
        ];
    }

    public function topShops(int $limit = 5): array
    {
        return Tenant::query()
            ->with('user:id,name')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_count')
            ->orderByDesc('reviews_avg_rating')
            ->take($limit)
            ->get()
            ->map(fn (Tenant $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'logo'         => $t->logo ? asset('storage/'.$t->logo) : null,
                'owner'        => $t->user?->name ?? 'Unassigned',
                'reviews'      => $t->reviews_count,
                'rating'       => $t->reviews_avg_rating ? round((float) $t->reviews_avg_rating, 1) : null,
            ])
            ->toArray();
    }
}