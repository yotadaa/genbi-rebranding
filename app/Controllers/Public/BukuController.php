<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Models\Buku;
use App\Services\SeoService;
use App\Services\StructuredData;

final class BukuController
{
    // Sekarang kita menyuntikkan ViewRenderer sekaligus Model Buku!
    public function __construct(
        private ViewRenderer $viewRenderer,
        private ?Buku $bukuModel = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        // 1. Mengelola Paginasi (Menyiapkan halaman saat ini, misal 12 buku per halaman)
        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 12, 24);

        // 2. Menampung kategori jika pengunjung mengklik filter kategori tertentu dari URL
        $kategori = $request->query('kategori') ?: null;

        // 3. Menghubungi Model untuk menarik data dari MySQL
        $books = $this->bukuModel?->getPublished($pg['per_page'], $pg['offset'], $kategori) ?? [];
        $total = $this->bukuModel?->countPublished($kategori) ?? count($books);
        $totalPages = Paginator::totalPages($total, $pg['per_page']);

        // 4. Siapkan Optimasi SEO & Breadcrumbs Google
        $seo = SeoService::forPage('book.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Buku & Majalah', 'url' => '/buku'],
        ]);

        // 5. Serahkan semua data asli ke View Frontend!
        $html = $this->viewRenderer->renderWithLayout('public/book/index.php', 'layouts/public.php', [
            'books' => $books,
            'page' => $pg['page'],
            'perPage' => $pg['per_page'],
            'total' => $total,
            'totalPages' => $totalPages,
            'kategori Aktif' => $kategori ?? 'Semua',
            'meta' => $meta,
            'jsonld' => $jsonld,
            'bodyClass' => 'page-buku',
            'activeNav' => 'buku',
        ]);

        $response->html($html);
    }
}
