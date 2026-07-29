<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * GET /admin/news/list
     * cms.js expects: { data: [...items], meta: { total, page, per_page, total_pages } }
     * Item keys: id, title, excerpt, photo, category, category_id, status, date, news_id
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request->input('per_page', 10)));
        $page    = max(1, (int) $request->input('page', 1));
        $q       = $request->input('q', '');
        $status  = $request->input('status', '');

        $query = News::with('category');

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('news_title', 'like', "%{$q}%")
                   ->orWhere('news_content_short', 'like', "%{$q}%");
            });
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($cats = $request->input('category')) {
            $catIds = is_array($cats) ? $cats : [$cats];
            $query->whereIn('category_id', array_map('intval', $catIds));
        }

        $total     = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $items     = $query->orderByRaw('COALESCE(published_at, news_date) DESC')
                           ->offset(($page - 1) * $perPage)
                           ->limit($perPage)
                           ->get();

        $data = $items->map(function ($n) {
            $photo = $n->photo ?? '';
            return [
                'id'          => $n->news_id,
                'news_id'     => $n->news_id,
                'title'       => $n->news_title ?? '',
                'news_title'  => $n->news_title ?? '',
                'excerpt'     => $n->news_content_short ?? Str::limit(strip_tags($n->news_content ?? ''), 120),
                'news_content_short' => $n->news_content_short ?? '',
                'date'        => $n->published_at ?? $n->news_date ?? '',
                'photo'       => $n->resolveImageUrl($photo),
                'category'    => $n->category?->category_name ?? 'Uncategorized',
                'category_id' => $n->category_id,
                'status'      => $n->status ?? 'published',
                'slug'        => $n->slug ?? Str::slug($n->news_title ?? '') . '-' . $n->news_id,
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
     * GET /admin/news/categories
     */
    public function categories()
    {
        $categories = Category::all()->map(function ($c) {
            return [
                'id'     => $c->category_id,
                'category_id' => $c->category_id,
                'name'   => $c->category_name ?? '',
                'category_name' => $c->category_name ?? '',
                'banner' => $c->category_banner ?? '',
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * GET /admin/news/{id}
     */
    public function show($id)
    {
        $n = News::with('category')->findOrFail($id);
        $photo = $n->photo ?? '';

        return response()->json(['success' => true, 'data' => [
            'id'                  => $n->news_id,
            'news_id'             => $n->news_id,
            'title'               => $n->news_title ?? '',
            'news_title'          => $n->news_title ?? '',
            'content'             => $n->news_content ?? '',
            'news_content'        => $n->news_content ?? '',
            'content_json'        => $n->content_json ?? null,
            'excerpt'             => $n->news_content_short ?? '',
            'news_content_short'  => $n->news_content_short ?? '',
            'date'                => $n->published_at ?? $n->news_date ?? '',
            'news_date'           => $n->news_date ?? '',
            'photo'               => $n->resolveImageUrl($photo),
            'banner'              => $n->resolveImageUrl((string) ($n->banner ?? '')),
            'category_id'         => $n->category_id,
            'category'            => $n->category?->category_name ?? '',
            'slug'                => $n->slug ?? Str::slug($n->news_title ?? '') . '-' . $n->news_id,
            'status'              => $n->status ?? 'published',
            'contributor_pewarta' => $n->contributor_pewarta ?? '',
            'contributor_editor'  => $n->contributor_editor ?? '',
            'meta_title'          => $n->meta_title ?? '',
            'meta_keyword'        => $n->meta_keyword ?? '',
            'meta_description'    => $n->meta_description ?? '',
        ]]);
    }

    /**
     * POST /admin/news
     */
    public function store(Request $request)
    {
        $request->merge($this->databasePayload($request));
        $validated = $request->validate([
            'news_title'         => 'required|string|max:500',
            'news_content'       => 'nullable|string',
            'news_content_short' => 'nullable|string|max:1000',
            'category_id'        => 'nullable|integer|exists:tbl_category,category_id',
            'news_date'          => 'nullable|date',
            'status'             => 'nullable|string|in:draft,published,archived',
            'photo'              => 'nullable|string',
            'slug'               => 'nullable|string|max:500',
            'meta_title'         => 'nullable|string|max:500',
            'meta_keyword'       => 'nullable|string|max:500',
            'meta_description'   => 'nullable|string|max:1000',
            'content_json'       => 'nullable',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['news_title']) . '-' . time();
        $validated['status'] = $validated['status'] ?? 'draft';

        if (!empty($validated['content_json']) && is_array($request->input('content_json'))) {
            $validated['content_json'] = json_encode($request->input('content_json'));
        }

        $news = News::create($validated);
        return response()->json(['success' => true, 'message' => 'Berita berhasil disimpan.', 'data' => ['id' => $news->news_id, 'news_id' => $news->news_id]]);
    }

    /**
     * POST /admin/news/{id}/update
     */
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $request->merge($this->databasePayload($request));

        $validated = $request->validate([
            'news_title'         => 'sometimes|string|max:500',
            'news_content'       => 'nullable|string',
            'news_content_short' => 'nullable|string|max:1000',
            'category_id'        => 'nullable|integer|exists:tbl_category,category_id',
            'news_date'          => 'nullable|date',
            'status'             => 'nullable|string|in:draft,published,archived',
            'photo'              => 'nullable|string',
            'slug'               => 'nullable|string|max:500',
            'meta_title'         => 'nullable|string|max:500',
            'meta_keyword'       => 'nullable|string|max:500',
            'meta_description'   => 'nullable|string|max:1000',
            'content_json'       => 'nullable',
        ]);

        if (isset($validated['news_title']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['news_title']) . '-' . $id;
        }

        if ($request->has('content_json') && is_array($request->input('content_json'))) {
            $validated['content_json'] = json_encode($request->input('content_json'));
        }

        $news->update($validated);
        return response()->json(['success' => true, 'message' => 'Berita berhasil diperbarui.', 'data' => ['id' => $news->news_id]]);
    }

    /**
     * POST /admin/news/{id}/delete
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return response()->json(['success' => true, 'message' => 'Berita berhasil dihapus.']);
    }

    /**
     * POST /admin/news/upload  (EditorJS image upload)
     */
    public function upload(Request $request)
    {
        $request->validate(['image' => 'required|file|image|max:5120']);
        $file     = $request->file('image');
        $filename = uniqid('news_', true) . '.' . $file->extension();
        $directory = public_path('uploads/news');
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $file->move($directory, $filename);
        $url = url('/uploads/news/' . $filename);
        return response()->json([
            'success' => 1,
            'data'    => ['url' => $url],
            'file'    => ['url' => $url],
        ]);
    }

    /** Map the shared editor's readable field names to legacy tbl_news columns. */
    private function databasePayload(Request $request): array
    {
        $input = $request->all();
        $aliases = [
            'title' => 'news_title', 'content' => 'news_content',
            'excerpt' => 'news_content_short', 'date' => 'news_date',
        ];
        foreach ($aliases as $from => $to) {
            if (!array_key_exists($to, $input) && array_key_exists($from, $input)) {
                $input[$to] = $input[$from];
            }
        }
        if (array_key_exists('photo', $input)) {
            $input['photo'] = $this->storagePath($input['photo']);
        }
        if (array_key_exists('category_id', $input) && (int) $input['category_id'] <= 0) {
            $input['category_id'] = null;
        }
        return $input;
    }

    /** tbl_news.photo stores a short uploads-relative path, not a full application URL. */
    private function storagePath(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') return '';
        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && preg_match('#^/?uploads/#i', $path)) {
            $value = $path;
        }
        $value = preg_replace('#^/?uploads/#i', '', $value) ?? $value;
        return ltrim($value, '/');
    }
}
