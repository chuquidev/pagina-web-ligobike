<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->string('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return BannerResource::collection(
            $query->orderBy('order')->paginate(12)->appends($request->query())
        );
    }

    public function store(BannerRequest $request)
    {
        $banner = Banner::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $banner->addMedia($request->file('image'))->toMediaCollection('image');
        }

        return new BannerResource($banner);
    }

    public function update(BannerRequest $request, Banner $banner)
    {
        $banner->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $banner->addMedia($request->file('image'))->toMediaCollection('image');
        }

        return new BannerResource($banner);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->noContent();
    }
}
