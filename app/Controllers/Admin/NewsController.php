<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\News;
use App\Services\HtmlSanitizer;

final class NewsController
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'archived'];
    private const MAX_TITLE_LENGTH = 255;
    private const MAX_EXCERPT_LENGTH = 1000;
    private const MAX_CONTENT_LENGTH = 100000;
    private const MAX_META_LENGTH = 500;
    private const UPLOAD_DIR = '/uploads/news/';
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5MB

    public function __construct(private ?News $news = null) {}

    public function index(Request $request, Response $response): void
    {
        if (!$this->news) {
            $response->json(['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'per_page' => 50, 'total_pages' => 1]]);
            return;
        }

        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = max(1, min(100, (int) ($request->query('per_page') ?? 50)));
        $offset = ($page - 1) * $perPage;

        // Build filters array
        $filters = [];
        if ($request->query('status')) {
            $filters['status'] = $request->query('status');
        }
        if ($request->query('q')) {
            $filters['q'] = $request->query('q');
        }
        
        // Parse category[] query params
        $categoryParams = $_GET['category'] ?? [];
        if (!is_array($categoryParams)) {
            $categoryParams = [$categoryParams];
        }
        $categoryIds = array_filter(array_map('intval', $categoryParams));
        if (!empty($categoryIds)) {
            $filters['categories'] = $categoryIds;
        }

        $items = $this->news->allForAdmin($perPage, $offset, $filters);
        $total = $this->news->countForAdmin($filters);
        $totalPages = (int) ceil($total / $perPage);

        $response->json([
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
            // Keep legacy fields for backward compatibility
            'total' => $total,
            'page' => $page,
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->news?->findById($id);

        if (!$item) {
            $response->json(['error' => 'Berita tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => $item]);
    }

    public function categories(Request $request, Response $response): void
    {
        $cats = $this->news?->categories() ?? [];
        $response->json(['data' => $cats]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->news) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $errors = $this->validate($body);

        if (!empty($errors)) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $sanitized = $this->sanitize($body);
        $sanitized['slug'] = $this->news->generateUniqueSlug($sanitized['news_title'] ?? $sanitized['title'] ?? 'berita');

        $id = $this->news->create($sanitized);

        if ($id) {
            $response->json(['data' => ['id' => $id, 'slug' => $sanitized['slug']]], 201);
        } else {
            $response->json(['error' => 'Gagal menyimpan berita'], 500);
        }
    }

    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->news) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $body = $request->json();

        // Validate at least some data is provided
        if (empty($body)) {
            $response->json(['error' => 'Tidak ada data untuk diperbarui'], 422);
            return;
        }

        $sanitized = $this->sanitize($body);

        // Generate unique slug if title changed
        if (isset($sanitized['news_title']) || isset($sanitized['title'])) {
            $title = $sanitized['news_title'] ?? $sanitized['title'] ?? '';
            if ($title !== '') {
                $sanitized['slug'] = $this->news->generateUniqueSlug($title, $id);
            }
        }

        $success = $this->news->updateNews($id, $sanitized);

        if ($success) {
            $response->json(['data' => ['id' => $id, 'updated' => true]]);
        } else {
            $response->json(['error' => 'Gagal memperbarui atau berita tidak ditemukan'], 404);
        }
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->news?->softDelete($id);

        if ($success) {
            $response->json(['data' => ['id' => $id, 'deleted' => true]]);
        } else {
            $response->json(['error' => 'Gagal menghapus atau berita tidak ditemukan'], 404);
        }
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }

        $file = $_FILES['image'];

        // Validate upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'Upload gagal: error code ' . $file['error']], 422);
            return;
        }

        // Validate file size
        if ($file['size'] > self::MAX_UPLOAD_SIZE) {
            $response->json(['error' => 'Ukuran file melebihi batas 5MB'], 422);
            return;
        }

        // Validate MIME type using finfo (not trusting browser MIME)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            $response->json(['error' => 'Tipe file tidak diizinkan. Hanya JPEG, PNG, WebP, dan GIF.'], 422);
            return;
        }

        // Validate it's actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $response->json(['error' => 'File bukan gambar yang valid'], 422);
            return;
        }

        // Generate safe filename
        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $filename = 'news-' . bin2hex(random_bytes(8)) . '.' . $ext;

        // Ensure upload directory exists
        $uploadDir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Disable PHP execution in upload directory
        $htaccess = $uploadDir . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        }

        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $response->json(['error' => 'Gagal menyimpan file'], 500);
            return;
        }

        $url = self::UPLOAD_DIR . $filename;
        $response->json(['data' => ['url' => $url, 'filename' => $filename]], 201);
    }

    private function validate(array $body): array
    {
        $errors = [];
        $title = trim((string) ($body['title'] ?? $body['news_title'] ?? ''));
        if ($title === '') {
            $errors[] = 'Judul berita wajib diisi';
        }
        if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
            $errors[] = 'Judul berita maksimal ' . self::MAX_TITLE_LENGTH . ' karakter';
        }
        return $errors;
    }

    /** @return array<string, mixed> */
    private function sanitize(array $body): array
    {
        $sanitized = [];

        // Title
        if (isset($body['title']) || isset($body['news_title'])) {
            $sanitized['news_title'] = strip_tags(mb_substr(trim((string) ($body['title'] ?? $body['news_title'] ?? '')), 0, self::MAX_TITLE_LENGTH));
        }

        // Content (allow HTML from editor)
        if (isset($body['content']) || isset($body['news_content'])) {
            $sanitized['news_content'] = $this->sanitizeEditorHtml((string) ($body['content'] ?? $body['news_content'] ?? ''));
        }

        // Excerpt
        if (isset($body['excerpt']) || isset($body['news_content_short'])) {
            $sanitized['news_content_short'] = strip_tags(mb_substr(trim((string) ($body['excerpt'] ?? $body['news_content_short'] ?? '')), 0, self::MAX_EXCERPT_LENGTH));
        }

        // Date
        if (isset($body['date']) || isset($body['news_date'])) {
            $date = trim((string) ($body['date'] ?? $body['news_date'] ?? ''));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $sanitized['news_date'] = $date;
            }
        }

        // Category
        if (isset($body['category_id'])) {
            $sanitized['category_id'] = (int) $body['category_id'];
        }

        // Featured photo only (filenames only, no paths)
        if (isset($body['photo'])) {
            $sanitized['photo'] = strip_tags(mb_substr(trim((string) $body['photo']), 0, 500));
        }

        // Comment toggle
        if (isset($body['comment'])) {
            $sanitized['comment'] = in_array($body['comment'], ['On', 'Off'], true) ? $body['comment'] : 'On';
        }

        // SEO fields
        if (isset($body['meta_title'])) {
            $sanitized['meta_title'] = strip_tags(mb_substr(trim((string) $body['meta_title']), 0, self::MAX_META_LENGTH));
        }
        if (isset($body['meta_keyword'])) {
            $sanitized['meta_keyword'] = strip_tags(mb_substr(trim((string) $body['meta_keyword']), 0, self::MAX_META_LENGTH));
        }
        if (isset($body['meta_description'])) {
            $sanitized['meta_description'] = strip_tags(mb_substr(trim((string) $body['meta_description']), 0, self::MAX_META_LENGTH));
        }

        // Status
        if (isset($body['status'])) {
            $status = strtolower(trim((string) $body['status']));
            $sanitized['status'] = in_array($status, self::ALLOWED_STATUSES, true) ? $status : 'draft';
        }

        // Contributors
        if (isset($body['contributor_pewarta'])) {
            $sanitized['contributor_pewarta'] = strip_tags(mb_substr(trim((string) $body['contributor_pewarta']), 0, 120));
        }
        if (isset($body['contributor_editor'])) {
            $sanitized['contributor_editor'] = strip_tags(mb_substr(trim((string) $body['contributor_editor']), 0, 120));
        }
        if (isset($body['contributor_redaksi'])) {
            $sanitized['contributor_redaksi'] = strip_tags(mb_substr(trim((string) $body['contributor_redaksi']), 0, 120));
        }

        return $sanitized;
    }

    private function sanitizeEditorHtml(string $html): string
    {
        return HtmlSanitizer::sanitize(mb_substr(trim($html), 0, self::MAX_CONTENT_LENGTH));
    }
}
