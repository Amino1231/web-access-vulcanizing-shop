<?php

namespace App\Support;

use App\Enums\VerificationStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Tenant;
use Carbon\Carbon;

class AdminDashboardMetrics
{
    // ─────────────────────────────────────────────
    // KPIs — supply side (shops)
    // ─────────────────────────────────────────────

    public function kpis(): array
    {
        $total = Tenant::count();
        $approved = Tenant::where('verification_status', VerificationStatus::Approved->value)->count();
        $activeShops = Tenant::where('is_active', true)
            ->where('verification_status', VerificationStatus::Approved->value)
            ->count();
        $pending = Tenant::whereIn('verification_status', [
            VerificationStatus::Pending->value,
            VerificationStatus::UnderReview->value,
            VerificationStatus::NeedsMoreInfo->value,
        ])->count();
        $rejected = Tenant::where('verification_status', VerificationStatus::Rejected->value)->count();

        return [
            [
                'label' => 'Registered tenants',
                'value' => (string) $total,
                'delta' => $this->delta(Tenant::class, 'created_at'),
                'tone'  => 'orange',
            ],
            [
                'label' => 'Active shops',
                'value' => (string) $activeShops,
                'delta' => $this->delta(Tenant::class, 'created_at', fn ($q) => $q
                    ->where('is_active', true)
                    ->where('verification_status', VerificationStatus::Approved->value)
                ),
                'tone' => 'green',
            ],
            [
                'label' => 'Pending checks',
                'value' => (string) $pending,
                'delta' => $this->delta(Tenant::class, 'created_at', fn ($q) => $q
                    ->whereIn('verification_status', [
                        VerificationStatus::Pending->value,
                        VerificationStatus::UnderReview->value,
                        VerificationStatus::NeedsMoreInfo->value,
                    ])
                ),
                'tone' => 'amber',
            ],
            [
                'label' => 'Rejected apps',
                'value' => (string) $rejected,
                'delta' => $this->delta(Tenant::class, 'created_at', fn ($q) => $q
                    ->where('verification_status', VerificationStatus::Rejected->value)
                ),
                'tone' => 'red',
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // Money KPIs — GMV, orders, AOV, customers
    // ─────────────────────────────────────────────

    public function moneyKpis(): array
    {
        $now            = now();
        $monthStart     = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $monthStart->copy()->subSecond();

        // All-time GMV
        $totalGmv = (float) Sale::whereIn('status', ['paid', 'completed'])->sum('final_amount');

        // This month vs last month GMV
        $thisMonthGmv = (float) Sale::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$monthStart, $now])
            ->sum('final_amount');

        $lastMonthGmv = (float) Sale::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('final_amount');

        // Orders this month
        $thisMonthOrders = Order::whereBetween('created_at', [$monthStart, $now])->count();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        // Total sales count (all-time)
        $totalSales = Sale::whereIn('status', ['paid', 'completed'])->count();

        // Active customers = distinct customers with at least one order
        $activeCustomers = (int) Order::distinct('customer_id')->count('customer_id');

        // Avg order value (this month)
        $aov = $thisMonthOrders > 0 ? round($thisMonthGmv / $thisMonthOrders, 2) : 0.0;

        return [
            [
                'label' => 'Platform GMV',
                'value' => '₱'.number_format($totalGmv, 2),
                'delta' => $this->moneyDelta($thisMonthGmv, $lastMonthGmv),
                'tone'  => 'green',
            ],
            [
                'label' => 'Orders this month',
                'value' => (string) $thisMonthOrders,
                'delta' => $this->moneyDelta($thisMonthOrders, $lastMonthOrders),
                'tone'  => 'orange',
            ],
            [
                'label' => 'Avg order value',
                'value' => '₱'.number_format($aov, 2),
                'delta' => '—',
                'tone'  => 'amber',
            ],
            [
                'label' => 'Active customers',
                'value' => (string) $activeCustomers,
                'delta' => '—',
                'tone'  => 'red',
            ],
        ];
    }

    private function moneyDelta(float|int $current, float|int $previous): string
    {
        if ($previous == 0 && $current == 0) return '—';
        if ($previous == 0) return '+'.number_format((float) $current, 0);

        $pct = round((($current - $previous) / $previous) * 100);

        return ($pct > 0 ? '+' : '').$pct.'%';
    }

    // ─────────────────────────────────────────────
    // Demand-side KPIs — appointments
    // ─────────────────────────────────────────────

    public function appointmentKpis(): array
    {
        $now        = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd   = $now->copy()->endOfDay();

        $today = Appointment::whereBetween('start_time', [$todayStart, $todayEnd])->count();

        $yesterday = Appointment::whereBetween('start_time', [
            $todayStart->copy()->subDay(),
            $todayEnd->copy()->subDay(),
        ])->count();

        $weekStart = $now->copy()->startOfWeek();
        $weekEnd   = $now->copy()->endOfWeek();

        $thisWeek = Appointment::whereBetween('start_time', [$weekStart, $weekEnd])->count();

        $completedThisWeek = Appointment::whereBetween('start_time', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->count();

        $completionRate = $thisWeek > 0
            ? (int) round(($completedThisWeek / $thisWeek) * 100)
            : 0;

        $avgApproval = Tenant::query()
            ->whereNotNull('approved_at')
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, submitted_at, approved_at)) as s')
            ->value('s');

        $avgDays = $avgApproval ? round($avgApproval / 86400, 1) : null;

        return [
            [
                'label' => 'Appointments today',
                'value' => (string) $today,
                'delta' => $this->dayOverDay($today, $yesterday),
                'tone'  => 'orange',
            ],
            [
                'label' => 'Appointments this week',
                'value' => (string) $thisWeek,
                'delta' => '—',
                'tone'  => 'green',
            ],
            [
                'label' => 'Completion rate',
                'value' => $completionRate.'%',
                'delta' => '—',
                'tone'  => 'amber',
            ],
            [
                'label' => 'Avg approval time',
                'value' => $avgDays !== null ? $avgDays.'d' : '—',
                'delta' => '—',
                'tone'  => 'red',
            ],
        ];
    }

