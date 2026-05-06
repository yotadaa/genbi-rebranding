<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Prestasi;

class PrestasiController
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'pending', 'archived'];
    private const UPLOAD_DIR = '/uploads/prestasi/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private ?Prestasi $prestasi = null) {}

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) ($request->query('page') ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        $items = $this->prestasi?->all($limit, $offset) ?? [];
        $response->json(['data' => $items, 'page' => $page]);
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

        $slug = $this->generateUniqueSlug($body['title'] ?? 'prestasi');
        $status = $this->sanitizeStatus($body['status'] ?? 'draft');

        $id = $this->prestasi?->create([
            'title' => strip_tags(mb_substr(trim($body['title'] ?? ''), 0, 255)),
            'slug' => $slug,
            'name' => strip_tags(mb_substr(trim($body['name'] ?? ''), 0, 255)),
            'campus' => strip_tags(mb_substr(trim($body['campus'] ?? ''), 0, 255)),
            'category' => strip_tags(mb_substr(trim($body['category'] ?? ''), 0, 100)),
            'year' => strip_tags(mb_substr(trim($body['year'] ?? ''), 0, 10)),
            'description' => strip_tags(mb_substr(trim($body['description'] ?? ''), 0, 5000)),
            'content' => mb_substr(trim($body['content'] ?? ''), 0, 50000),
            'image' => strip_tags(mb_substr(trim($body['image'] ?? ''), 0, 500)),
            'institution' => strip_tags(mb_substr(trim($body['institution'] ?? ''), 0, 255)),
            'status' => $status,
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
        if (empty(trim($body['title'] ?? ''))) {
            $errors[] = 'Judul prestasi wajib diisi';
        }
        if (empty(trim($body['name'] ?? ''))) {
            $errors[] = 'Nama anggota wajib diisi';
        }
        if (empty(trim($body['campus'] ?? ''))) {
            $errors[] = 'Komisariat wajib diisi';
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
        $fieldLimits = [
            'title' => 255,
            'name' => 255,
            'campus' => 255,
            'category' => 100,
            'year' => 10,
            'description' => 5000,
            'content' => 50000,
            'image' => 500,
            'institution' => 255,
            'slug' => 255,
        ];

        $sanitized = [];
        foreach ($fieldLimits as $field => $limit) {
            if (!isset($body[$field])) {
                continue;
            }
            $value = mb_substr(trim((string) $body[$field]), 0, $limit);
            // Strip HTML from all fields except content (which may contain editor HTML)
            if ($field !== 'content') {
                $value = strip_tags($value);
            }
            $sanitized[$field] = $value;
        }

        // Validate status if provided
        if (isset($body['status'])) {
            $sanitized['status'] = $this->sanitizeStatus((string) $body['status']);
        }

        // Validate slug format if provided
        if (isset($sanitized['slug'])) {
            $sanitized['slug'] = $this->generateSlug($sanitized['slug']);
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
        while ($this->prestasi?->findBySlug($slug)) {
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
