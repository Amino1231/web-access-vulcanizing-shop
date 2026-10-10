<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\RoadSideAssignment;
use App\Models\RoadSideRequest;
use App\Models\RoadSideService;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class RoadSideSeeder extends Seeder
{
    public function run(): void
    {
        $problemTypes = ['flat_tire', 'dead_battery', 'overheating', 'out_of_fuel'];
        $statuses     = ['completed', 'accepted', 'pending'];
        $customers    = User::role('customer')->get();

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            $employees = Employee::where('tenant_id', $tenant->id)->get();
            $services  = Service::where('tenant_id', $tenant->id)->get();

            foreach ($customers as $index => $customer) {
                $vehicle = Vehicle::where('customer_id', $customer->id)->first();
                if (! $vehicle) continue;

                $status = $statuses[$index % count($statuses)];

                $request = RoadSideRequest::updateOrCreate(
                    [
                        'tenant_id'      => $tenant->id,
                        'customer_id'    => $customer->id,
                        'request_number' => 'RSQ-SEED-' . $tenant->id . '-' . $customer->id,
                    ],
                    [
                        'vehicle_id'   => $vehicle->id,
                        'latitude'     => $tenant->latitude + (($index + 1) / 10000),
                        'longitude'    => $tenant->longitude + (($index + 1) / 10000),
                        'address'      => 'Near ' . $tenant->address,
                        'problem_type' => $problemTypes[$index % count($problemTypes)],
                        'description'  => 'Customer reports vehicle issue on the road.',
                        'status'       => $status,
                        'requested_at' => now()->subHours(rand(1, 48)),
                        'accepted_at'  => in_array($status, ['accepted', 'completed']) ? now()->subHours(rand(1, 20)) : null,
                        'completed_at' => $status === 'completed' ? now()->subHours(rand(1, 5)) : null,
                    ]
                );

                if (in_array($status, ['accepted', 'completed']) && $employees->isNotEmpty()) {
                    $employee = $employees->get($index % $employees->count());

                    RoadSideAssignment::updateOrCreate(
                        [
                            'roadside_request_id' => $request->id,
                            'employee_id'         => $employee->id,
                        ],
                        [
                            'tenant_id'    => $tenant->id,
                            'assigned_at'  => $request->accepted_at ?? now(),
                            'arrival_at'   => $status === 'completed' ? now()->subMinutes(rand(10, 40)) : null,
                            'completed_at' => $status === 'completed' ? $request->completed_at : null,
                            'status'       => $status === 'completed' ? 'completed' : 'assigned',
                        ]
                    );

                    if ($services->isNotEmpty()) {
                        $svc = $services->get($index % $services->count());

                        RoadSideService::updateOrCreate(
                            [
                                'roadside_request_id' => $request->id,
                                'service_id'          => $svc->id,
                            ],
                            [
                                'tenant_id' => $tenant->id,
                                'price'     => $svc->price,
                            ]
                        );
                    }
                }
            }
        }
    }
}