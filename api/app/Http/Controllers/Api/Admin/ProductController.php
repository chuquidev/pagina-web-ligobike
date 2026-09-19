<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhere('sku', 'ilike', '%' . $term . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->integer('brand'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return ProductResource::collection(
            $query->latest()->paginate(12)->appends($request->query())
        );
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        foreach ($request->file('images', []) as $image) {
            $product->addMedia($image)->toMediaCollection('images');
        }

        return new ProductResource($product->load('category'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        foreach ($request->file('images', []) as $image) {
            $product->addMedia($image)->toMediaCollection('images');
        }

        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return new ProductResource($product->load('category'));
    }

    public function deleteImage(Product $product, int $mediaId)
    {
        $product->media()->findOrFail($mediaId)->delete();

        return response()->noContent();
    }
}
