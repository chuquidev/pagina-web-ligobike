<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    public function index()
    {
        $data = Cache::remember('public.faqs', 300, function () {
            return Faq::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(fn(Faq $faq) => (new FaqResource($faq))->resolve())
                ->all();
        });

        return response()->json(['data' => $data]);
    }
}
