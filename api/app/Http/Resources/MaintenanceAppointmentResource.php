<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MaintenanceServiceResource;

class MaintenanceAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => new MaintenanceServiceResource($this->whenLoaded('service')),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'bike_info' => $this->bike_info,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
