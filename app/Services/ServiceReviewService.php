<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;

class ServiceReviewService
{
    public function hasReviewed(User $user, Service $service): bool
    {
        return Review::query()
            ->where('customer_id', $user->id)
            ->where('service_id', $service->id)
            ->exists();
    }

    public function hasCompletedService(User $user, Service $service): bool
    {
        return Appointment::query()
            ->where('customer_id', $user->id)
            ->where('service_id', $service->id)
            ->where('status', AppointmentStatus::Completed->value)
            ->exists();
    }

    public function canReview(User $user, Service $service): bool
    {
        return $this->hasCompletedService($user, $service)
            && ! $this->hasReviewed($user, $service);
    }

    public function eligibilityReason(User $user, Service $service): string
    {
        if (! $this->hasCompletedService($user, $service)) {
            return 'You can review this service after completing an appointment.';
        }

        if ($this->hasReviewed($user, $service)) {
            return 'You have already reviewed this service.';
        }

        return '';
    }
}