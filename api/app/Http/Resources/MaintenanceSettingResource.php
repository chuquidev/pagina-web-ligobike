<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'business_hours' => $this->business_hours,
            'capacity' => $this->capacity,
            'slot_interval_minutes' => $this->slot_interval_minutes,
            'advance_booking_days' => $this->advance_booking_days,
            'min_notice_hours' => $this->min_notice_hours,
        ];
    }
}
