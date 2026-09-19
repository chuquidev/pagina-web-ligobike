<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreSettingResource;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

class StoreSettingController extends Controller
{
    public function show()
    {
        $data = Cache::remember('public.settings', 300, function () {
            return (new StoreSettingResource(StoreSetting::current()))->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
