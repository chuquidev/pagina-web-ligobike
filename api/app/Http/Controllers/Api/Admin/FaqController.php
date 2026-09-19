<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('search')) {
            $query->where('question', 'ilike', '%' . $request->string('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return FaqResource::collection(
            $query->orderBy('order')->paginate(12)->appends($request->query())
        );
    }

    public function store(FaqRequest $request)
    {
        return new FaqResource(Faq::create($request->validated()));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());
        return new FaqResource($faq);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->noContent();
    }
}
