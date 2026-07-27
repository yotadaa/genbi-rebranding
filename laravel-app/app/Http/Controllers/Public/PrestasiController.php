<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prestasi;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', $request->input('limit', 12));
        $page = $request->input('page', 1);
        $activeQ = $request->input('q');
        $activeCategory = $request->input('category');
        $activeYear = $request->input('year');
        $layout = $request->input('layout', 'grid');

        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-4.png');
        };

        $query = Prestasi::published()->latestPrestasi();

        if ($activeQ !== null && $activeQ !== '' && $activeQ !== 'Semua' && $activeQ !== 'All') {
            $query->where('title', 'like', '%' . $activeQ . '%');
        }
        if ($activeCategory !== null && $activeCategory !== '' && $activeCategory !== 'Semua' && $activeCategory !== 'All') {
            $query->where('category', $activeCategory);
        }
        if ($activeYear !== null && $activeYear !== '' && $activeYear !== 'Semua' && $activeYear !== 'All') {
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
        })->values()->toArray();

        $categories = Prestasi::select('category')->distinct()->pluck('category')->filter()->toArray();
        sort($categories);
        $years = Prestasi::select('year')->distinct()->pluck('year')->filter()->toArray();
        rsort($years);

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

        return view('public.prestasi.index', [
            'items' => $items,
            'filters' => [
                'q' => $activeQ ?? '',
                'category' => $activeCategory ?? '',
                'year' => $activeYear ?? '',
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

    public function show(Request $request, $slug)
    {
        $resolveImageUrl = function($path) {
            return \App\Services\ImageResolver::resolve($path, '/uploads/slider-4.png');
        };

        $prestasiItem = Prestasi::published()->where('slug', $slug)->first();
        
        if (!$prestasiItem) {
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'Not found'], 404);
            }
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

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json(['data' => $item]);
        }

        return view('public.prestasi.show', [
            'item' => $item,
            'seo' => [
                'canonical' => url()->current()
            ],
            'scripts' => '<script defer src="/assets/js/dist/pages/prestasi-detail.js"></script>',
        ]);
    }
}
