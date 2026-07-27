<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrestasiController extends Controller
{
    public function index()
    {
        $prestasi = Prestasi::latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $prestasi]);
    }

    public function show($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        return response()->json(['success' => true, 'data' => $prestasi]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'year' => 'required|integer',
            'member_or_institution' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image',
            'status' => 'required|string'
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('prestasi', 'public');
        }

        $prestasi = Prestasi::create($validated);
        return response()->json(['success' => true, 'message' => 'Prestasi created.', 'data' => $prestasi]);
    }

    public function update(Request $request, $id)
    {
        $prestasi = Prestasi::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer',
            'member_or_institution' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image',
            'status' => 'sometimes|string'
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            if ($prestasi->image) Storage::disk('public')->delete($prestasi->image);
            $validated['image'] = $request->file('image')->store('prestasi', 'public');
        }

        $prestasi->update($validated);
        return response()->json(['success' => true, 'message' => 'Prestasi updated.', 'data' => $prestasi]);
    }

    public function destroy($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        if ($prestasi->image) Storage::disk('public')->delete($prestasi->image);
        $prestasi->delete();
        return response()->json(['success' => true, 'message' => 'Prestasi deleted.']);
    }
}
