<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $page = $request->input('page', 1);
        $activeQ = $request->input('q', '');
        $activeCategory = $request->input('category', '');

        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $query = News::published()->with('category');
        
        if ($activeQ !== '') {
            $query->where(function($q) use ($activeQ) {
                $q->where('news_title', 'like', '%' . $activeQ . '%')
                  ->orWhere('news_content', 'like', '%' . $activeQ . '%');
            });
        }
        
        if ($activeCategory !== '') {
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
        })->toArray();
        
        return view('public.news.index', [
            'items' => $items,
            'filters' => [
                'q' => $activeQ,
                'category' => $activeCategory
            ],
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/news.js"></script>',
        ]);
    }

    public function show($slug)
    {
        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $newsModel = News::published()->with('category')->where('slug', $slug)->first();
        
        if (!$newsModel) {
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
