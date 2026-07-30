<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Services\ImageResolver;
use App\Services\SeoService;

class FeatureController extends Controller
{
    public function index()
    {
        $programs = Feature::with('images')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Feature $feature): array {
                $images = $feature->images
                    ->map(static fn ($image): string => ImageResolver::resolve($image->image_path, ''))
                    ->filter()
                    ->values()
                    ->all();

                if ($images === []) {
                    $images = ['/uploads/slider-1.png'];
                }

                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'title' => $feature->title ?: $feature->name,
                    'description' => $feature->description ?: $feature->content,
                    'focus' => $feature->focus,
                    'icon_key' => $feature->icon_key ?: $feature->icon ?: 'sparkles',
                    'images' => $images,
                ];
            })
            ->values()
            ->all();

        return view('public.feature.index', [
            'programs' => $programs,
            'activeNav' => 'feature',
            'meta' => SeoService::renderMetaBlock(SeoService::forPage('feature.html')),
            'scripts' => '<script defer src="/assets/js/dist/pages/feature.js?v=20260730a"></script>',
        ]);
    }
}
