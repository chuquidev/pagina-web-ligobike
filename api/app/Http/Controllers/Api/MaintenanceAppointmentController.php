<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\MaintenanceAppointmentResource;
use App\Models\MaintenanceAppointment;
use App\Models\MaintenanceService;
use App\Models\MaintenanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceAppointmentController extends Controller
{
    public function store(AppointmentRequest $request)
    {
        $service = MaintenanceService::where('is_active', true)->findOrFail($request->maintenance_service_id);
        $settings = MaintenanceSetting::current();

        $startsAt = Carbon::parse($request->date . ' ' . $request->time);
        $endsAt = $startsAt->copy()->addMinutes($service->duration_minutes);

        return DB::transaction(function () use ($request, $service, $settings, $startsAt, $endsAt) {
            $overlapping = MaintenanceAppointment::where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->lockForUpdate()
                ->get(['id']);

            if ($overlapping->count() >= $settings->capacity) {
                return response()->json([
                    'message' => 'Ese horario ya no está disponible. Por favor elige otro.',
                ], 422);
            }

            $appointment = MaintenanceAppointment::create([
                'maintenance_service_id' => $service->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'bike_info' => $request->bike_info,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'pending',
            ]);

            return new MaintenanceAppointmentResource($appointment->load('service'));
        });
    }
}
