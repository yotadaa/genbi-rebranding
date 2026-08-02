<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Services\SeoService;
use App\Services\StructuredData;

final class BukuController
{
    public function __construct(private ViewRenderer $viewRenderer) {}

    public function index(Request $request, Response $response): void
    {
        // Setup SEO Metadata
        $seo = SeoService::forPage('book.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Buku & Majalah', 'url' => '/buku'],
        ]);

        // Render view public/buku/index.php menggunakan layout standar website
        $html = $this->viewRenderer->renderWithLayout('public/book/index.php', 'layouts/public.php', [
            'meta' => $meta,
            'jsonld' => $jsonld,
            'bodyClass' => 'page-buku',
            'activeNav' => 'buku', // Agar menu navbar "Buku" menyala aktif
            'scripts' => '',
        ]);
        $response->html($html);
    }
}
