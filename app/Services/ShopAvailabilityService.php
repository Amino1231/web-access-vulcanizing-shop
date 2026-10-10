<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Tenant;
use Carbon\Carbon;

class ShopAvailabilityService
{
    public function __construct(public readonly Tenant $tenant) {}

    /**
     * Return 30-minute slots for a given date.
     *
     * Each slot: ['time' => 'H:i', 'available' => bool, 'reason' => string]
     */
    public function slotsForDate(Carbon $date, ?int $employeeId = null): array
    {
        $dayName = $date->format('l');
        $hours   = $this->tenant->business_hours[$dayName] ?? null;

        if (! $hours || empty($hours['open']) || empty($hours['close'])) {
            return [];   // Shop closed that day
        }

        $start  = Carbon::parse($date->toDateString() . ' ' . $hours['open']);
        $end    = Carbon::parse($date->toDateString() . ' ' . $hours['close']);
        $now    = now();

        // Pull booked appointments for that date in one query
        $booked = Appointment::query()
            ->where('tenant_id', $this->tenant->id)
            ->whereDate('start_time', $date->toDateString())
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->get(['start_time', 'end_time'])
            ->map(fn ($a) => [
                'start' => $a->start_time->format('H:i'),
                'end'   => $a->end_time->format('H:i'),
            ])
            ->all();

        // Count of active employees working that day (optional, for capacity)
        $activeEmployees = Employee::where('tenant_id', $this->tenant->id)
            ->where('is_active', true)
            ->count();

        $slots  = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $time = $cursor->format('H:i');

            $isPast     = $cursor->lt($now);
            $isBooked   = $this->isSlotBooked($time, $booked);

            $reason = 'available';
            if ($isPast) {
                $reason = 'past';
            } elseif ($isBooked && $activeEmployees <= 1) {
                // Only 1 employee → a booked slot means nothing available
                $reason = 'booked';
            } elseif ($isBooked) {
                // Multiple employees → may still have capacity
                $reason = 'partially_booked';
            }

            $slots[] = [
                'time'      => $time,
                'available' => $reason === 'available' || $reason === 'partially_booked',
                'reason'    => $reason,
            ];

            $cursor->addMinutes(30);
        }

        return $slots;
    }

    protected function isSlotBooked(string $time, array $booked): bool
    {
        foreach ($booked as $range) {
            if ($time >= $range['start'] && $time < $range['end']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Count how many slots are open today for a quick indicator.
     */
    public function availableSlotsTodayCount(): int
    {
        return collect($this->slotsForDate(now()))
            ->where('available', true)
            ->count();
    }
}