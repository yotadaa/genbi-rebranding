<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoGalleryController extends Controller
{
    public function index()
    {
        $photos = PhotoGallery::latest()->paginate(12);
        return response()->json(['success' => true, 'data' => $photos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image',
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        $photo = PhotoGallery::create([
            'title' => $request->title,
            'image' => $path
        ]);

        return response()->json(['success' => true, 'message' => 'Photo added.', 'data' => $photo]);
    }

    public function destroy($id)
    {
        $photo = PhotoGallery::findOrFail($id);
        if ($photo->image) Storage::disk('public')->delete($photo->image);
        $photo->delete();
        return response()->json(['success' => true, 'message' => 'Photo deleted.']);
    }
}
