<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Services\SeoService;
use App\Services\StructuredData;

final class AboutController
{
    public function __construct(private ViewRenderer $viewRenderer) {}

    public function index(Request $request, Response $response): void
    {
        $seo = SeoService::forPage('about.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'About', 'url' => '/about'],
        ]);

        $html = $this->viewRenderer->renderWithLayout('public/about/index.php', 'layouts/public.php', [
            'meta' => $meta,
            'jsonld' => $jsonld,
            'bodyClass' => 'page-about',
            'activeNav' => 'about',
            'scripts' => '<script defer src="/assets/js/pages/about.js"></script>',
        ]);
        $response->html($html);
    }
}
