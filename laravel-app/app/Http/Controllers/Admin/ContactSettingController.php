<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactSetting;

class ContactSettingController extends Controller
{
    public function show()
    {
        return response()->json(['success' => true, 'data' => ContactSetting::find(1)]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'place_name' => 'required|string|max:120',
            'coordinates_label' => 'required|string|max:160',
            'maps_url' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'meta_title' => 'nullable|string|max:255',
            'meta_keyword' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $settings = ContactSetting::updateOrCreate(['id' => 1], $request->only(['place_name', 'address', 'email', 'phone', 'coordinates_label', 'maps_url', 'latitude', 'longitude', 'meta_title', 'meta_keyword', 'meta_description']));
        return response()->json(['success' => true, 'data' => $settings, 'message' => 'Contact settings updated.']);
    }
}
