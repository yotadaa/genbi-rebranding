<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Services\SeoService;

class PrestasiController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Prestasi $prestasi = null,
        private ?PrestasiToken $tokenModel = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        if ($request->acceptsJson()) {
            $page = max(1, (int) ($request->query('page') ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $items = $this->prestasi?->published($limit, $offset) ?? [];
            $response->json(['data' => $items, 'page' => $page]);
            return;
        }

        $seo = SeoService::forPage('prestasi.html');
        $meta = SeoService::renderMetaBlock($seo);
        $response->html($this->renderer->render('prestasi.html', ['meta' => $meta]));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';

        if ($request->acceptsJson()) {
            $item = $this->prestasi?->findBySlug($slug);
            if (!$item) {
                $response->json(['error' => 'Not found'], 404);
                return;
            }
            $response->json(['data' => $item]);
            return;
        }

        $item = $this->prestasi?->findBySlug($slug);
        if (is_array($item)) {
            $seo = SeoService::forPrestasi($item);
        } else {
            $seo = SeoService::forPage('prestasi.html');
        }
        $meta = SeoService::renderMetaBlock($seo);
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
                'campus' => strip_tags(mb_substr(trim($body['campus'] ?? ''), 0, 255)),
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
}
