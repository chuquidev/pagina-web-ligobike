<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSettingRequest;
use App\Http\Resources\StoreSettingResource;
use App\Models\StoreSetting;

class StoreSettingController extends Controller
{
    public function show()
    {
        return new StoreSettingResource(StoreSetting::current());
    }

    public function update(StoreSettingRequest $request)
    {
        $settings = StoreSetting::current();
        $settings->update($request->safe()->except(['logo', 'about_images']));

        if ($request->hasFile('logo')) {
            $settings->addMedia($request->file('logo'))->toMediaCollection('logo');
        }

        foreach ($request->file('about_images', []) as $image) {
            $settings->addMedia($image)->toMediaCollection('about');
        }

        return new StoreSettingResource($settings->fresh());
    }

    public function deleteAboutImage(int $mediaId)
    {
        $settings = StoreSetting::current();
        $settings->media()->where('id', $mediaId)->firstOrFail()->delete();

        return response()->noContent();
    }
}
