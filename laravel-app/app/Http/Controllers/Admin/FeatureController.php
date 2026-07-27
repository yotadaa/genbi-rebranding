<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::all();
        return response()->json(['success' => true, 'data' => $features]);
    }

    public function show($id)
    {
        $feature = Feature::findOrFail($id);
        return response()->json(['success' => true, 'data' => $feature]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('features', 'public');
        }

        $feature = Feature::create($validated);
        return response()->json(['success' => true, 'message' => 'Feature created.', 'data' => $feature]);
    }

    public function update(Request $request, $id)
    {
        $feature = Feature::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($feature->image) Storage::disk('public')->delete($feature->image);
            $validated['image'] = $request->file('image')->store('features', 'public');
        }

        $feature->update($validated);
        return response()->json(['success' => true, 'message' => 'Feature updated.', 'data' => $feature]);
    }

    public function destroy($id)
    {
        $feature = Feature::findOrFail($id);
        if ($feature->image) Storage::disk('public')->delete($feature->image);
        $feature->delete();
        return response()->json(['success' => true, 'message' => 'Feature deleted.']);
    }
}
