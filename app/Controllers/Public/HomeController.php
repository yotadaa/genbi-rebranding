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

final class HomeController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Feature $featureModel = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $seo = SeoService::forPage('index.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization();

        if ($this->viewRenderer instanceof ViewRenderer && $this->featureModel instanceof Feature) {
            $html = $this->viewRenderer->renderWithLayout('public/home/index.php', 'layouts/public.php', [
                'programs' => $this->featureModel?->homeVisible(12) ?? [],
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-home',
                'scripts' => '<script src="/assets/js/pages/home.js?v=20260508e"></script>',
            ]);
            $response->html($html);
            return;
        }

        $response->html($this->renderer->render('index.html', ['meta' => $meta]));
    }
}
