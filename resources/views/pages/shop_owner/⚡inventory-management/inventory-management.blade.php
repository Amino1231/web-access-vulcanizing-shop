<div class="space-y-6"
     x-data="{ notifications: [] }"
     @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     ">

    {{-- Notifications --}}
    <div class="fixed top-4 right-4 z-[60] flex flex-col gap-2 pointer-events-none">
        <template x-for="(n, i) in notifications" :key="i">
            <div class="pointer-events-auto w-80 rounded-xl border px-4 py-3 shadow-lg text-sm font-medium"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-700': n.type === 'success',
                     'bg-red-50 border-red-200 text-red-700': n.type === 'error',
                     'bg-slate-50 border-slate-200 text-slate-700': !n.type
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Header --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                Owner portal
            </p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                Product inventory
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Track stock movements for your physical products only.
            </p>

            {{-- Scope clarification --}}
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-[11px] font-medium text-blue-700 dark:text-blue-300">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 16v-4M12 8h.01" />
                    </svg>
                    Inventory is for <strong>products</strong>, not services
                </span>

                <a href="{{ route('owner.appointments') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-[11px] font-semibold text-[#FF5E14] transition hover:bg-orange-100 dark:hover:bg-orange-500/20">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Manage services →
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('owner.product_create') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New product
            </a>

            <button wire:click="openAdjustModal" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
                Adjust stock
            </button>
        </div>
    </div>

    @if (empty($kpis))
        <div class="rounded-3xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-10 text-center text-slate-500 dark:text-gray-400">
            No business data is available yet. Complete the business setup to populate dashboard metrics.
        </div>
    @else
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- KPIs --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                @php
                    $toneBadge = match ($kpi['tone'] ?? 'green') {
                        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
                        'amber'  => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                        'red'    => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
                        default  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                        <span>{{ $kpi['label'] }}</span>
                        <span class="rounded-full px-2 py-1 text-[9px] {{ $toneBadge }}">{{ $kpi['delta'] }}</span>
                    </div>
                    <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $kpi['value'] }}</div>
                </article>
            @endforeach
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- Charts --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Movement</p>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Net product stock change</h2>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#FF5E14]"></span>
                        Last 6 months
                    </div>
                </div>
                <div class="h-56 w-full">
                    <canvas id="ownerLineChart" class="h-full w-full"></canvas>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <div class="mb-4">
                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Snapshot</p>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Product stock status</h2>
                </div>
                <div class="flex flex-col items-center justify-center gap-4">
                    <canvas id="ownerDonutChart" width="190" height="190"></canvas>
                    <div class="w-full space-y-2">
                        @foreach ($donutSeries as $segment)
                            <div class="flex items-center justify-between text-sm text-slate-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $segment['color'] }}"></span>
                                    {{ $segment['label'] }}
                                </div>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $segment['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- Inventory log --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="border-b border-slate-200 dark:border-gray-800 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Audit log</p>
                        <h2 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">Product stock movement history</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">
                            Every stock adjustment, order fulfillment, and cancellation is recorded here.
                        </p>
                    </div>

                    @php $logs = $this->logs; @endphp
                    <div class="shrink-0 text-xs text-slate-500 dark:text-gray-400">
                        {{ number_format($logs->total()) }} {{ \Illuminate\Support\Str::plural('entry', $logs->total()) }}
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input wire:model.live.debounce.300ms="logSearch" type="text"
                               placeholder="Search by product, SKU, or reason..."
                               class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                    </div>

                    <select wire:model.live="logType"
                            class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                        <option value="">All types</option>
                        <option value="stock_in">Stock in</option>
                        <option value="stock_out">Stock out</option>
                    </select>

                    <input wire:model.live="logFrom" type="date"
                           class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

                    <input wire:model.live="logTo" type="date"
                           class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

                    @if ($logSearch || $logType || $logFrom || $logTo)
                        <button wire:click="clearLogFilters" type="button"
                                class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700">
                            Clear
                        </button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-slate-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Product</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Type</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Change</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Before → After</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Reason</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($logs as $entry)
                            @php
                                $isIn = $entry->type === 'stock_in';
                                $qty  = (int) $entry->quantity;
                            @endphp
                            <tr wire:key="log-{{ $entry->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $entry->product?->name ?? 'Deleted product' }}
                                    </p>
                                    @if ($entry->product?->sku)
                                        <p class="truncate font-mono text-xs text-slate-500 dark:text-gray-400">
                                            {{ $entry->product->sku }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider
                                        {{ $isIn
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                            : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300' }}">
                                        @if ($isIn)
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" />
                                            </svg>
                                            Stock in
                                        @else
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20V4m0 0l6 6m-6-6L6 10" />
                                            </svg>
                                            Stock out
                                        @endif
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center font-bold {{ $isIn ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $qty > 0 ? '+' : '' }}{{ $qty }}
                                </td>

                                <td class="px-4 py-3 text-center text-xs text-slate-500 dark:text-gray-400">
                                    <span class="font-mono">{{ $entry->before_stock }}</span>
                                    <span class="text-slate-400"> → </span>
                                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $entry->after_stock }}</span>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="truncate text-xs text-slate-600 dark:text-gray-400 max-w-[200px]">
                                        {{ $entry->remarks ?? '—' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-gray-400">
                                    {{ $entry->created_at?->format('M d, Y g:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-16 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No product inventory activity</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                        Try a different filter, or click <strong class="text-slate-700 dark:text-gray-200">Adjust stock</strong> above.
                                    </p>
                                    <p class="mt-3 text-[11px] text-slate-400 dark:text-gray-500 italic">
                                        Services are managed from the <a href="{{ route('owner.appointments') }}" wire:navigate class="text-[#FF5E14] hover:underline not-italic">Appointments</a> page, not here.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->total() > 0)
                <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500 dark:text-gray-400">
                        Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                    </p>
                    <div>{{ $logs->links() }}</div>
                </div>
            @endif
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- ADJUSTMENT MODAL --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($showAdjustModal)
        @php
            $picked = $this->pickedProduct;
            $currentStock = $this->currentStock;
        @endphp
        <div x-data @keydown.escape.window="$wire.closeAdjustModal()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">

            <div class="flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

                {{-- Header --}}
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Manual adjustment</p>
                        <h3 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">Adjust product stock</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">
                            Only physical products are inventoried. Services aren't adjusted here.
                        </p>
                    </div>
                    <button wire:click="closeAdjustModal" type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-5">

                    @error('general')
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                    @enderror

                    {{-- Product picker --}}
                    @if (! $picked)
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Pick a product <span class="text-red-500">*</span>
                            </label>
                            <p class="mb-2 text-[11px] text-slate-500 dark:text-gray-400">
                                Only products appear below — services aren't inventory-tracked.
                            </p>
                            <input wire:model.live.debounce.300ms="productSearch" type="text"
                                   placeholder="Search by product name or SKU..."
                                   class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />

                            @error('selectedProductId')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-3 max-h-64 overflow-y-auto rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                                @forelse ($this->pickerProducts as $p)
                                    <button type="button" wire:click="pickProduct({{ $p->id }})"
                                            class="flex w-full items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 px-3 py-2.5 text-left transition last:border-b-0 hover:bg-slate-50 dark:hover:bg-gray-800/60">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $p->name }}</p>
                                            @if ($p->sku)
                                                <p class="truncate font-mono text-[11px] text-slate-500 dark:text-gray-400">{{ $p->sku }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $p->stock }}</p>
                                            <p class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-gray-400">in stock</p>
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-slate-500 dark:text-gray-400">
                                        No products found.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        {{-- Picked product display --}}
                        <div class="rounded-xl border border-orange-200 dark:border-orange-500/30 bg-orange-50 dark:bg-orange-500/10 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-orange-700 dark:text-orange-400">Selected product</p>
                                    <p class="mt-0.5 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $picked->name }}</p>
                                    @if ($picked->sku)
                                        <p class="truncate font-mono text-xs text-slate-500 dark:text-gray-400">{{ $picked->sku }}</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="clearPickedProduct"
                                        class="shrink-0 rounded-lg border border-orange-200 dark:border-orange-500/30 bg-white dark:bg-gray-800 px-2.5 py-1 text-[11px] font-semibold text-orange-700 dark:text-orange-400 hover:bg-orange-100">
                                    Change
                                </button>
                            </div>
                        </div>

                        {{-- Variant picker (only if variants exist) --}}
                        @if ($picked->variants->isNotEmpty())
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Which variant? <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="selectedVariantId"
                                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100">
                                    <option value="">— Select variant —</option>
                                    @foreach ($picked->variants as $v)
                                        <option value="{{ $v->id }}">
                                            {{ $v->sku }} — {{ $v->stock_quantity }} in stock
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedVariantId')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Current stock display --}}
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/50 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    Current stock
                                </span>
                                <span class="text-xl font-bold text-slate-900 dark:text-white">{{ $currentStock }}</span>
                            </div>
                        </div>

                        {{-- Adjustment type --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Adjustment type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3.5 py-3 transition
                                    {{ $adjustType === 'stock_in'
                                        ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'
                                        : 'border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700' }}">
                                    <input type="radio" wire:model.live="adjustType" value="stock_in"
                                           class="h-4 w-4 text-emerald-500 focus:ring-emerald-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Stock In</p>
                                        <p class="text-[11px] text-slate-500 dark:text-gray-400">Add units</p>
                                    </div>
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3.5 py-3 transition
                                    {{ $adjustType === 'stock_out'
                                        ? 'border-red-500 bg-red-50 dark:bg-red-500/10'
                                        : 'border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700' }}">
                                    <input type="radio" wire:model.live="adjustType" value="stock_out"
                                           class="h-4 w-4 text-red-500 focus:ring-red-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Stock Out</p>
                                        <p class="text-[11px] text-slate-500 dark:text-gray-400">Remove units</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('adjustQuantity', Math.max(1, adjustQuantity - 1))"
                                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                    </svg>
                                </button>
                                <input wire:model.live="adjustQuantity" type="number" min="1" max="100000"
                                       class="flex-1 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-center text-lg font-bold text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                <button type="button" wire:click="$set('adjustQuantity', adjustQuantity + 1)"
                                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                            @error('adjustQuantity')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- Preview new stock --}}
                            @php
                                $preview = $adjustType === 'stock_in'
                                    ? $currentStock + (int) $adjustQuantity
                                    : max(0, $currentStock - (int) $adjustQuantity);
                            @endphp
                            <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                                New stock after adjustment:
                                <span class="font-bold {{ $adjustType === 'stock_in' ? 'text-emerald-600' : 'text-red-600' }}">{{ $preview }}</span>
                            </p>
                        </div>

                        {{-- Reason --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Reason <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="adjustReason" type="text"
                                   placeholder="e.g. Supplier delivery, Damaged, Return, Physical count correction"
                                   class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                            @error('adjustReason')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                                This will appear in the audit log so you can trace why stock changed.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                    <button wire:click="closeAdjustModal" type="button"
                            class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="saveAdjustment"
                            wire:loading.attr="disabled"
                            wire:target="saveAdjustment"
                            @disabled(! $picked || ($picked->variants->isNotEmpty() && ! $selectedVariantId))
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveAdjustment">
                            {{ $adjustType === 'stock_in' ? 'Add stock' : 'Remove stock' }}
                        </span>
                        <span wire:loading wire:target="saveAdjustment">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    // ─── Line chart ────────────────────────
    const lineCanvas = document.getElementById('ownerLineChart');
    if (lineCanvas) {
        const labels = @json($chartLabels);
        const values = @json($lineSeries).map(Number);
        const isDark = document.documentElement.classList.contains('dark');

        const drawLine = () => {
            const ctx = lineCanvas.getContext('2d');
            const w = lineCanvas.clientWidth;
            const h = lineCanvas.clientHeight || 224;
            const dpr = window.devicePixelRatio || 1;

            lineCanvas.width  = w * dpr;
            lineCanvas.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, w, h);

            const padding = { top: 16, right: 16, bottom: 28, left: 40 };
            const plotW = w - padding.left - padding.right;
            const plotH = h - padding.top - padding.bottom;
            const max = Math.max(...values.map(Math.abs), 1);
            const chartMax = Math.ceil(max / 5) * 5 || 5;

            ctx.strokeStyle = isDark ? '#1f2937' : '#e2e8f0';
            ctx.lineWidth = 1;
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = isDark ? '#6b7280' : '#94a3b8';

            for (let i = 0; i <= 4; i++) {
                const y = padding.top + (plotH / 4) * i;
                const val = Math.round(chartMax - (chartMax / 4) * i);
                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(w - padding.right, y);
                ctx.stroke();
                ctx.fillText(String(val), padding.left - 8, y);
            }

            if (!values.length) return;

            const points = values.map((v, i) => ({
                x: padding.left + (plotW / Math.max(values.length - 1, 1)) * i,
                y: padding.top + plotH - (v / chartMax) * plotH,
            }));

            const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + plotH);
            gradient.addColorStop(0, 'rgba(255, 94, 20, 0.30)');
            gradient.addColorStop(1, 'rgba(255, 94, 20, 0.01)');

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            points.slice(1).forEach((p) => ctx.lineTo(p.x, p.y));
            ctx.lineTo(points[points.length - 1].x, padding.top + plotH);
            ctx.lineTo(points[0].x, padding.top + plotH);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            points.slice(1).forEach((p) => ctx.lineTo(p.x, p.y));
            ctx.strokeStyle = '#FF5E14';
            ctx.lineWidth = 3;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.stroke();

            points.forEach((p) => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = isDark ? '#111827' : '#ffffff';
                ctx.fill();
                ctx.strokeStyle = '#FF5E14';
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
            ctx.font = '11px sans-serif';
            labels.forEach((label, i) => {
                const x = padding.left + (plotW / Math.max(labels.length - 1, 1)) * i;
                ctx.fillText(label, x, h - 8);
            });
        };

        drawLine();
        new ResizeObserver(drawLine).observe(lineCanvas);
    }

    // ─── Donut chart ───────────────────────
    const donutCanvas = document.getElementById('ownerDonutChart');
    if (donutCanvas) {
        const ctx = donutCanvas.getContext('2d');
        const data = @json($donutSeries);
        const total = data.reduce((sum, d) => sum + Number(d.value || 0), 0) || 0;
        const isDark = document.documentElement.classList.contains('dark');

        if (total > 0) {
            const cx = donutCanvas.width / 2;
            const cy = donutCanvas.height / 2;
            const radius = 62;
            let angle = -Math.PI / 2;

            data.forEach((item) => {
                const value = Number(item.value || 0);
                if (value === 0) return;

                const slice = (value / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.arc(cx, cy, radius, angle, angle + slice);
                ctx.closePath();
                ctx.fillStyle = item.color;
                ctx.fill();
                angle += slice;
            });

            ctx.beginPath();
            ctx.arc(cx, cy, 36, 0, Math.PI * 2);
            ctx.fillStyle = isDark ? '#111827' : '#ffffff';
            ctx.fill();

            ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
            ctx.textAlign = 'center';
            ctx.font = '700 18px sans-serif';
            ctx.fillText(String(total), cx, cy + 4);

            ctx.font = '10px sans-serif';
            ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
            ctx.fillText('PRODUCTS', cx, cy + 18);
        }
    }
</script>
@endscript