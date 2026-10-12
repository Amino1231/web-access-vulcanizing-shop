<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Post;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Notifications\AppointmentBooked;
use App\Services\ServiceReviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public int $postId;

    public string $selectedDate = '';

    public string $selectedTime = '';

    public ?int $selectedVehicle = null;

    public ?int $selectedEmployee = null;

    public string $notes = '';

    // Review form state
    public int $reviewRating = 5;

    public string $reviewText = '';

    public string $reviewMessage = '';

    public function mount(int $post): void
    {
        $this->postId = $post;
        $this->selectedDate = now()->addDay()->format('Y-m-d');

        if (! $this->post) {
            abort(404, 'Service not found.');
        }
    }

    #[Computed]
    public function post(): ?Post
    {
        return Post::query()
            ->where('status', 'published')
            ->whereNull('archived_at')
            ->with(['service.category:id,name', 'service:id,name', 'tenant:id,name,phone,address'])
            ->find($this->postId);
    }

    #[Computed]
    public function vehicles()
    {
        return Vehicle::query()
            ->where('customer_id', Auth::id())
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'brand', 'model', 'vehicle_type']);
    }

    /**
     * Employees eligible for the currently selected date+time.
     * If date or time isn't picked yet → returns ALL active employees.
     * Once picked → only employees whose schedule covers the service window.
     */
    #[Computed]
    public function employees()
    {
        $post = $this->post;
        if (! $post) {
            return collect();
        }

        $query = Employee::query()
            ->where('tenant_id', $post->tenant_id)
            ->where('is_active', true);

        if ($this->selectedDate && $this->selectedTime) {
            $dayIso = Carbon::parse($this->selectedDate)->dayOfWeekIso; // 1-7
            $start = $this->selectedTime;
            $duration = $post->service?->duration_minutes ?? 30;
            $end = Carbon::parse($this->selectedDate.' '.$this->selectedTime)
                ->addMinutes($duration)
                ->format('H:i');

            $query->whereHas('schedules', function ($q) use ($dayIso, $start, $end) {
                $q->where('day_of_week', $dayIso)
                    ->where('start_time', '<=', $start)
                    ->where('end_time', '>=', $end);
            });
        }

        return $query->orderBy('name')->get(['id', 'name', 'position']);
    }

    /**
     * Available time slots for the currently selected date.
     *
     * IMPORTANT: this method is called `timeSlots()` (not `slots()`)
     * because `slots` is a reserved property name inside Livewire —
     * using `$this->slots` silently returns Livewire's internal
     * blade-slot container instead of the computed array.
     */
    #[Computed]
    public function timeSlots(): array
    {
        $post = $this->post;
        if (! $post || ! $this->selectedDate) {
            return [];
        }

        $start = Carbon::parse($this->selectedDate.' 08:00');
        $end = Carbon::parse($this->selectedDate.' 18:00');

        $dayIso = Carbon::parse($this->selectedDate)->dayOfWeekIso; // 1-7

        // Existing appointments for that day
        $booked = Appointment::query()
            ->where('tenant_id', $post->tenant_id)
            ->whereDate('start_time', $this->selectedDate)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value, AppointmentStatus::NoShow->value])
            ->pluck('start_time')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->all();

        // Does this shop use the employee system at all?
        $hasEmployees = Employee::where('tenant_id', $post->tenant_id)
            ->where('is_active', true)
            ->exists();

        // If yes, load all active employee shifts for this weekday
        $daySchedules = collect();
        if ($hasEmployees) {
            $daySchedules = Schedule::query()
                ->where('day_of_week', $dayIso)
                ->whereIn('employee_id', function ($q) use ($post) {
                    $q->select('id')
                        ->from('employees')
                        ->where('tenant_id', $post->tenant_id)
                        ->where('is_active', true);
                })
                ->get(['start_time', 'end_time']);
        }

        $duration = $post->service?->duration_minutes ?? 30;

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slotStart = $cursor->format('H:i');
            $slotEnd = $cursor->copy()->addMinutes($duration)->format('H:i');

            $isBooked = in_array($slotStart, $booked, true);
            $isPast = $cursor->isPast();

            // Only enforce staff-availability when the shop actually has employees.
            // A shop with zero employees keeps working as before.
            $hasStaff = true;
            if ($hasEmployees) {
                $hasStaff = $daySchedules->contains(function ($s) use ($slotStart, $slotEnd) {
                    $sStart = substr((string) $s->start_time, 0, 5);
                    $sEnd = substr((string) $s->end_time, 0, 5);

                    return $sStart <= $slotStart && $sEnd >= $slotEnd;
                });
            }

            $reason = null;
            if ($isBooked) {
                $reason = 'booked';
            } elseif ($isPast) {
                $reason = 'past';
            } elseif (! $hasStaff) {
                $reason = 'no_staff';
            }

            $slots[] = [
                'time' => $slotStart,
                'available' => $reason === null,
                'reason' => $reason,
            ];

            $cursor->addMinutes(30);
        }

        return $slots;
    }

    // ─── Reviews ─────────────────────────────

    #[Computed]
    public function reviews()
    {
        $service = $this->post?->service;
        if (! $service) {
            return collect();
        }

        return Review::query()
            ->where('service_id', $service->id)
            ->visible()
            ->with(['customer:id,name'])
            ->latest()
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function reviewStats(): array
    {
        $service = $this->post?->service;
        if (! $service) {
            return ['count' => 0, 'average' => 0.0, 'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]];
        }

        $count = Review::where('service_id', $service->id)->count();

        if ($count === 0) {
            return ['count' => 0, 'average' => 0.0, 'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]];
        }

        $avg = round((float) Review::where('service_id', $service->id)->avg('rating'), 1);

        $breakdown = Review::where('service_id', $service->id)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        return [
            'count' => $count,
            'average' => $avg,
            'breakdown' => [
                5 => (int) ($breakdown[5] ?? 0),
                4 => (int) ($breakdown[4] ?? 0),
                3 => (int) ($breakdown[3] ?? 0),
                2 => (int) ($breakdown[2] ?? 0),
                1 => (int) ($breakdown[1] ?? 0),
            ],
        ];
    }

    #[Computed]
    public function canReview(): bool
    {
        $user = Auth::user();
        $service = $this->post?->service;

        if (! $user || ! $service) {
            return false;
        }

        return app(ServiceReviewService::class)->canReview($user, $service);
    }

    #[Computed]
    public function reviewBlockReason(): string
    {
        $user = Auth::user();
        $service = $this->post?->service;

        if (! $user || ! $service) {
            return 'Sign in to leave a review.';
        }

        return app(ServiceReviewService::class)->eligibilityReason($user, $service);
    }

    #[Computed]
    public function hasReviewedService(): bool
    {
        $user = Auth::user();
        $service = $this->post?->service;

        if (! $user || ! $service) {
            return false;
        }

        return app(ServiceReviewService::class)->hasReviewed($user, $service);
    }

    public function submitReview(ServiceReviewService $service): void
    {
        $user = Auth::user();
        $post = $this->post;
        $svc = $post?->service;

        if (! $user || ! $svc) {
            $this->reviewMessage = 'Please sign in to leave a review.';

            return;
        }

        if (! $service->canReview($user, $svc)) {
            $this->reviewMessage = $service->eligibilityReason($user, $svc);

            return;
        }

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewText' => ['nullable', 'string', 'min:3', 'max:1000'],
        ]);

        Review::create([
            'tenant_id' => $post->tenant_id,
            'customer_id' => $user->id,
            'service_id' => $svc->id,
            'rating' => $this->reviewRating,
            'review' => trim($this->reviewText) ?: null,
        ]);

        $this->reviewRating = 5;
        $this->reviewText = '';
        $this->reviewMessage = 'Thanks! Your review has been posted.';

        unset(
            $this->reviews,
            $this->reviewStats,
            $this->canReview,
            $this->reviewBlockReason,
            $this->hasReviewedService,
        );
    }

    // ─── Booking ─────────────────────────────

    public function updatedSelectedDate(): void
    {
        $this->selectedTime = '';
        $this->selectedEmployee = null;
        unset($this->timeSlots);
    }

    public function updatedSelectedTime(): void
    {
        // If the currently selected employee no longer works this window, clear it
        if ($this->selectedEmployee !== null && ! $this->employees->contains('id', $this->selectedEmployee)) {
            $this->selectedEmployee = null;
        }
    }

    public function book()
    {
        try {
            $post = $this->post;
            if (! $post) {
                $this->addError('general', 'Service not found.');

                return;
            }

            $this->validate([
                'selectedDate' => ['required', 'date', 'after_or_equal:today'],
                'selectedTime' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
                'selectedVehicle' => ['nullable', 'integer', 'exists:vehicles,id'],
                'selectedEmployee' => ['nullable', 'integer', 'exists:employees,id'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $start = Carbon::parse($this->selectedDate.' '.$this->selectedTime);
            $end = $start->copy()->addMinutes($post->service?->duration_minutes ?? 30);

            Log::info('Booking attempt', [
                'post_id' => $post->id,
                'tenant_id' => $post->tenant_id,
                'service_id' => $post->service_id,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'status_val' => AppointmentStatus::Scheduled->value,
            ]);

            // ─── Conflict check ─────────────────
            $conflict = Appointment::query()
                ->where('tenant_id', $post->tenant_id)
                ->whereDate('start_time', $this->selectedDate)
                ->whereNotIn('status', [AppointmentStatus::Cancelled->value, AppointmentStatus::NoShow->value])
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                        ->where('end_time', '>', $start);
                })
                ->exists();

            if ($conflict) {
                $this->addError('selectedTime', 'That slot was just taken. Please pick another.');
                unset($this->timeSlots);

                return;
            }

            // ─── Vehicle (must belong to this customer) ────
            $vehicleId = null;
            if ($this->selectedVehicle) {
                $vehicleId = Vehicle::where('customer_id', Auth::id())
                    ->whereKey($this->selectedVehicle)
                    ->value('id');
            }

            // ─── Employee (must be assigned + working that window) ────
            $employeeId = null;
            if ($this->selectedEmployee) {
                $employeeId = $this->resolveEligibleEmployee($post, $start, $end);

                if (! $employeeId) {
                    $this->addError(
                        'selectedEmployee',
                        'That staff member is not available for this time. Pick another or leave blank.'
                    );

                    return;
                }
            }

            $appointment = DB::transaction(function () use ($post, $start, $end, $vehicleId, $employeeId) {
                return Appointment::create([
                    'tenant_id' => $post->tenant_id,
                    'customer_id' => Auth::id(),
                    'service_id' => $post->service_id,
                    'vehicle_id' => $vehicleId,
                    'employee_id' => $employeeId,
                    'created_by' => Auth::id(),
                    'appointment_number' => 'APT-'.strtoupper(Str::random(8)),
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => AppointmentStatus::Scheduled->value,
                    'notes' => trim($this->notes) ?: null,
                ]);
            });

            Log::info('Appointment row created', ['id' => $appointment->id]);

            $appointment->tenant?->user?->notify(new AppointmentBooked($appointment));

            Log::info('Notification sent');

            session()->flash('booking_success', $appointment->appointment_number);

            return redirect()->route('customer.appointment_detail', $appointment->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire show field-level errors normally
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Booking FAILED', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'type' => get_class($e),
            ]);

            $this->addError('general', get_class($e).': '.$e->getMessage());

            return;
        }
    }

    /**
     * Verify the employee still belongs to the shop, is active,
     * and their schedule covers the requested window.
     * Returns the ID or null if invalid.
     */
    protected function resolveEligibleEmployee(Post $post, Carbon $start, Carbon $end): ?int
    {
        $dayIso = $start->dayOfWeekIso;
        $startStr = $start->format('H:i');
        $endStr = $end->format('H:i');

        $employee = Employee::where('tenant_id', $post->tenant_id)
            ->where('is_active', true)
            ->whereKey($this->selectedEmployee)
            ->whereHas('schedules', function ($q) use ($dayIso, $startStr, $endStr) {
                $q->where('day_of_week', $dayIso)
                    ->where('start_time', '<=', $startStr)
                    ->where('end_time', '>=', $endStr);
            })
            ->first();

        return $employee?->id;
    }

    /**
     * True if the shop has no employees (unscheduled shops always pass),
     * OR at least one active employee works the full window.
     */
    protected function isSlotStaffed(Post $post, Carbon $start, Carbon $end): bool
    {
        $hasEmployees = Employee::where('tenant_id', $post->tenant_id)
            ->where('is_active', true)
            ->exists();

        if (! $hasEmployees) {
            return true;
        }

        $dayIso = $start->dayOfWeekIso;
        $startStr = $start->format('H:i');
        $endStr = $end->format('H:i');

        return Employee::where('tenant_id', $post->tenant_id)
            ->where('is_active', true)
            ->whereHas('schedules', function ($q) use ($dayIso, $startStr, $endStr) {
                $q->where('day_of_week', $dayIso)
                    ->where('start_time', '<=', $startStr)
                    ->where('end_time', '>=', $endStr);
            })
            ->exists();
    }
};