<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Category::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_category,name',
        ]);
        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);
        return response()->json(['success' => true, 'message' => 'Category created.', 'data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_category,name,'.$id,
        ]);
        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);
        return response()->json(['success' => true, 'message' => 'Category updated.', 'data' => $category]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
