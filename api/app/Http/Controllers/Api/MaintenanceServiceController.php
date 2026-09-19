<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceServiceResource;
use App\Models\MaintenanceService;

class MaintenanceServiceController extends Controller
{
    public function index()
    {
        return MaintenanceServiceResource::collection(
            MaintenanceService::where('is_active', true)->orderBy('order')->get()
        );
    }
}