    private function dayOverDay(int $current, int $previous): string
    {
        if ($previous === 0 && $current === 0) return '—';
        if ($previous === 0) return '+'.$current;

        $pct = round((($current - $previous) / $previous) * 100);

        return ($pct > 0 ? '+' : '').$pct.'%';
    }

    // ─────────────────────────────────────────────
    // Platform activity — dual series
    // ─────────────────────────────────────────────

    public function platformActivity(int $months = 7): array
    {
        $firstMonth = now()->subMonths($months - 1)->startOfMonth();

        $range = collect(range(0, $months - 1))
            ->map(fn (int $offset) => $firstMonth->copy()->addMonths($offset));

        $tenantCounts = Tenant::query()
            ->where('created_at', '>=', $firstMonth)
            ->get(['created_at'])
            ->countBy(fn (Tenant $t) => $t->created_at?->format('Y-m'));

        $apptCounts = Appointment::query()
            ->where('created_at', '>=', $firstMonth)
            ->get(['created_at'])
            ->countBy(fn (Appointment $a) => $a->created_at?->format('Y-m'));

        return [
            'labels'       => $range->map(fn (Carbon $m) => $m->format('M'))->all(),
            'tenants'      => $range->map(fn (Carbon $m) => (int) $tenantCounts->get($m->format('Y-m'), 0))->all(),
            'appointments' => $range->map(fn (Carbon $m) => (int) $apptCounts->get($m->format('Y-m'), 0))->all(),
        ];
    }

    // ─────────────────────────────────────────────
    // Leaderboard — top shops by combined activity
    // ─────────────────────────────────────────────

    public function topShops(int $limit = 5, int $days = 30): array
    {
        $start = now()->subDays($days);

        $apptCounts = Appointment::query()
            ->selectRaw('tenant_id, COUNT(*) as c')
            ->where('start_time', '>=', $start)
            ->groupBy('tenant_id')
            ->pluck('c', 'tenant_id');

        // Revenue from completed sales
        $revenue = Sale::query()
            ->selectRaw('tenant_id, SUM(final_amount) as total')
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['paid', 'completed'])
            ->groupBy('tenant_id')
            ->pluck('total', 'tenant_id');

        // Order counts (recent)
        $orderCounts = Order::query()
            ->selectRaw('tenant_id, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('tenant_id')
            ->pluck('c', 'tenant_id');

        $ratings = Review::query()
            ->selectRaw('tenant_id, AVG(rating) as avg_rating')
            ->where('created_at', '>=', $start)
            ->groupBy('tenant_id')
            ->pluck('avg_rating', 'tenant_id');

        return Tenant::query()
            ->with('user:id,name')
            ->get()
            ->map(fn (Tenant $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'logo'         => $t->logo ? asset('storage/'.$t->logo) : null,
                'owner'        => $t->user?->name,
                'appointments' => (int) ($apptCounts[$t->id] ?? 0),
                'orders'       => (int) ($orderCounts[$t->id] ?? 0),
                'revenue'      => (float) ($revenue[$t->id] ?? 0),
                'rating'       => isset($ratings[$t->id]) ? round((float) $ratings[$t->id], 1) : null,
            ])
            ->sortByDesc(fn ($s) => $s['revenue'] + ($s['appointments'] * 100))
            ->take($limit)
            ->values()
            ->toArray();
    }

    // ─────────────────────────────────────────────
    // Delta helper (MoM % change)
    // ─────────────────────────────────────────────

