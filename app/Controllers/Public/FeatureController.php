<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\Feature;
use App\Services\SeoService;
use App\Services\StructuredData;

final class FeatureController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Feature $featureModel = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $programs = $this->featureModel?->published() ?? [];

        if ($request->acceptsJson()) {
            $response->json(['data' => $programs]);
            return;
        }

        $seo = SeoService::forPage('feature.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Program Utama', 'url' => '/feature'],
        ]);

        if ($this->viewRenderer instanceof ViewRenderer) {
            $response->html($this->viewRenderer->renderWithLayout('public/feature/index.php', 'layouts/public.php', [
                'programs' => $programs,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-feature',
                'scripts' => '<script defer src="/assets/js/pages/feature.js"></script>',
            ]));
            return;
        }

        $response->html($this->renderer->render('feature.html', ['meta' => $meta, 'jsonld' => $jsonld]));
    }
}
