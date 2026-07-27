<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('category')->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $news]);
    }

    public function categories()
    {
        $categories = Category::all();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        return response()->json(['success' => true, 'data' => $news]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:tbl_category,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $news = News::create($validated);

        return response()->json(['success' => true, 'message' => 'News created.', 'data' => $news]);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:tbl_category,id',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image' => 'nullable|image',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            if ($news->image) Storage::disk('public')->delete($news->image);
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($validated);

        return response()->json(['success' => true, 'message' => 'News updated.', 'data' => $news]);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        if ($news->image) Storage::disk('public')->delete($news->image);
        $news->delete();

        return response()->json(['success' => true, 'message' => 'News deleted.']);
    }
    
    public function upload(Request $request)
    {
        $request->validate(['upload' => 'required|image']);
        $path = $request->file('upload')->store('news/uploads', 'public');
        return response()->json([
            'url' => Storage::url($path)
        ]);
    }
}
