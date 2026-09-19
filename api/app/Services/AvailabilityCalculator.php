<?php

namespace App\Services;

use App\Models\MaintenanceAppointment;
use App\Models\MaintenanceSetting;
use Carbon\Carbon;

class AvailabilityCalculator
{
    public function __construct(private MaintenanceSetting $settings) {}

    public function slotsFor(Carbon $date, int $durationMinutes): array
    {
        $dayKey = strtolower($date->format('l'));
        $hours = $this->settings->business_hours[$dayKey] ?? null;

        if (empty($hours['open']) || empty($hours['close'])) {
            return [];
        }

        $open = Carbon::parse($date->toDateString() . ' ' . $hours['open']);
        $close = Carbon::parse($date->toDateString() . ' ' . $hours['close']);
        $interval = $this->settings->slot_interval_minutes;
        $capacity = $this->settings->capacity;
        $earliestAllowed = now()->addHours($this->settings->min_notice_hours);

        $existing = MaintenanceAppointment::where('status', '!=', 'cancelled')
            ->whereDate('starts_at', $date->toDateString())
            ->get(['starts_at', 'ends_at']);

        $slots = [];
        $cursor = $open->copy();

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($close)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($durationMinutes);

            if ($slotStart->gte($earliestAllowed)) {
                $overlapping = $existing->filter(
                    fn($appt) => $slotStart->lt($appt->ends_at) && $slotEnd->gt($appt->starts_at)
                )->count();

                if ($overlapping < $capacity) {
                    $slots[] = $slotStart->format('H:i');
                }
            }

            $cursor->addMinutes($interval);
        }

        return $slots;
    }
}
