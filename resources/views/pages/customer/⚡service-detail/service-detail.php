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
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public int $postId;

    public string $selectedDate = '';

    public string $selectedTime = '';

    // ─── Vehicle fields (all optional, created inline on booking) ───
    public string $plateNumber = '';
    public string $vehicleType = '';
    public string $brand = '';
    public string $model = '';
    public string $yearModel = '';

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
    public function vehicleTypes(): array
    {
        return [
            'motorcycle' => 'Motorcycle',
            'bicycle'    => 'Bicycle',
            'ebike'      => 'E-bike',
            'sedan'      => 'Sedan',
            'suv'        => 'SUV',
            'van'        => 'Van',
            'pickup'     => 'Pickup',
            'truck'      => 'Truck',
            'tricycle'   => 'Tricycle',
            'bus'        => 'Bus',
            'other'      => 'Other',
        ];
    }

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
            $dayIso = Carbon::parse($this->selectedDate)->dayOfWeekIso;
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

    #[Computed]
    public function timeSlots(): array
    {
        $post = $this->post;
        if (! $post || ! $this->selectedDate) {
            return [];
        }

        $start = Carbon::parse($this->selectedDate.' 08:00');
        $end = Carbon::parse($this->selectedDate.' 18:00');

        $dayIso = Carbon::parse($this->selectedDate)->dayOfWeekIso;

        $booked = Appointment::query()
            ->where('tenant_id', $post->tenant_id)
            ->whereDate('start_time', $this->selectedDate)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value, AppointmentStatus::NoShow->value])
            ->pluck('start_time')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->all();

        $hasEmployees = Employee::where('tenant_id', $post->tenant_id)
            ->where('is_active', true)
            ->exists();

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
        if ($this->selectedEmployee !== null && ! $this->employees->contains('id', $this->selectedEmployee)) {
            $this->selectedEmployee = null;
        }
    }

    /**
     * True if the customer typed in any vehicle field.
     * We only persist a Vehicle row when at least one field is filled.
     */
    protected function hasVehicleDetails(): bool
    {
        return trim($this->plateNumber) !== ''
            || $this->vehicleType !== ''
            || trim($this->brand) !== ''
            || trim($this->model) !== ''
            || trim($this->yearModel) !== '';
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
                'plateNumber' => ['nullable', 'string', 'max:50'],
                'vehicleType' => ['nullable', 'string', Rule::in(array_keys($this->vehicleTypes))],
                'brand' => ['nullable', 'string', 'max:100'],
                'model' => ['nullable', 'string', 'max:100'],
                'yearModel' => ['nullable', 'string', 'max:20'],
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

            $appointment = DB::transaction(function () use ($post, $start, $end, $employeeId) {
                // ─── Create vehicle inline (only if any field filled) ───
                $vehicleId = null;
                if ($this->hasVehicleDetails()) {
                    $vehicle = Vehicle::create([
                        'tenant_id'    => $post->tenant_id,
                        'customer_id'  => Auth::id(),
                        'plate_number' => trim($this->plateNumber) ?: null,
                        'vehicle_type' => $this->vehicleType ?: null,
                        'brand'        => trim($this->brand) ?: null,
                        'model'        => trim($this->model) ?: null,
                        'year_model'   => trim($this->yearModel) ?: null,
                    ]);
                    $vehicleId = $vehicle->id;
                }

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