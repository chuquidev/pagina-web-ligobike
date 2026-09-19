<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceAppointmentResource;
use App\Models\MaintenanceAppointment;
use Illuminate\Http\Request;

class MaintenanceAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceAppointment::with('service')->orderBy('starts_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->string('date'));
        }
        if ($request->filled('search')) {
            $query->where('customer_name', 'ilike', '%' . $request->string('search') . '%');
        }

        return MaintenanceAppointmentResource::collection(
            $query->paginate(12)->appends($request->query())
        );
    }

    public function updateStatus(Request $request, MaintenanceAppointment $appointment)
    {
        $request->validate(['status' => ['required', 'in:pending,confirmed,cancelled,completed']]);
        $appointment->update(['status' => $request->status]);
        return new MaintenanceAppointmentResource($appointment->load('service'));
    }

    public function destroy(MaintenanceAppointment $appointment)
    {
        $appointment->delete();
        return response()->noContent();
    }
}
