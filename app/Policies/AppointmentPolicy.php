<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $a): bool
    {
        return match (true) {
            $user->hasRole('admin')    => true,
            $user->hasRole('owner')    => $user->ownedTenants()->whereKey($a->tenant_id)->exists()
                                            || $user->tenants()->whereKey($a->tenant_id)->exists(),
            $user->hasRole('employee') => $user->currentEmployee()?->id === $a->employee_id,
            $user->hasRole('customer') => $a->customer_id === $user->id,
            default                    => false,
        };
    }

    public function accept(User $user, Appointment $a): bool
    {
        return $user->hasRole('employee')
            && $user->currentEmployee()?->id === $a->employee_id
            && in_array($a->status, ['scheduled', 'confirmed', 'assigned'], true);
    }

    public function start(User $user, Appointment $a): bool
    {
        return $user->hasRole('employee')
            && $user->currentEmployee()?->id === $a->employee_id
            && in_array($a->status, ['accepted'], true);
    }

    public function complete(User $user, Appointment $a): bool
    {
        return $user->hasRole('employee')
            && $user->currentEmployee()?->id === $a->employee_id
            && in_array($a->status, ['accepted', 'in_progress'], true);
    }

    public function cancel(User $user, Appointment $a): bool
    {
        return $user->hasRole('employee')
            && $user->currentEmployee()?->id === $a->employee_id
            && in_array($a->status, ['scheduled', 'confirmed', 'assigned', 'accepted'], true);
    }
}