<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->string('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return BrandResource::collection(
            $query->orderBy('name')->paginate(12)->appends($request->query())
        );
    }

    public function store(BrandRequest $request)
    {
        return new BrandResource(Brand::create($request->validated()));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        return new BrandResource($brand);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar una marca que tiene productos asociados.',
            ], 422);
        }

        $brand->delete();
        return response()->noContent();
    }
}
