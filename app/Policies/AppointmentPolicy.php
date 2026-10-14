<?php

namespace App\Policies;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $a): bool
    {
        // Admin can view anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner of the shop
        if ($user->hasRole('owner')) {
            $owns = $user->ownedTenants()->whereKey($a->tenant_id)->exists()
                || $user->tenants()->whereKey($a->tenant_id)->exists();

            if ($owns) {
                return true;
            }
        }

        // Assigned employee
        if ($user->hasRole('employee')) {
            $employee = $user->currentEmployee();
            if ($employee && (int) $employee->id === (int) $a->employee_id) {
                return true;
            }
        }

        // The customer who booked it
        if ($user->hasRole('customer')) {
            return (int) $a->customer_id === (int) $user->id;
        }

        return false;
    }

    public function accept(User $user, Appointment $a): bool
    {
        return $this->isAssignedEmployee($user, $a)
            && in_array($a->status, [
                AppointmentStatus::Scheduled,
                AppointmentStatus::Confirmed,
                AppointmentStatus::Assigned,
            ], true);
    }

    public function start(User $user, Appointment $a): bool
    {
        return $this->isAssignedEmployee($user, $a)
            && $a->status === AppointmentStatus::Accepted;
    }

    public function complete(User $user, Appointment $a): bool
    {
        return $this->isAssignedEmployee($user, $a)
            && in_array($a->status, [
                AppointmentStatus::Accepted,
                AppointmentStatus::InProgress,
            ], true);
    }

    public function cancel(User $user, Appointment $a): bool
    {
        // Employee path
        if ($this->isAssignedEmployee($user, $a)) {
            return in_array($a->status, [
                AppointmentStatus::Scheduled,
                AppointmentStatus::Assigned,
                AppointmentStatus::Accepted,
                AppointmentStatus::Confirmed,
            ], true);
        }

        // Owner path
        if ($user->hasRole('owner')) {
            $owns = $user->ownedTenants()->whereKey($a->tenant_id)->exists()
                || $user->tenants()->whereKey($a->tenant_id)->exists();

            if ($owns) {
                return $a->status->canBeCancelled();
            }
        }

        // Customer path
        if ($user->hasRole('customer') && (int) $a->customer_id === (int) $user->id) {
            return $a->status->canBeCancelled()
                && $a->start_time?->isFuture();
        }

        return false;
    }

    public function update(User $user, Appointment $a): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('owner')) {
            return $user->ownedTenants()->whereKey($a->tenant_id)->exists()
                || $user->tenants()->whereKey($a->tenant_id)->exists();
        }

        return $this->isAssignedEmployee($user, $a);
    }

    public function delete(User $user, Appointment $a): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('owner')
                && ($user->ownedTenants()->whereKey($a->tenant_id)->exists()
                    || $user->tenants()->whereKey($a->tenant_id)->exists());
    }

    /**
     * Shared check: is $user the employee assigned to this appointment?
     */
    protected function isAssignedEmployee(User $user, Appointment $a): bool
    {
        if (! $user->hasRole('employee')) {
            return false;
        }

        $employee = $user->currentEmployee();

        return $employee !== null
            && $a->employee_id !== null
            && (int) $employee->id === (int) $a->employee_id;
    }
}