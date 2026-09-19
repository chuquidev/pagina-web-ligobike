<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index()
    {
        $data = Cache::remember('public.brands', 300, function () {
            return Brand::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn(Brand $brand) => (new BrandResource($brand))->resolve())
                ->all();
        });

        return response()->json(['data' => $data]);
    }
}
