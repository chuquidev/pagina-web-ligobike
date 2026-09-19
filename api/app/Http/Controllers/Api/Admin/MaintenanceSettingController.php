<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MaintenanceSettingRequest;
use App\Http\Resources\MaintenanceSettingResource;
use App\Models\MaintenanceSetting;

class MaintenanceSettingController extends Controller
{
    public function show()
    {
        return new MaintenanceSettingResource(MaintenanceSetting::current());
    }

    public function update(MaintenanceSettingRequest $request)
    {
        $settings = MaintenanceSetting::current();
        $settings->update($request->validated());
        return new MaintenanceSettingResource($settings->fresh());
    }
}
