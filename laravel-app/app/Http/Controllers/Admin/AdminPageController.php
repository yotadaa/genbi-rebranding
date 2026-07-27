<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPageController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = [
            'news' => \App\Models\News::count(),
            'prestasi' => \App\Models\Prestasi::count(),
            'comments' => \App\Models\NewsComment::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', [
            'title' => 'Dashboard | Admin GenBI',
            'cmsPage' => 'dashboard',
            'cmsMode' => 'list',
            'stats' => $stats,
            'scripts' => '<script defer src="/assets/js/dist/admin/dashboard.js?v=20260616g"></script>',
        ]);
    }

    public function newsIndex(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 25);
        $query = \App\Models\News::with('category');

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('news_title', 'like', "%{$q}%")
                   ->orWhere('news_content', 'like', "%{$q}%");
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($cats = $request->query('category')) {
            $catIds = is_array($cats) ? $cats : [$cats];
            $query->whereIn('category_id', $catIds);
        }

        $total = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $newsModels = $query->latest('news_date')->offset(($page - 1) * $perPage)->limit($perPage)->get();

        $items = $newsModels->map(function ($n) {
            return [
                'id' => $n->news_id ?? $n->id,
                'title' => $n->news_title ?? $n->title,
                'date' => $n->news_date ?? $n->created_at,
                'excerpt' => Str::limit(strip_tags($n->news_content ?? $n->content ?? ''), 100),
                'photo' => $n->resolveImageUrl($n->photo ?? ''),
                'category' => $n->category?->category_name ?? $n->category?->name ?? 'Uncategorized',
                'status' => $n->status ?? 'published',
            ];
        })->toArray();

        $categories = \App\Models\Category::all()->map(function ($c) {
            return [
                'id' => $c->category_id ?? $c->id,
                'name' => $c->category_name ?? $c->name,
            ];
        })->toArray();

        $filters = [
            'q' => $request->query('q', ''),
            'status' => $request->query('status', ''),
        ];
        $selectedCategories = is_array($request->query('category')) ? $request->query('category') : ($request->query('category') ? [$request->query('category')] : []);
        $layout = $request->query('layout', 'list');

        return view('admin.news.index', [
            'title' => 'View News | Admin GenBI',
            'cmsPage' => 'news',
            'cmsMode' => 'list',
            'items' => $items,
            'categories' => $categories,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'selectedCategories' => $selectedCategories,
            'layout' => $layout,
            'scripts' => '<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>',
        ]);
    }

    public function newsAdd()
    {
        $categories = \App\Models\Category::all()->map(function ($c) {
            return ['id' => $c->category_id ?? $c->id, 'name' => $c->category_name ?? $c->name];
        })->toArray();

        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;

        return view('admin.news.form', [
            'title' => 'Add News | Admin GenBI',
            'cmsPage' => 'news-add',
            'cmsMode' => 'editor',
            'isEdit' => false,
            'item' => null,
            'categories' => $categories,
            'scripts' => $editorScripts,
        ]);
    }

    public function newsEdit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $n = \App\Models\News::findOrFail($id);
        $item = [
            'id' => $n->news_id ?? $n->id,
            'title' => $n->news_title ?? $n->title,
            'excerpt' => $n->news_content_short ?? Str::limit(strip_tags($n->news_content ?? ''), 100),
            'content' => $n->news_content ?? $n->content,
            'date' => $n->news_date ?? $n->created_at,
            'category_id' => $n->category_id,
            'photo' => $n->resolveImageUrl($n->photo ?? ''),
            'contributor_pewarta' => $n->contributor_pewarta ?? '',
            'contributor_editor' => $n->contributor_editor ?? '',
            'meta_title' => $n->meta_title ?? '',
            'meta_keyword' => $n->meta_keyword ?? '',
            'meta_description' => $n->meta_description ?? '',
            'status' => $n->status ?? 'draft',
        ];

        $categories = \App\Models\Category::all()->map(function ($c) {
            return ['id' => $c->category_id ?? $c->id, 'name' => $c->category_name ?? $c->name];
        })->toArray();

        $editorScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script defer src="/assets/js/dist/admin/cms.js?v=20260617a"></script>
HTML;

        return view('admin.news.form', [
            'title' => ($item['title'] . ' - Edit') . ' | Admin GenBI',
            'cmsPage' => 'news-edit',
            'cmsMode' => 'editor',
            'isEdit' => true,
            'item' => $item,
            'categories' => $categories,
            'scripts' => $editorScripts,
        ]);
    }

    public function show(Request $request, $page, $sub = null)
    {
        $pageName = $sub ? "{$page}-{$sub}" : $page;
        $viewName = "admin.{$pageName}";

        // Try fallback html file extraction first if view doesn't exist or to maintain SPA compatibility
        $htmlPath = base_path('../fallbacks/admin/' . $pageName . '.html');
        if (!file_exists($htmlPath)) {
            $htmlPath = base_path('fallbacks/admin/' . $pageName . '.html');
        }

        if (file_exists($htmlPath)) {
            $html = file_get_contents($htmlPath);
            preg_match('/<title>(.*?)<\/title>/si', $html, $titleMatch);
            preg_match('/<body[^>]*data-cms-page="([^"]*)"[^>]*data-cms-mode="([^"]*)"[^>]*>/si', $html, $bodyMatch);
            preg_match('/<main[^>]*>(.*?)<\/main>/si', $html, $contentMatch);
            preg_match_all('/<script\b[^>]*>.*?<\/script>/si', $html, $scriptMatches);

            $scripts = array_filter($scriptMatches[0] ?? [], function ($script) {
                return !str_contains($script, '/assets/js/data.js')
                    && !str_contains($script, '/assets/js/api-core.js')
                    && !str_contains($script, '/assets/js/api.js')
                    && !str_contains($script, '/assets/js/app.js')
                    && !str_contains($script, '/assets/js/lib/ui.js')
                    && !str_contains($script, '/assets/js/admin/admin.js');
            });

            $scripts = array_map(function ($script) {
                $script = str_replace(
                    ['../assets/js/admin/cms.js', '/assets/js/admin/cms.js'],
                    '/assets/js/dist/admin/cms.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/settings.js', '/assets/js/admin/settings.js'],
                    '/assets/js/dist/admin/settings.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/page.js', '/assets/js/admin/page.js'],
                    '/assets/js/dist/admin/page.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/genbi-point.js', '/assets/js/admin/genbi-point.js'],
                    '/assets/js/dist/admin/genbi-point.js?v=20260617a',
                    $script
                );
                $script = str_replace(
                    ['../assets/js/admin/presensi.js', '/assets/js/admin/presensi.js'],
                    '/assets/js/dist/admin/presensi.js?v=20260617a',
                    $script
                );
                if (str_contains($script, ' src=') && !preg_match('/\sdefer\b/i', $script)) {
                    return preg_replace('/<script\b/i', '<script defer', $script, 1) ?? $script;
                }
                return $script;
            }, $scripts);

            return view('admin.static-shell', [
                'title' => trim(strip_tags($titleMatch[1] ?? 'Admin GenBI')),
                'cmsPage' => $bodyMatch[1] ?? $pageName,
                'cmsMode' => $bodyMatch[2] ?? 'list',
                'staticContent' => trim($contentMatch[1] ?? ''),
                'scripts' => implode('', $scripts),
            ]);
        }

        if (view()->exists($viewName)) {
            return view($viewName);
        }

        return abort(404);
    }
}
