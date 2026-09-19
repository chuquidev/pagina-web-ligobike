<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->string('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return CategoryResource::collection(
            $query->orderBy('order')->paginate(12)->appends($request->query())
        );
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $category->addMedia($request->file('image'))->toMediaCollection('image');
        }

        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $category->addMedia($request->file('image'))->toMediaCollection('image');
        }

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar una categoría que tiene productos asociados.',
            ], 422);
        }

        $category->delete();

        return response()->noContent();
    }
}
