<?php

use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $tab = 'all';    // all | recent | low | flagged | hidden

    #[Url(history: true)]
    public string $tenantFilter = '';

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public int $perPage = 20;

    // Detail modal
    public ?int $viewingId = null;
    public ?array $viewing = null;

    // Confirmation modal
    public ?int $confirmingId = null;
    public ?string $confirmingAction = null;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTab(): void          { $this->resetPage(); }
    public function updatingTenantFilter(): void { $this->resetPage(); }
    public function updatingFrom(): void         { $this->resetPage(); }
    public function updatingTo(): void           { $this->resetPage(); }

    #[Computed]
    public function counts(): array
    {
        return [
            'all'     => Review::count(),
            'recent'  => Review::where('created_at', '>=', now()->subDays(7))->count(),
            'low'     => Review::where('rating', '<=', 2)->count(),
            'flagged' => Review::whereNotNull('flagged_at')->count(),
            'hidden'  => Review::where('is_hidden', true)->count(),
        ];
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function reviews()
    {
        return Review::query()
            ->with([
                'customer:id,name,email',
                'tenant:id,name,logo',
                'product:id,name',
                'service:id,name',
                'employee:id,name',
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('review', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term))
                        ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', $term));
                });
            })
            ->when($this->tab === 'recent',  fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))
            ->when($this->tab === 'low',     fn ($q) => $q->where('rating', '<=', 2))
            ->when($this->tab === 'flagged', fn ($q) => $q->whereNotNull('flagged_at'))
            ->when($this->tab === 'hidden',  fn ($q) => $q->where('is_hidden', true))
            ->when($this->tenantFilter !== '', fn ($q) => $q->where('tenant_id', $this->tenantFilter))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '',   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tab = 'all';
        $this->tenantFilter = '';
        $this->from = '';
        $this->to = '';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Detail modal
    // ─────────────────────────────────────────

    public function viewReview(int $id): void
    {
        $r = Review::with([
            'customer:id,name,email,phone',
            'tenant:id,name,logo,email',
            'product:id,name,sku',
            'service:id,name',
            'employee:id,name,position',
            'sale:id,invoice_number',
        ])->find($id);

        if (! $r) {
            session()->flash('error', 'Review not found.');
            return;
        }

        $this->viewingId = $r->id;
        $this->viewing = [
            'id'          => $r->id,
            'rating'      => (int) $r->rating,
            'review'      => $r->review,
            'kind'        => $r->kind(),
            'subject'     => $r->subjectLabel(),
            'is_hidden'   => (bool) $r->is_hidden,
            'flagged_at'  => $r->flagged_at?->format('M d, Y g:i A'),
            'hidden_at'   => $r->hidden_at?->format('M d, Y g:i A'),
            'created_at'  => $r->created_at?->format('M d, Y g:i A'),
            'customer' => [
                'name'  => $r->customer?->name ?? 'Anonymous',
                'email' => $r->customer?->email,
                'phone' => $r->customer?->phone,
            ],
            'tenant' => [
                'id'    => $r->tenant?->id,
                'name'  => $r->tenant?->name,
                'logo'  => $r->tenant?->logo ? asset('storage/'.$r->tenant->logo) : null,
                'email' => $r->tenant?->email,
            ],
            'product'  => $r->product ? ['name' => $r->product->name, 'sku' => $r->product->sku] : null,
            'service'  => $r->service ? ['name' => $r->service->name] : null,
            'employee' => $r->employee ? ['name' => $r->employee->name, 'position' => $r->employee->position] : null,
            'sale'     => $r->sale ? ['invoice_number' => $r->sale->invoice_number] : null,
        ];
    }

    public function closeModal(): void
    {
        $this->viewingId = null;
        $this->viewing = null;
    }

    // ─────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────

    public function toggleFlag(int $id): void
    {
        $r = Review::find($id);
        if (! $r) return;

        $r->update([
            'flagged_at' => $r->flagged_at ? null : now(),
        ]);

        unset($this->reviews, $this->counts);

        if ($this->viewingId === $id) {
            $this->viewReview($id);
        }

        session()->flash('success', $r->flagged_at ? 'Review flagged for follow-up.' : 'Flag removed.');
    }

    public function confirmHide(int $id): void
    {
        $r = Review::find($id);
        if (! $r) return;

        $this->confirmingId = $r->id;
        $this->confirmingAction = $r->is_hidden ? 'unhide' : 'hide';
    }

    public function confirmDelete(int $id): void
    {
        $r = Review::find($id);
        if (! $r) return;

        $this->confirmingId = $r->id;
        $this->confirmingAction = 'delete';
    }

    public function cancelAction(): void
    {
        $this->confirmingId = null;
        $this->confirmingAction = null;
    }

    public function performAction(): void
    {
        if (! $this->confirmingId || ! $this->confirmingAction) return;

        $r = Review::find($this->confirmingId);
        if (! $r) { $this->cancelAction(); return; }

        if ($this->confirmingAction === 'hide') {
            $r->update([
                'is_hidden' => true,
                'hidden_at' => now(),
            ]);
            $msg = 'Review hidden from public.';
        } elseif ($this->confirmingAction === 'unhide') {
            $r->update([
                'is_hidden' => false,
                'hidden_at' => null,
            ]);
            $msg = 'Review is visible again.';
        } elseif ($this->confirmingAction === 'delete') {
            $r->delete();
            $msg = 'Review permanently deleted.';
        } else {
            $this->cancelAction();
            return;
        }

        unset($this->reviews, $this->counts);

        if ($this->viewingId === $r->id) {
            if ($this->confirmingAction === 'delete') {
                $this->closeModal();
            } else {
                $this->viewReview($r->id);
            }
        }

        session()->flash('success', $msg);
        $this->cancelAction();
    }

    public function starClass(int $index, int $rating): string
    {
        return $index <= $rating
            ? 'text-yellow-400'
            : 'text-slate-300 dark:text-gray-600';
    }
};