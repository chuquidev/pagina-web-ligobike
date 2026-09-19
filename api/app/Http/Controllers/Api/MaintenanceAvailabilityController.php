<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceService;
use App\Models\MaintenanceSetting;
use App\Services\AvailabilityCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaintenanceAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'service_id' => ['required', 'exists:maintenance_services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = MaintenanceService::findOrFail($request->service_id);
        $settings = MaintenanceSetting::current();
        $date = Carbon::parse($request->date);

        $calculator = new AvailabilityCalculator($settings);
        $slots = $calculator->slotsFor($date, $service->duration_minutes);

        return response()->json(['slots' => $slots]);
    }
}
