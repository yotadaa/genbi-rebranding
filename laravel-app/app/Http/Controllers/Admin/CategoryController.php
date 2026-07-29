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
        return response()->json(['success' => true, 'data' => Category::orderBy('category_name')->get()->map(fn (Category $category) => $this->map($category))->values()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_category,category_name',
        ]);
        $category = Category::create(['category_name' => $validated['name']]);
        return response()->json(['success' => true, 'message' => 'Category created.', 'data' => $this->map($category)]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_category,category_name,'.$id.',category_id',
        ]);
        $category->update(['category_name' => $validated['name']]);
        return response()->json(['success' => true, 'message' => 'Category updated.', 'data' => $this->map($category->fresh())]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if ($category->news()->exists()) {
            return response()->json(['success' => false, 'error' => 'Kategori masih digunakan oleh berita.'], 409);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }

    private function map(Category $category): array
    {
        return [
            'id' => $category->category_id,
            'category_id' => $category->category_id,
            'name' => $category->category_name ?? '',
            'category_name' => $category->category_name ?? '',
            'banner' => $category->category_banner ?? '',
            'category_banner' => $category->category_banner ?? '',
        ];
    }
}
