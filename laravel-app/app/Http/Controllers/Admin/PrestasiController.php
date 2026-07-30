<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrestasiController extends Controller
{
    /**
     * GET /admin/prestasi/list
     * cms.js expects: { data: [...], meta: { total, page, per_page, total_pages } }
     * Item keys: id, prestasi_id, title, member_name, institution, year, category, rank, status, photo, slug
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->input('per_page', 25)));
        $page    = max(1, (int) $request->input('page', 1));
        $q       = $request->input('q', '');
        $status  = $request->input('status', '');
        $cat     = $request->input('category', '');
        $year    = $request->input('year', '');

        $query = Prestasi::query();

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                   ->orWhere('member_name', 'like', "%{$q}%")
                   ->orWhere('institution', 'like', "%{$q}%");
            });
        }
        if ($status !== '') $query->where('status', $status);
        if ($cat !== '')    $query->where('category', $cat);
        if ($year !== '')   $query->where('year', $year);

        $total      = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $items      = $query->orderByDesc('created_at')->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $data = $items->map(function ($p) {
            $photo = $p->photo ?? '';
            return [
                'id'          => $p->prestasi_id,
                'prestasi_id' => $p->prestasi_id,
                'title'       => $p->title ?? '',
                'member_name' => $p->member_name ?? '',
                'institution' => $p->institution ?? '',
                'year'        => $p->year ?? '',
                'category'    => $p->category ?? '',
                'rank'        => $p->rank ?? '',
                'detail'      => $p->detail ?? '',
                'description' => $p->description ?? '',
                'photo'       => $p->resolveImageUrl($photo),
                'slug'        => $p->slug ?? Str::slug($p->title ?? 'prestasi') . '-' . $p->prestasi_id,
                'status'      => $p->status ?? 'published',
                'is_featured' => (bool) ($p->is_featured ?? false),
                'meta_title'       => $p->meta_title ?? '',
                'meta_keyword'     => $p->meta_keyword ?? '',
                'meta_description' => $p->meta_description ?? '',
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    /**
     * GET /admin/prestasi/{id}
     */
    public function show($id)
    {
        $p     = Prestasi::findOrFail($id);
        $photo = $p->photo ?? '';

        return response()->json(['success' => true, 'data' => [
            'id'          => $p->prestasi_id,
            'prestasi_id' => $p->prestasi_id,
            'title'       => $p->title ?? '',
            'member_name' => $p->member_name ?? '',
            'name'        => $p->member_name ?? '',
            'institution' => $p->institution ?? '',
            'year'        => $p->year ?? '',
            'category'    => $p->category ?? '',
            'rank'        => $p->rank ?? '',
            'detail'      => $p->detail ?? '',
            'content'     => $p->detail ?? '',
            'description' => $p->description ?? '',
            'photo'       => $p->resolveImageUrl($photo),
            'image'       => $p->resolveImageUrl($photo),
            'slug'        => $p->slug ?? Str::slug($p->title ?? 'prestasi') . '-' . $p->prestasi_id,
            'status'      => $p->status ?? 'published',
            'is_featured' => (bool) ($p->is_featured ?? false),
            'meta_title'       => $p->meta_title ?? '',
            'meta_keyword'     => $p->meta_keyword ?? '',
            'meta_description' => $p->meta_description ?? '',
        ]]);
    }

    /**
     * POST /admin/prestasi
     */
    public function store(Request $request)
    {
        $request->merge($this->databasePayload($request));
        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'member_name' => 'nullable|string|max:500',
            'institution' => 'nullable|string|max:500',
            'year'        => 'nullable|integer',
            'category'    => 'nullable|string|max:255',
            'rank'        => 'nullable|string|max:255',
            'detail'      => 'nullable|string',
            'description' => 'nullable|string',
            'photo'       => 'nullable|string',
            'status'      => 'nullable|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title'       => 'nullable|string|max:500',
            'meta_keyword'     => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:1000',
        ]);

        $validated['slug']   = Str::slug($validated['title']) . '-' . time();
        $validated['status'] = $validated['status'] ?? 'draft';

        $prestasi = Prestasi::create($validated);
        return response()->json(['success' => true, 'message' => 'Prestasi berhasil disimpan.', 'data' => ['id' => $prestasi->prestasi_id]]);
    }

    /**
     * POST /admin/prestasi/{id}/update
     */
    public function update(Request $request, $id)
    {
        $prestasi = Prestasi::findOrFail($id);
        $request->merge($this->databasePayload($request));

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:500',
            'member_name' => 'nullable|string|max:500',
            'institution' => 'nullable|string|max:500',
            'year'        => 'nullable|integer',
            'category'    => 'nullable|string|max:255',
            'rank'        => 'nullable|string|max:255',
            'detail'      => 'nullable|string',
            'description' => 'nullable|string',
            'photo'       => 'nullable|string',
            'status'      => 'nullable|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title'       => 'nullable|string|max:500',
            'meta_keyword'     => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $id;
        }

        $prestasi->update($validated);
        return response()->json(['success' => true, 'message' => 'Prestasi berhasil diperbarui.', 'data' => ['id' => $prestasi->prestasi_id]]);
    }

    /**
     * POST /admin/prestasi/{id}/delete
     */
    public function destroy($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        $prestasi->delete();
        return response()->json(['success' => true, 'message' => 'Prestasi berhasil dihapus.']);
    }

    /**
     * POST /admin/prestasi/upload
     */
    public function upload(Request $request)
    {
        $request->validate(['image' => 'required|file|image|max:5120']);
        $file     = $request->file('image');
        $filename = uniqid('prestasi_', true) . '.' . $file->getClientOriginalExtension();
        $dest     = public_path('uploads/prestasi');
        if (!is_dir($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $filename);
        return response()->json([
            'success' => 1,
            'url'     => url('/uploads/prestasi/' . $filename),
            'file'    => ['url' => url('/uploads/prestasi/' . $filename)],
        ]);
    }

    private function databasePayload(Request $request): array
    {
        $input = $request->all();
        if (!array_key_exists('member_name', $input) && array_key_exists('name', $input)) {
            $input['member_name'] = $input['name'];
        }
        if (!array_key_exists('detail', $input) && array_key_exists('content', $input)) {
            $input['detail'] = $input['content'];
        }
        if (!array_key_exists('photo', $input) && array_key_exists('image', $input)) {
            $input['photo'] = $input['image'];
        }
        return $input;
    }
}
