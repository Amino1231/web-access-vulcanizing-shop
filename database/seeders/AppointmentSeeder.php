<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $services  = Service::where('tenant_id', $tenant->id)->get();
            $employees = Employee::where('tenant_id', $tenant->id)->get();

            if ($services->isEmpty()) continue;

            foreach ($customers as $customer) {
                $vehicle = Vehicle::where('customer_id', $customer->id)->first();
                if (! $vehicle) continue;

                $baseKey = ['tenant_id' => $tenant->id, 'customer_id' => $customer->id];

                // Completed (recent past)
                Appointment::updateOrCreate(
                    $baseKey + ['appointment_number' => 'APT-COMP-' . $tenant->id . '-' . $customer->id],
                    [
                        'service_id'  => $services->random()->id,
                        'vehicle_id'  => $vehicle->id,
                        'employee_id' => $employees->random()?->id,
                        'start_time'  => now()->subDays(rand(3, 15))->setTime(10, 0),
                        'end_time'    => now()->subDays(rand(3, 15))->setTime(11, 0),
                        'status'      => AppointmentStatus::Completed->value,
                        'notes'       => 'Completed service — no issues.',
                    ]
                );

                // Today — for the "Appointments today" KPI
                Appointment::updateOrCreate(
                    $baseKey + ['appointment_number' => 'APT-TODAY-' . $tenant->id . '-' . $customer->id],
                    [
                        'service_id'  => $services->random()->id,
                        'vehicle_id'  => $vehicle->id,
                        'employee_id' => $employees->random()?->id,
                        'start_time'  => now()->setTime(13, 0),
                        'end_time'    => now()->setTime(14, 0),
                        'status'      => AppointmentStatus::Confirmed->value,
                        'notes'       => 'Customer confirmed. Please have bay 2 ready.',
                    ]
                );

                // Upcoming (next week)
                Appointment::updateOrCreate(
                    $baseKey + ['appointment_number' => 'APT-UP-' . $tenant->id . '-' . $customer->id],
                    [
                        'service_id'  => $services->random()->id,
                        'vehicle_id'  => $vehicle->id,
                        'employee_id' => $employees->random()?->id,
                        'start_time'  => now()->addDays(rand(1, 7))->setTime(14, 0),
                        'end_time'    => now()->addDays(rand(1, 7))->setTime(15, 0),
                        'status'      => AppointmentStatus::Scheduled->value,
                        'notes'       => 'Customer requested tire rotation and pressure check.',
                    ]
                );
            }
        }
    }
}