<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
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
    public string $roleFilter = '';    // '' | owner | customer | inactive

    public int $perPage = 15;

    // Detail modal
    public ?int $viewingUserId = null;

    public ?array $viewingUser = null;

    // Confirmation modal
    public ?int $confirmingUserId = null;

    public ?string $confirmingAction = null;

    public ?string $confirmingName = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'all' => User::count(),
            'owners' => User::role('owner')->count(),
            'customers' => User::role('customer')->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->withCount(['ownedTenants', 'orders'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($this->roleFilter === 'owner', fn ($q) => $q->role('owner'))
            ->when($this->roleFilter === 'customer', fn ($q) => $q->role('customer'))
            ->when($this->roleFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Detail modal
    // ─────────────────────────────────────────

    public function viewUser(int $userId): void
    {
        $user = User::with([
            'ownedTenants:id,user_id,name,slug,verification_status,is_active,logo,created_at',
            'tenants:id,name,slug',
        ])->find($userId);

        if (! $user) {
            return;
        }

        $roles = $user->getRoleNames()->all();

        $recentOrders = Order::where('customer_id', $user->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'status', 'total', 'created_at'])
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status_label' => $o->status->label(),
                'badge' => $o->status->badge(),
                'total' => (float) $o->total,
                'created_at' => $o->created_at?->format('M d, Y'),
            ])->toArray();

        $totalSpent = (float) Order::where('customer_id', $user->id)
            ->where('status', OrderStatus::Completed->value)
            ->sum('total');

        $totalRevenue = (float) Sale::whereHas('tenant', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['paid', 'completed'])
            ->sum('final_amount');

        $this->viewingUserId = $user->id;
        $this->viewingUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'birth_date' => $user->birth_date?->format('M d, Y'),
            'address' => $user->address,
            'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
            'is_active' => (bool) $user->is_active,
            'email_verified' => (bool) $user->email_verified_at,
            'last_login_at' => $user->last_login_at?->format('M d, Y g:i A'),
            'created_at' => $user->created_at?->format('M d, Y g:i A'),
            'roles' => $roles,
            'is_owner' => in_array('owner', $roles, true),
            'is_customer' => in_array('customer', $roles, true),
            'shops' => $user->ownedTenants->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'verification_status' => $t->verification_status,
                'is_active' => (bool) $t->is_active,
                'logo' => $t->logo ? asset('storage/'.$t->logo) : null,
                'created_at' => $t->created_at?->format('M d, Y'),
            ])->toArray(),
            'shared_shops' => $user->tenants->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
            ])->toArray(),
            'recent_orders' => $recentOrders,
            'total_spent' => $totalSpent,
            'total_revenue' => $totalRevenue,
        ];
    }

    public function closeUserModal(): void
    {
        $this->viewingUserId = null;
        $this->viewingUser = null;
    }

    // ─────────────────────────────────────────
    // Confirmation flow
    // ─────────────────────────────────────────

    public function confirmToggleActive(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->confirmingUserId = $user->id;
        $this->confirmingAction = $user->is_active ? 'deactivate' : 'activate';
        $this->confirmingName = $user->name;
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->confirmingUserId = $user->id;
        $this->confirmingAction = 'delete';
        $this->confirmingName = $user->name;
    }

    public function cancelAction(): void
    {
        $this->confirmingUserId = null;
        $this->confirmingAction = null;
        $this->confirmingName = null;
    }

    public function performAction(): void
    {
        if (! $this->confirmingUserId || ! $this->confirmingAction) {
            return;
        }

        $user = User::find($this->confirmingUserId);
        if (! $user) {
            $this->cancelAction();

            return;
        }

        if ($this->confirmingAction === 'delete') {
            // Safety rails
            if ($user->id === Auth::id()) {
                session()->flash('error', 'You cannot delete your own account.');
                $this->cancelAction();

                return;
            }

            if ($user->hasRole('admin')) {
                session()->flash('error', 'You cannot delete another admin.');
                $this->cancelAction();

                return;
            }

            // Refuse if they own shops — those would orphan tenants
            if ($user->ownedTenants()->exists()) {
                session()->flash('error', 'This user owns shops. Deactivate their account instead.');
                $this->cancelAction();

                return;
            }

            $name = $user->name;
            $email = $user->email;

            DB::transaction(function () use ($user) {
                // Kill active sessions
                DB::table('sessions')->where('user_id', $user->id)->delete();

                // Detach roles (spatie pivot)
                $user->syncRoles([]);

                // Remove from tenant_user pivot if any
                DB::table('tenant_users')->where('user_id', $user->id)->delete();

                // Now safe to hard-delete
                $user->delete();
            });

            unset($this->users, $this->counts);
            $this->cancelAction();

            session()->flash('success', "{$name} ({$email}) deleted.");

            return;
        }

        if ($this->confirmingAction === 'activate') {
            $user->update(['is_active' => true]);
            $msg = "{$user->name} activated.";
        } elseif ($this->confirmingAction === 'deactivate') {
            $user->update(['is_active' => false]);

            // Force logout from all sessions
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $msg = "{$user->name} deactivated.";
        } else {
            $this->cancelAction();

            return;
        }

        unset($this->users, $this->counts);

        if ($this->viewingUserId === $user->id) {
            $this->viewUser($user->id);
        }

        session()->flash('success', $msg);
        $this->cancelAction();
    }
    // ─────────────────────────────────────────
    // Password reset
    // ─────────────────────────────────────────

    public function sendPasswordReset(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        try {
            Password::sendResetLink(['email' => $user->email]);
            session()->flash('success', "Password reset link sent to {$user->email}.");
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'Could not send reset link.');
        }
    }
};
