<?php

use App\Support\AdminDashboardMetrics;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public array $kpis = [];
    public array $topShops = [];

    public function mount(AdminDashboardMetrics $metrics): void
    {
        $this->kpis = $metrics->kpis();
        $this->topShops = $metrics->topShops(10); // Get top 10 shops
    }
};