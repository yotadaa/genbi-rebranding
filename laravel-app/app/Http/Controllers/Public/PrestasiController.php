<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prestasi;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $activeQ = $request->input('q');
        $activeCategory = $request->input('category');
        $activeYear = $request->input('year');
        $layout = $request->input('layout', 'grid');

        $resolveImageUrl = function($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            if (str_starts_with($path, '/uploads/')) return url($path);
            if (str_starts_with($path, 'uploads/')) return url('/' . $path);
            return url('/uploads/' . ltrim($path, '/'));
        };

        $query = Prestasi::published()->latestPrestasi();

        if ($activeQ) {
            $query->where('title', 'like', '%' . $activeQ . '%');
        }
        if ($activeCategory) {
            $query->where('category', $activeCategory);
        }
        if ($activeYear) {
            $query->where('year', $activeYear);
        }

        $paginator = $query->paginate($perPage);

        $items = $paginator->map(function($p) use ($resolveImageUrl) {
            return [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'category' => $p->category,
                'year' => $p->year,
                'description' => $p->description,
                'name' => current(array_filter([$p->member_name, $p->institution_name, ''])),
                'institution' => current(array_filter([$p->institution, $p->campus_name, ''])),
                'image' => $resolveImageUrl($p->photo),
            ];
        })->toArray();

        $categories = Prestasi::select('category')->distinct()->pluck('category')->filter()->toArray();
        $years = Prestasi::select('year')->distinct()->pluck('year')->filter()->toArray();
        rsort($years);

        return view('public.prestasi.index', [
            'items' => $items,
            'filters' => [
                'q' => $activeQ,
                'category' => $activeCategory,
                'year' => $activeYear,
                'layout' => $layout
            ],
            'filterOptions' => [
                'categories' => $categories,
                'years' => $years
            ],
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi.js"></script>',
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

        $prestasiItem = Prestasi::published()->where('slug', $slug)->first();
        
        if (!$prestasiItem) {
            abort(404);
        }

        $item = [
            'id' => $prestasiItem->id,
            'slug' => $prestasiItem->slug,
            'title' => $prestasiItem->title,
            'category' => $prestasiItem->category,
            'year' => $prestasiItem->year,
            'description' => $prestasiItem->description,
            'content' => $prestasiItem->detail ?? $prestasiItem->content,
            'name' => current(array_filter([$prestasiItem->member_name, $prestasiItem->institution_name, ''])),
            'institution' => current(array_filter([$prestasiItem->institution, $prestasiItem->campus_name, ''])),
            'image' => $resolveImageUrl($prestasiItem->photo),
        ];

        return view('public.prestasi.show', [
            'item' => $mappedItem,
            'seo' => [
                'canonical' => url()->current()
            ],
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi-detail.js"></script>',
        ]);
    }
}
