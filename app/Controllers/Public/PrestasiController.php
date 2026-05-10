<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\ErrorHandler;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Services\HtmlSanitizer;
use App\Services\SeoService;
use App\Services\StructuredData;

class PrestasiController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Prestasi $prestasi = null,
        private ?PrestasiToken $tokenModel = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 24);
            $items = $this->prestasi?->published($pg['per_page'], $pg['offset']) ?? [];
            $total = $this->prestasi?->countPublished() ?? count($items);
            $response->json([
                'data' => array_map(fn (array $item): array => $this->sanitizePublicItem($item), $items),
                'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
            ]);
            return;
        }

        $seo = SeoService::forPage('prestasi.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Prestasi', 'url' => '/prestasi'],
        ]);

        if ($this->viewRenderer instanceof ViewRenderer) {
            $pg = Paginator::resolve([
                'page' => $request->query('page'),
                'per_page' => $request->query('per_page'),
            ], 12, 24);
            $items = $this->prestasi?->published($pg['per_page'], $pg['offset']) ?? [];
            $total = $this->prestasi?->countPublished() ?? count($items);
            $totalPages = Paginator::totalPages($total, $pg['per_page']);

            $html = $this->viewRenderer->renderWithLayout('public/prestasi/index.php', 'layouts/public.php', [
                'items' => $items,
                'page' => $pg['page'],
                'perPage' => $pg['per_page'],
                'total' => $total,
                'totalPages' => $totalPages,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-prestasi',
                'scripts' => '<script defer src="/assets/js/dist/pages/prestasi.js"></script>',
            ]);
            $response->html($html);
            return;
        }

        $response->html($this->renderer->render('prestasi.html', ['meta' => $meta]));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $slug = mb_substr($params['slug'] ?? '', 0, 255);

        if ($request->acceptsJson()) {
            $item = $this->prestasi?->findBySlug($slug);
            if (!$item) {
                $response->json(['error' => 'Not found'], 404);
                return;
            }
            $response->json(['data' => $this->sanitizePublicItem($item)]);
            return;
        }

        $item = $this->prestasi?->findBySlug($slug);
        if (is_array($item)) {
            $item = $this->sanitizePublicItem($item);
        }
        if (is_array($item)) {
            $seo = SeoService::forPrestasi($item);
        } else {
            $seo = SeoService::forPage('prestasi.html');
        }
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = is_array($item)
            ? StructuredData::organization() . PHP_EOL . '  ' . StructuredData::breadcrumbs([
                ['name' => 'Beranda', 'url' => '/'],
                ['name' => 'Prestasi', 'url' => '/prestasi'],
                ['name' => $item['title'] ?? 'Detail', 'url' => '/prestasi/' . ($item['slug'] ?? $slug)],
            ])
            : '';

        if (!is_array($item)) {
            ErrorHandler::render($response, 404, 'Prestasi tidak ditemukan', 'Prestasi yang Anda cari tidak tersedia, belum dipublikasikan, atau sudah dipindahkan.');
            return;
        }

        if ($this->viewRenderer instanceof ViewRenderer) {
            $html = $this->viewRenderer->renderWithLayout('public/prestasi/show.php', 'layouts/public.php', [
                'item' => $item,
                'seo' => $seo,
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-prestasi-detail',
                'scripts' => '<script defer src="/assets/js/dist/pages/prestasi.js"></script>',
            ]);
            $response->html($html, is_array($item) ? 200 : 404);
            return;
        }

        $response->html($this->renderer->render('prestasi.html', ['meta' => $meta]));
    }

    public function submissionForm(Request $request, Response $response, array $params): void
    {
        $token = $params['token'] ?? '';

        if ($request->acceptsJson()) {
            $valid = $this->tokenModel?->validateToken($token);
            if (!$valid) {
                $response->json(['error' => 'Token tidak valid atau sudah digunakan'], 403);
                return;
            }
            $response->json(['data' => ['valid' => true, 'label' => $valid['label']]]);
            return;
        }

        $response->html($this->renderer->render('prestasi-submit.html', ['noindex' => true]));
    }

    public function submitWithToken(Request $request, Response $response, array $params): void
    {
        $token = $params['token'] ?? '';

        // Validate body first (no DB needed)
        $body = $request->json();
        $errors = $this->validateSubmission($body);
        if (!empty($errors)) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        // Use transaction + FOR UPDATE to prevent TOCTOU race condition
        $db = $this->tokenModel?->getDb();
        if (!$db) {
            $response->json(['error' => 'Service unavailable'], 503);
            return;
        }

        $db->beginTransaction();
        try {
            $valid = $this->tokenModel->validateTokenForUpdate($token);

            if (!$valid) {
                $db->rollBack();
                $response->json(['error' => 'Token tidak valid atau sudah digunakan'], 403);
                return;
            }

            // Mark token used immediately within the same transaction
            $this->tokenModel->markUsed($valid['id']);

            $slug = $this->generateUniqueSlug($body['title'] ?? 'prestasi');

            $id = $this->prestasi?->create([
                'title' => strip_tags(mb_substr(trim($body['title'] ?? ''), 0, 255)),
                'slug' => $slug,
                'name' => strip_tags(mb_substr(trim($body['name'] ?? ''), 0, 255)),
                'category' => strip_tags(mb_substr(trim($body['category'] ?? ''), 0, 100)),
                'year' => strip_tags(mb_substr(trim($body['year'] ?? ''), 0, 4)),
                'description' => strip_tags(mb_substr(trim($body['description'] ?? ''), 0, 5000)),
                'content' => strip_tags(mb_substr(trim($body['content'] ?? ''), 0, 50000)),
                'institution' => strip_tags(mb_substr(trim($body['institution'] ?? ''), 0, 255)),
                'status' => 'pending',
            ]);

            if ($id) {
                $db->commit();
                $response->json(['data' => ['id' => $id, 'status' => 'pending']], 201);
            } else {
                $db->rollBack();
                $response->json(['error' => 'Gagal menyimpan data'], 500);
            }
        } catch (\Throwable $e) {
            $db->rollBack();
            $response->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }

    private function validateSubmission(array $body): array
    {
        $errors = [];
        if (empty(trim($body['title'] ?? ''))) {
            $errors[] = 'Judul prestasi wajib diisi';
        }
        if (mb_strlen(trim($body['title'] ?? '')) > 255) {
            $errors[] = 'Judul prestasi maksimal 255 karakter';
        }
        if (empty(trim($body['name'] ?? ''))) {
            $errors[] = 'Nama anggota wajib diisi';
        }
        if (mb_strlen(trim($body['name'] ?? '')) > 255) {
            $errors[] = 'Nama anggota maksimal 255 karakter';
        }
        if (empty(trim($body['campus'] ?? ''))) {
            $errors[] = 'Komisariat wajib diisi';
        }
        if (empty(trim($body['category'] ?? ''))) {
            $errors[] = 'Kategori wajib diisi';
        }
        $year = trim($body['year'] ?? '');
        if ($year === '') {
            $errors[] = 'Tahun wajib diisi';
        } elseif (!preg_match('/^(19|20)\d{2}$/', $year)) {
            $errors[] = 'Tahun harus berformat tahun yang valid (1900-2099)';
        }
        if (mb_strlen(trim($body['content'] ?? '')) > 50000) {
            $errors[] = 'Konten terlalu panjang (maks 50.000 karakter)';
        }
        if (mb_strlen(trim($body['description'] ?? '')) > 5000) {
            $errors[] = 'Deskripsi terlalu panjang (maks 5.000 karakter)';
        }
        return $errors;
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
        $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';
        return trim($slug, '-') ?: 'prestasi';
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = $this->generateSlug($title);
        $slug = $base;
        $suffix = 1;

        while ($this->prestasi?->findBySlug($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
            if ($suffix > 100) {
                $slug = $base . '-' . bin2hex(random_bytes(4));
                break;
            }
        }

        return $slug;
    }

    /** @param array<string, mixed> $item @return array<string, mixed> */
    private function sanitizePublicItem(array $item): array
    {
        $item['title'] = strip_tags((string) ($item['title'] ?? ''));
        $item['name'] = strip_tags((string) ($item['name'] ?? ''));
        $item['member_name'] = $item['name'];
        $item['campus'] = strip_tags((string) ($item['campus'] ?? ''));
        $item['category'] = strip_tags((string) ($item['category'] ?? ''));
        $item['description'] = strip_tags((string) ($item['description'] ?? ''));
        $item['institution'] = strip_tags((string) ($item['institution'] ?? ''));
        $item['content'] = HtmlSanitizer::sanitize((string) ($item['content'] ?? ''));
        $item['detail'] = $item['content'];

        return $item;
    }
}
