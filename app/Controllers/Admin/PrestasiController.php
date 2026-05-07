<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Models\Prestasi;

class PrestasiController
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'archived'];
    private const UPLOAD_DIR = '/uploads/prestasi/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private ?Prestasi $prestasi = null) {}

    public function index(Request $request, Response $response): void
    {
        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 25, 100);
        $status = $request->query('status');
        $items = $this->prestasi?->all($pg['per_page'], $pg['offset']) ?? [];
        $total = $this->prestasi?->countAll($status) ?? count($items);
        $response->json([
            'data' => $items,
            'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->prestasi?->findById($id);

        if (!$item) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }

        $response->json(['data' => $item]);
    }

    public function store(Request $request, Response $response): void
    {
        $body = $request->json();
        $errors = $this->validate($body);

        if (!empty($errors)) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $sanitized = $this->sanitizePayload($body);
        $slug = $this->generateUniqueSlug($sanitized['title'] ?: 'prestasi');
        $status = $this->sanitizeStatus($body['status'] ?? 'draft');

        $id = $this->prestasi?->create([
            'title' => $sanitized['title'],
            'slug' => $slug,
            'name' => $sanitized['name'],
            'category' => $sanitized['category'],
            'year' => $sanitized['year'],
            'description' => $sanitized['description'],
            'content' => $sanitized['content'],
            'image' => $sanitized['image'],
            'institution' => $sanitized['institution'],
            'status' => $status,
            'meta_title' => $sanitized['meta_title'],
            'meta_keyword' => $sanitized['meta_keyword'],
            'meta_description' => $sanitized['meta_description'],
        ]);

        if ($id) {
            $response->json(['data' => ['id' => $id]], 201);
        } else {
            $response->json(['error' => 'Gagal menyimpan'], 500);
        }
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $body = $request->json();

        // Validate and sanitize update payload
        $sanitized = $this->sanitizeUpdateBody($body);
        if (empty($sanitized)) {
            $response->json(['error' => 'Tidak ada data valid untuk diperbarui'], 422);
            return;
        }

        $success = $this->prestasi?->update($id, $sanitized);

        if ($success) {
            $response->json(['data' => ['id' => $id, 'updated' => true]]);
        } else {
            $response->json(['error' => 'Gagal memperbarui atau data tidak ditemukan'], 404);
        }
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->prestasi?->softDelete($id);

        if ($success) {
            $response->json(['data' => ['id' => $id, 'deleted' => true]]);
        } else {
            $response->json(['error' => 'Gagal menghapus atau data tidak ditemukan'], 404);
        }
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'Upload gagal: error code ' . $file['error']], 422);
            return;
        }

        if ($file['size'] > self::MAX_UPLOAD_SIZE) {
            $response->json(['error' => 'Ukuran file melebihi batas 5MB'], 422);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            $response->json(['error' => 'Tipe file tidak diizinkan. Hanya JPEG, PNG, WebP, dan GIF.'], 422);
            return;
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $response->json(['error' => 'File bukan gambar yang valid'], 422);
            return;
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $filename = 'prestasi-' . bin2hex(random_bytes(8)) . '.' . $ext;

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
            $response->json(['error' => 'Gagal menyimpan file'], 500);
            return;
        }

        $url = self::UPLOAD_DIR . $filename;
        $response->json(['data' => ['url' => $url, 'filename' => $filename]], 201);
    }

    private function validate(array $body): array
    {
        $errors = [];
        if (empty(trim($body['name'] ?? ''))) {
            $errors[] = 'Nama wajib dipilih';
        }
        if (empty(trim($body['institution'] ?? ''))) {
            $errors[] = 'Penyelenggara wajib diisi';
        }
        if (empty(trim($body['category'] ?? ''))) {
            $errors[] = 'Peringkat wajib diisi';
        }
        $year = trim((string) ($body['year'] ?? ''));
        if ($year === '' || !preg_match('/^(19|20)\d{2}$/', $year)) {
            $errors[] = 'Tahun harus berformat tahun yang valid';
        }
        if (mb_strlen(trim((string) ($body['description'] ?? ''))) > 5000) {
            $errors[] = 'Deskripsi maksimal 5.000 karakter';
        }
        return $errors;
    }

    private function sanitizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        return in_array($status, self::ALLOWED_STATUSES, true) ? $status : 'draft';
    }

    /** @return array<string, string> */
    private function sanitizeUpdateBody(array $body): array
    {
        $sanitized = $this->sanitizePayload($body, false);

        if (isset($body['status'])) {
            $sanitized['status'] = $this->sanitizeStatus((string) $body['status']);
        }

        if (isset($body['slug'])) {
            $sanitized['slug'] = $this->generateSlug((string) $body['slug']);
        }

        return array_filter($sanitized, static fn($value) => $value !== null);
    }

    /** @return array<string, string|null> */
    private function sanitizePayload(array $body, bool $includeDefaults = true): array
    {
        $fieldLimits = [
            'title' => 255,
            'name' => 255,
            'category' => 100,
            'year' => 4,
            'description' => 5000,
            'content' => 50000,
            'image' => 1000,
            'institution' => 255,
            'meta_title' => 255,
            'meta_keyword' => 1000,
            'meta_description' => 1000,
        ];

        $sanitized = [];
        foreach ($fieldLimits as $field => $limit) {
            if (!isset($body[$field])) {
                if ($includeDefaults) {
                    $sanitized[$field] = $field === 'category' ? 'Prestasi' : '';
                }
                continue;
            }
            $value = mb_substr(trim((string) $body[$field]), 0, $limit);
            if ($field !== 'content') {
                $value = strip_tags($value);
            }
            $sanitized[$field] = $value;
        }

        return $sanitized;
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

        // Check for existing slugs and append suffix if needed
        while ($this->prestasi?->findAnyBySlug($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
            if ($suffix > 100) {
                // Safety valve: append random suffix
                $slug = $base . '-' . bin2hex(random_bytes(4));
                break;
            }
        }

        return $slug;
    }
}
