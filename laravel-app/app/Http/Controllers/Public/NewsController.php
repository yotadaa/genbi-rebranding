<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', $request->input('limit', 12));
        $page = $request->input('page', 1);
        $activeQ = $request->input('q');
        $activeCategory = $request->input('category');

        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-1.png');
        };

        $query = News::published()->with('category');
        
        if ($activeQ !== null && $activeQ !== '' && $activeQ !== 'Semua' && $activeQ !== 'All') {
            $query->where(function($q) use ($activeQ) {
                $q->where('news_title', 'like', '%' . $activeQ . '%')
                  ->orWhere('news_content', 'like', '%' . $activeQ . '%');
            });
        }
        
        if ($activeCategory !== null && $activeCategory !== '' && $activeCategory !== 'Semua' && $activeCategory !== 'All') {
            $query->whereHas('category', function($q) use ($activeCategory) {
                $q->where('category_name', $activeCategory);
            });
        }
        
        $paginator = $query->latestNews()->paginate($perPage);
        
        $items = $paginator->map(function($news) use ($resolveImageUrl) {
            return [
                'id' => $news->news_id,
                'slug' => $news->slug,
                'title' => $news->news_title,
                'excerpt' => $news->news_content_short ?: mb_strimwidth(strip_tags($news->news_content), 0, 150, '...'),
                'date' => $news->published_at ?: ($news->news_date ?: $news->created_at),
                'image' => $resolveImageUrl($news->photo ?: $news->banner),
                'category' => $news->category ? $news->category->category_name : 'Berita GenBI',
            ];
        })->values()->toArray();

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'items' => $items,
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]);
        }
        
        return view('public.news.index', [
            'items' => $items,
            'filters' => [
                'q' => $activeQ ?? '',
                'category' => $activeCategory ?? ''
            ],
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/news.js"></script>',
        ]);
    }

    public function show(Request $request, $slug)
    {
        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-1.png');
        };

        $newsModel = News::published()->with('category')->where('slug', $slug)->first();
        
        if (!$newsModel) {
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'Not found'], 404);
            }
            abort(404);
        }

        $newsItem = [
            'id' => $newsModel->news_id,
            'slug' => $newsModel->slug,
            'title' => $newsModel->news_title,
            'content' => $newsModel->news_content,
            'excerpt' => $newsModel->news_content_short,
            'date' => $newsModel->published_at ?: ($newsModel->news_date ?: $newsModel->created_at),
            'image' => $resolveImageUrl($newsModel->photo ?: $newsModel->banner),
            'category' => $newsModel->category ? $newsModel->category->category_name : 'Berita GenBI',
            'author' => current(array_filter([$newsModel->contributor_pewarta, 'GenBI Jambi'])),
            'editor' => $newsModel->contributor_editor,
        ];

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json(['data' => $newsItem]);
        }
        
        return view('public.news.show', [
            'item' => $newsItem,
            'newsItem' => $newsItem,
            'scripts' => '<script defer src="/assets/js/dist/pages/news-detail.js"></script>',
        ]);
    }

    public function legacyShow($id)
    {
        $newsModel = News::find($id);
        
        if (!$newsModel || !$newsModel->slug) {
            abort(404);
        }
        
        return redirect()->route('news.show', ['slug' => $newsModel->slug], 301);
    }
}