    private function delta(string $modelClass, string $dateColumn, ?callable $filter = null): string
    {
        $now       = now();
        $thisStart = $now->copy()->startOfMonth();
        $lastStart = $now->copy()->subMonth()->startOfMonth();
        $lastEnd   = $thisStart->copy()->subSecond();

        $apply = function ($query) use ($filter) {
            if ($filter) {
                $filter($query);
            }
            return $query;
        };

        $thisCount = $apply($modelClass::query())
            ->where($dateColumn, '>=', $thisStart)
            ->count();

        $lastCount = $apply($modelClass::query())
            ->whereBetween($dateColumn, [$lastStart, $lastEnd])
            ->count();

        if ($lastCount === 0) {
            return '—';
        }

        $pct = round((($thisCount - $lastCount) / $lastCount) * 100);

        if ($pct === 0) {
            return '0%';
        }

        return ($pct > 0 ? '+' : '').$pct.'%';
    }

    // ─────────────────────────────────────────────
    // Tenant detail (used by All Shops modal)
    // ─────────────────────────────────────────────

    public function tenantDetail(int $tenantId): ?array
    {
        $tenant = Tenant::query()
            ->with(['user:id,name,email,phone'])
            ->find($tenantId);

        if (! $tenant) {
            return null;
        }

        $stats = [
            'products'        => Product::where('tenant_id', $tenant->id)->count(),
            'orders'          => Order::where('tenant_id', $tenant->id)->count(),
            'orders_pending'  => Order::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'employees'       => Employee::where('tenant_id', $tenant->id)->count(),
            'appointments'    => Appointment::where('tenant_id', $tenant->id)->count(),
            'services'        => Service::where('tenant_id', $tenant->id)->count(),
            'reviews'         => Review::where('tenant_id', $tenant->id)->count(),
            'revenue'         => (float) Sale::where('tenant_id', $tenant->id)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('final_amount'),
        ];

        $status = $tenant->verification_status instanceof VerificationStatus
            ? $tenant->verification_status->value
            : $tenant->verification_status;

        $reviews = Review::query()
            ->where('tenant_id', $tenant->id)
            ->with([
                'customer:id,name,email',
                'service:id,name',
                'employee:id,name',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Review $r) => [
                'id'         => $r->id,
                'rating'     => (int) $r->rating,
                'review'     => $r->review,
                'created_at' => $r->created_at?->diffForHumans(),
                'customer'   => [
                    'name'  => $r->customer?->name ?? 'Anonymous',
                    'email' => $r->customer?->email,
                ],
                'service'  => $r->service?->name,
                'employee' => $r->employee?->name,
            ])
            ->all();

        return [
            'id'                        => $tenant->id,
            'name'                      => $tenant->name,
            'slug'                      => $tenant->slug,
            'email'                     => $tenant->email,
            'phone'                     => $tenant->phone,
            'address'                   => $tenant->address,
            'logo'                      => $tenant->logo ? asset('storage/'.$tenant->logo) : null,
            'latitude'                  => $tenant->latitude,
            'longitude'                 => $tenant->longitude,
            'verification_status'       => $status,
            'rejection_reason'          => $tenant->rejection_reason,
            'missing_requirements'      => is_array($tenant->missing_requirements) ? $tenant->missing_requirements : [],
            'business_hours'            => $tenant->business_hours ?? [],
            'attachments'               => collect($tenant->attachment ?? [])
                ->map(fn ($p) => ['path' => $p, 'url' => asset('storage/'.$p)])
                ->toArray(),
            'is_active'                 => (bool) $tenant->is_active,
            'business_setup_completed'  => (bool) $tenant->business_setup_completed,
            'submitted_at'              => $tenant->submitted_at?->format('M d, Y g:i A'),
            'created_at'                => $tenant->created_at?->format('M d, Y g:i A'),
            'owner' => [
                'id'    => $tenant->user?->id,
                'name'  => $tenant->user?->name ?? 'Unassigned',
                'email' => $tenant->user?->email,
                'phone' => $tenant->user?->phone,
            ],
            'stats'   => $stats,
            'reviews' => $reviews,
        ];
    }

    // ─────────────────────────────────────────────
    // Verification mix donut
    // ─────────────────────────────────────────────

    public function verificationMix(): array
    {
        $palette = [
            'pending'         => ['color' => '#f8c86f', 'swatch' => 'bg-amber-400'],
            'under_review'    => ['color' => '#4f8ef7', 'swatch' => 'bg-blue-500'],
            'approved'        => ['color' => '#10b981', 'swatch' => 'bg-emerald-500'],
            'rejected'        => ['color' => '#ef4444', 'swatch' => 'bg-red-500'],
            'needs_more_info' => ['color' => '#a855f7', 'swatch' => 'bg-purple-500'],
        ];

        $counts = Tenant::query()
            ->selectRaw('verification_status, COUNT(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        $series = [];
        $total  = 0;

        foreach (VerificationStatus::cases() as $case) {
            $value = (int) ($counts[$case->value] ?? 0);
            $total += $value;

            $series[] = [
                'label'  => $case->label(),
                'value'  => $value,
                'color'  => $palette[$case->value]['color'] ?? '#94a3b8',
                'swatch' => $palette[$case->value]['swatch'] ?? 'bg-slate-400',
            ];
        }

        return [
            'series' => $series,
            'total'  => $total,
        ];
    }
}