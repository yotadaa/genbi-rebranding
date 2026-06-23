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
use App\Services\CsrfService;
use App\Services\SeoService;
use App\Services\StructuredData;

class PrestasiController
{
    private const UPLOAD_DIR = '/uploads/prestasi/';
    private const MAX_UPLOAD_SIZE = 5_242_880;
    private const MAX_UPLOAD_FILES = 6;
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

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
                'scripts' => '<script defer src="/assets/js/dist/pages/prestasi-detail.js?v=20260519k"></script>',
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
                $response->json(['error' => 'Token tidak valid, kedaluwarsa, atau sudah dicabut'], 403);
                return;
            }
            $response->json(['data' => ['valid' => true, 'label' => $valid['label']]]);
            return;
        }

        $response->html($this->renderer->render('prestasi-submit.html', [
            'noindex' => true,
            'csrf_token' => CsrfService::token(),
        ]));
    }

    public function submitWithToken(Request $request, Response $response, array $params): void
    {
        $token = $params['token'] ?? '';

        $body = $this->submissionInput($request);
        $errors = $this->validateSubmission($body);
        $uploadedImages = [];

        if (empty($errors)) {
            $upload = $this->uploadSubmissionImages($_FILES['photos'] ?? null);
            $uploadedImages = $upload['files'];
            $errors = array_merge($errors, $upload['errors']);
        }

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
                $response->json(['error' => 'Token tidak valid, kedaluwarsa, atau sudah dicabut'], 403);
                return;
            }

            $sanitizedBody = $this->sanitizeSubmissionBody($body);
            $slug = $this->generateUniqueSlug($sanitizedBody['title'] !== '' ? $sanitizedBody['title'] : 'prestasi');
            $imageUrl = $sanitizedBody['image_url'];
            $primaryImage = $uploadedImages[0]['url'] ?? $imageUrl;
            $seo = $this->buildSubmissionSeo($sanitizedBody);

            $id = $this->prestasi?->create([
                'title' => $sanitizedBody['title'],
                'slug' => $slug,
                'name' => $sanitizedBody['name'],
                'category' => $sanitizedBody['category'],
                'year' => $sanitizedBody['year'],
                'description' => $sanitizedBody['description'],
                'content' => $sanitizedBody['content'],
                'institution' => $sanitizedBody['institution'],
                'image' => $primaryImage,
                // tbl_prestasi currently only allows draft/published/archived. Token
                // submissions still return "pending" to the client, but are stored as
                // draft for admin review until the schema grows a dedicated pending state.
                'status' => 'draft',
                'meta_title' => $seo['meta_title'],
                'meta_keyword' => $seo['meta_keyword'],
                'meta_description' => $seo['meta_description'],
            ]);

            if ($id) {
                $this->storeSubmissionLog($db, $valid['id'], $id, $sanitizedBody, $uploadedImages, $request);
                $db->commit();
                $response->json(['data' => ['id' => $id, 'status' => 'pending']], 201);
            } else {
                $this->deleteUploadedImages($uploadedImages);
                $db->rollBack();
                $requestId = $this->logSubmissionFailure('prestasi_create_returned_empty_id', null, $token, $sanitizedBody, $uploadedImages);
                $response->json($this->submissionFailurePayload('prestasi_create_returned_empty_id', $requestId), 500);
            }
        } catch (\Throwable $e) {
            $this->deleteUploadedImages($uploadedImages);
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $requestId = $this->logSubmissionFailure('submission_transaction_failed', $e, $token, $body, $uploadedImages);
            $response->json($this->submissionFailurePayload('submission_transaction_failed', $requestId), 500);
        }
    }

    /** @param array<string, mixed> $body @param array<int, array{url: string, filename: string, mime: string, size: int}> $uploadedImages */
    private function logSubmissionFailure(string $stage, ?\Throwable $error, string $token, array $body, array $uploadedImages): string
    {
        $requestId = 'prestasi_submit_' . bin2hex(random_bytes(8));

        ErrorHandler::log($error ?? 'Prestasi token submission failed', [
            'request_id' => $requestId,
            'stage' => $stage,
            'token_prefix' => substr(hash('sha256', trim($token)), 0, 12),
            'title_length' => mb_strlen($this->sanitizeSubmissionText((string) ($body['title'] ?? ''), 255)),
            'category' => $this->sanitizeSubmissionText((string) ($body['category'] ?? ''), 80),
            'year' => $this->sanitizeSubmissionText((string) ($body['year'] ?? ''), 4),
            'campus_length' => mb_strlen($this->sanitizeSubmissionText((string) ($body['campus'] ?? ''), 255)),
            'upload_count' => count($uploadedImages),
        ]);

        return $requestId;
    }

    /** @return array<string, string> */
    private function submissionFailurePayload(string $stage, string $requestId): array
    {
        return [
            'error' => 'Gagal menyimpan data',
            'code' => $stage,
            'request_id' => $requestId,
            'detail' => 'Detail error sudah dicatat di log server.',
        ];
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

    /** @return array<string, string> */
    private function submissionInput(Request $request): array
    {
        $requestBody = !empty($_POST) ? $_POST : $request->json();

        return [
            'title' => $this->submissionInputString($requestBody, 'title'),
            'category' => $this->submissionInputString($requestBody, 'category'),
            'year' => $this->submissionInputString($requestBody, 'year'),
            'campus' => $this->submissionInputString($requestBody, 'campus'),
            'name' => $this->submissionInputString($requestBody, 'name'),
            'institution' => $this->submissionInputString($requestBody, 'institution'),
            'description' => $this->submissionInputString($requestBody, 'description'),
            'content' => $this->submissionInputString($requestBody, 'content'),
            'image_url' => $this->submissionInputString($requestBody, 'image_url'),
        ];
    }

    private function submissionInputString(array $body, string $key): string
    {
        $value = $body[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<string, string> $body @return array<string, string> */
    private function sanitizeSubmissionBody(array $body): array
    {
        return [
            'title' => $this->sanitizeSubmissionText($body['title'] ?? '', 255),
            'category' => $this->sanitizeSubmissionText($body['category'] ?? '', 100),
            'year' => $this->sanitizeSubmissionText($body['year'] ?? '', 4),
            'campus' => $this->sanitizeSubmissionText($body['campus'] ?? '', 255),
            'name' => $this->sanitizeSubmissionText($body['name'] ?? '', 255),
            'institution' => $this->sanitizeSubmissionText($body['institution'] ?? '', 255),
            'description' => $this->sanitizeSubmissionText($body['description'] ?? '', 5000),
            'content' => $this->sanitizeSubmissionText($body['content'] ?? '', 50000),
            'image_url' => HtmlSanitizer::sanitizeUrl($body['image_url'] ?? ''),
        ];
    }

    private function sanitizeSubmissionText(string $value, int $maxLength): string
    {
        $value = trim(strip_tags($value));
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

        return mb_substr(trim($value), 0, $maxLength);
    }

    /** @return array{meta_title: string, meta_keyword: string, meta_description: string} */
    private function buildSubmissionSeo(array $body): array
    {
        $title = strip_tags(mb_substr(trim((string) ($body['title'] ?? 'Prestasi GenBI Jambi')), 0, 180));
        $category = strip_tags(mb_substr(trim((string) ($body['category'] ?? 'Prestasi')), 0, 100));
        $name = strip_tags(mb_substr(trim((string) ($body['name'] ?? '')), 0, 120));
        $institution = strip_tags(mb_substr(trim((string) ($body['institution'] ?? '')), 0, 120));
        $year = strip_tags(mb_substr(trim((string) ($body['year'] ?? date('Y'))), 0, 4));
        $description = strip_tags(mb_substr(trim((string) ($body['description'] ?? '')), 0, 220));

        $summary = $description !== ''
            ? $description
            : trim($category . ($name !== '' ? ' ' . $name : '') . ($institution !== '' ? ' oleh ' . $institution : '') . ' tahun ' . $year . '. Dokumentasi prestasi GenBI Jambi.');

        return [
            'meta_title' => mb_substr($title . ' | GenBI Jambi', 0, 255),
            'meta_keyword' => mb_substr(implode(', ', array_filter([$category, 'prestasi GenBI Jambi', $name, $institution, $year])), 0, 1000),
            'meta_description' => mb_substr($summary, 0, 1000),
        ];
    }

    /** @return array{files: array<int, array{url: string, filename: string, mime: string, size: int}>, errors: string[]} */
    private function uploadSubmissionImages(mixed $files): array
    {
        if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
            return ['files' => [], 'errors' => []];
        }

        $normalized = [];
        $count = count($files['name']);
        if ($count > self::MAX_UPLOAD_FILES) {
            return ['files' => [], 'errors' => ['Maksimal ' . self::MAX_UPLOAD_FILES . ' foto dapat diunggah.']];
        }

        for ($i = 0; $i < $count; $i++) {
            $error = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $normalized[] = [
                'name' => (string) ($files['name'][$i] ?? ''),
                'type' => (string) ($files['type'][$i] ?? ''),
                'tmp_name' => (string) ($files['tmp_name'][$i] ?? ''),
                'error' => $error,
                'size' => (int) ($files['size'][$i] ?? 0),
            ];
        }

        $stored = [];
        $errors = [];
        foreach ($normalized as $index => $file) {
            $validated = $this->storeSingleSubmissionImage($file, $index + 1);
            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                continue;
            }

            $stored[] = $validated;
        }

        if (!empty($errors)) {
            foreach ($stored as $file) {
                $path = dirname(__DIR__, 3) . '/public' . $file['url'];
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            return ['files' => [], 'errors' => $errors];
        }

        return ['files' => $stored, 'errors' => []];
    }

    /** @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
     *  @return array{url: string, filename: string, mime: string, size: int}|array{error: string}
     */
    private function storeSingleSubmissionImage(array $file, int $position): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload foto #' . $position . ' gagal.'];
        }

        if ($file['size'] <= 0 || $file['size'] > self::MAX_UPLOAD_SIZE) {
            return ['error' => 'Ukuran foto #' . $position . ' melebihi batas 5MB.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            return ['error' => 'Tipe file foto #' . $position . ' tidak diizinkan. Gunakan JPEG, PNG, WebP, atau GIF.'];
        }

        if (@getimagesize($file['tmp_name']) === false) {
            return ['error' => 'File foto #' . $position . ' bukan gambar yang valid.'];
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $filename = 'prestasi-submit-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $htaccess = $uploadDir . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        }

        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['error' => 'Gagal menyimpan foto #' . $position . '.'];
        }

        return [
            'url' => self::UPLOAD_DIR . $filename,
            'filename' => $filename,
            'mime' => $mimeType,
            'size' => $file['size'],
        ];
    }

    /** @param array<int, array{url: string, filename: string, mime: string, size: int}> $uploadedImages */
    private function storeSubmissionLog(\PDO $db, int $tokenId, int $prestasiId, array $body, array $uploadedImages, Request $request): void
    {
        $stmt = $db->prepare(
            'INSERT INTO tbl_prestasi_submission (token_id, prestasi_id, submitter_name, submitter_email, payload_json, ip_address, user_agent, created_at) VALUES (:token_id, :prestasi_id, :submitter_name, :submitter_email, :payload_json, :ip_address, :user_agent, NOW())'
        );

        $payload = [
            'title' => trim((string) ($body['title'] ?? '')),
            'category' => trim((string) ($body['category'] ?? '')),
            'year' => trim((string) ($body['year'] ?? '')),
            'campus' => trim((string) ($body['campus'] ?? '')),
            'name' => trim((string) ($body['name'] ?? '')),
            'institution' => trim((string) ($body['institution'] ?? '')),
            'description' => trim((string) ($body['description'] ?? '')),
            'content' => trim((string) ($body['content'] ?? '')),
            'photos' => array_map(static fn(array $file): array => [
                'url' => $file['url'],
                'filename' => $file['filename'],
                'mime' => $file['mime'],
                'size' => $file['size'],
            ], $uploadedImages),
        ];

        $stmt->execute([
            ':token_id' => $tokenId,
            ':prestasi_id' => $prestasiId,
            ':submitter_name' => trim((string) ($body['name'] ?? '')),
            ':submitter_email' => 'token-submission@genbijambi.local',
            ':payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':ip_address' => $request->ip(),
            ':user_agent' => $request->userAgent(),
        ]);
    }

    /** @param array<int, array{url: string, filename: string, mime: string, size: int}> $uploadedImages */
    private function deleteUploadedImages(array $uploadedImages): void
    {
        foreach ($uploadedImages as $file) {
            $path = dirname(__DIR__, 3) . '/public' . $file['url'];
            if (is_file($path)) {
                @unlink($path);
            }
        }
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
