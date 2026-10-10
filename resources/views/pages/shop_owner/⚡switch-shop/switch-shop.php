<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function mount(Tenant $tenant)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Guard: only allow switching to a shop this user can access
        $allowed = $user->ownedTenants()->whereKey($tenant->id)->exists()
            || $user->tenants()->whereKey($tenant->id)->exists();

        if (! $allowed) {
            abort(403, 'You do not have access to this shop.');
        }

        session(['active_tenant_id' => $tenant->id]);

        return redirect()->route('owner.dashboard');
    }
};