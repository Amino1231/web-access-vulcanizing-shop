<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeLocation;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employeeUsers = User::role('employee')->get();

        $staff = [
            ['name' => 'Rico Santos', 'position' => 'Senior Technician', 'commission' => 5.00, 'phone' => '09172220001'],
            ['name' => 'Lito Cruz',   'position' => 'Vulcanizer',        'commission' => 3.50, 'phone' => '09172220002'],
            ['name' => 'Nina Gomez',  'position' => 'Service Advisor',   'commission' => 2.00, 'phone' => '09172220003'],
        ];

        foreach (Tenant::where('is_active', true)->get() as $tenant) {
            foreach ($staff as $index => $person) {
                $linkedUser = $employeeUsers->get($index);

                $emp = Employee::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name'      => $person['name'],
                    ],
                    [
                        'user_id'         => $linkedUser?->id,
                        'position'        => $person['position'],
                        'commission_rate' => $person['commission'],
                        'phone'           => $person['phone'],
                        'hired_at'        => now()->subMonths(rand(3, 24)),
                        'is_active'       => true,
                    ]
                );

                foreach (range(1, 6) as $day) {
                    Schedule::updateOrCreate(
                        [
                            'tenant_id'   => $tenant->id,
                            'employee_id' => $emp->id,
                            'day_of_week' => $day,
                        ],
                        [
                            'start_time' => '08:00',
                            'end_time'   => $day === 6 ? '12:00' : '17:00',
                        ]
                    );
                }

                EmployeeLocation::firstOrCreate(
                    [
                        'tenant_id'   => $tenant->id,
                        'employee_id' => $emp->id,
                    ],
                    [
                        'latitude'    => $tenant->latitude + (($index + 1) / 10000),
                        'longitude'   => $tenant->longitude + (($index + 1) / 10000),
                        'recorded_at' => now()->subMinutes(15),
                    ]
                );
            }
        }
    }
}