<?php

use App\Support\AdminDashboardMetrics;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public array $kpis = [];
    public array $moneyKpis = [];
    public array $appointmentKpis = [];

    public array $activityLabels = [];
    public array $activityTenants = [];
    public array $activityAppointments = [];

    public array $donutSeries = [];
    public int   $donutTotal = 0;

    public array $topShops = [];

    public function mount(AdminDashboardMetrics $metrics): void
    {
        $this->kpis            = $metrics->kpis();
        $this->moneyKpis       = $metrics->moneyKpis();
        $this->appointmentKpis = $metrics->appointmentKpis();

        $activity = $metrics->platformActivity();
        $this->activityLabels       = $activity['labels'];
        $this->activityTenants      = $activity['tenants'];
        $this->activityAppointments = $activity['appointments'];

        $mix = $metrics->verificationMix();
        $this->donutSeries = $mix['series'];
        $this->donutTotal  = $mix['total'];

        $this->topShops = $metrics->topShops(5);
    }
};