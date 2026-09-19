<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function index()
    {
        $data = Cache::remember('public.banners', 300, function () {
            return Banner::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(fn(Banner $banner) => (new BannerResource($banner))->resolve())
                ->all();
        });

        return response()->json(['data' => $data]);
    }
}
