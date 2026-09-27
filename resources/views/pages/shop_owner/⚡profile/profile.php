<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    #[Computed]
    public function tenant(): ?Tenant
    {
        return Auth::user()?->tenant;
    }
};
?>