<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        // Cacheamos el array ya transformado (no los modelos de Eloquent): cachear
        // objetos completos puede romperse al recuperarlos ("incomplete object" al
        // deserializar). Un array plano es siempre seguro de guardar y leer.
        $data = Cache::remember('public.categories', 300, function () {
            return Category::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(fn(Category $category) => (new CategoryResource($category))->resolve())
                ->all();
        });

        return response()->json(['data' => $data]);
    }
}
