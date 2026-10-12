<?php

namespace Database\Seeders;

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'owner_email'     => 'owner@gmail.com',
                'name'            => 'RoadMaster Vulcanizing',
                'phone'           => '09170000002',
                'email'           => 'shop@roadmaster.com',
                'address'         => 'Osmeña Avenue, Barangay VIII, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.9027,
                'longitude'       => 123.0722,
                'status'          => VerificationStatus::Approved->value,
                'is_active'       => true,
                'commission_rate' => 10.00,   // standard rate
                'submitted'       => 60,
                'approved'        => 58,
            ],
            [
                'owner_email'     => 'owner2@gmail.com',
                'name'            => 'Cebu Tire Pros',
                'phone'           => '09170000003',
                'email'           => 'shop@cebutirepros.com',
                'address'         => 'Yulo Street, Barangay XIII, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.8980,
                'longitude'       => 123.0660,
                'status'          => VerificationStatus::Approved->value,
                'is_active'       => true,
                'commission_rate' => 8.50,    // negotiated lower rate
                'submitted'       => 45,
                'approved'        => 43,
            ],
            [
                'owner_email'     => 'owner3@gmail.com',
                'name'            => 'Davao Wheel Works',
                'phone'           => '09170000004',
                'email'           => 'shop@davaowheelworks.com',
                'address'         => 'Gatuslao Street, Barangay VI, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.9085,
                'longitude'       => 123.0780,
                'status'          => VerificationStatus::Pending->value,
                'is_active'       => false,
                'commission_rate' => 10.00,
                'submitted'       => 3,
                'approved'        => null,
            ],
            [
                'owner_email'     => 'owner@gmail.com',
                'name'            => 'Manila Tire Hub',
                'phone'           => '09170000005',
                'email'           => 'shop@manilatirehub.com',
                'address'         => 'Don Miguel Ossorio Street, Barangay I, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.9025513,
                'longitude'       => 123.0724525,
                'status'          => VerificationStatus::UnderReview->value,
                'is_active'       => false,
                'commission_rate' => 12.00,   // premium shop, higher rate
                'submitted'       => 1,
                'approved'        => null,
            ],
            [
                'owner_email'     => 'owner@gmail.com',
                'name'            => 'QC Wheel Repair',
                'phone'           => '09170000006',
                'email'           => 'shop@qcwheel.com',
                'address'         => 'Victorias–Negros Road, Barangay XIX, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.8910,
                'longitude'       => 123.0830,
                'status'          => VerificationStatus::NeedsMoreInfo->value,
                'is_active'       => false,
                'commission_rate' => 10.00,
                'submitted'       => 5,
                'approved'        => null,
            ],
            [
                'owner_email'     => 'owner@gmail.com',
                'name'            => 'Bulacan Auto Shop',
                'phone'           => '09170000007',
                'email'           => 'shop@bulacanauto.com',
                'address'         => 'Magsaysay Street, Barangay XXI, Victorias City, Negros Occidental, Negros Island Region, 6119, Philippines',
                'latitude'        => 10.9150,
                'longitude'       => 123.0620,
                'status'          => VerificationStatus::Rejected->value,
                'is_active'       => false,
                'commission_rate' => 10.00,
                'submitted'       => 10,
                'approved'        => null,
            ],
        ];

        foreach ($shops as $data) {
            $owner = User::where('email', $data['owner_email'])->first();
            if (! $owner) continue;

            Tenant::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'user_id'                  => $owner->id,
                    'name'                     => $data['name'],
                    'phone'                    => $data['phone'],
                    'email'                    => $data['email'],
                    'address'                  => $data['address'],
                    'latitude'                 => $data['latitude'],
                    'longitude'                => $data['longitude'],
                    'is_active'                => $data['is_active'],
                    'business_setup_completed' => true,
                    'business_hours'           => $this->defaultHours(),
                    'commission_rate'          => $data['commission_rate'],   // ← NEW
                    'verification_status'      => $data['status'],
                    'submitted_at'             => now()->subDays($data['submitted']),
                    'approved_at'              => $data['approved'] !== null ? now()->subDays($data['approved']) : null,
                    'rejection_reason'         => $data['status'] === VerificationStatus::Rejected->value
                        ? 'Business permit was unreadable. Please re-upload a clear scan.'
                        : null,
                    'missing_requirements'     => $data['status'] === VerificationStatus::NeedsMoreInfo->value
                        ? ['BIR registration certificate (blurry scan)']
                        : null,
                ]
            );
        }
    }

    protected function defaultHours(): array
    {
        $days  = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = ['open' => '08:00', 'close' => '18:00'];
        }

        return $hours;
    }
}