<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MaintenanceServiceRequest;
use App\Http\Resources\MaintenanceServiceResource;
use App\Models\MaintenanceService;
use Illuminate\Http\Request;

class MaintenanceServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceService::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->string('search') . '%');
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return MaintenanceServiceResource::collection(
            $query->orderBy('order')->paginate(12)->appends($request->query())
        );
    }

    public function store(MaintenanceServiceRequest $request)
    {
        return new MaintenanceServiceResource(MaintenanceService::create($request->validated()));
    }

    public function update(MaintenanceServiceRequest $request, MaintenanceService $service)
    {
        $service->update($request->validated());
        return new MaintenanceServiceResource($service);
    }

    public function destroy(MaintenanceService $service)
    {
        if ($service->appointments()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar un servicio que tiene citas pendientes o confirmadas.',
            ], 422);
        }

        $service->delete();
        return response()->noContent();
    }
}
