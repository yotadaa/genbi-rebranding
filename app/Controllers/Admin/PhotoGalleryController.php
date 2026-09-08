<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\PhotoGallery;
use App\Services\HtmlSanitizer;

class PhotoGalleryController
{
    private const UPLOAD_DIR = '/uploads/gallery/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private ?PhotoGallery $photos = null) {}

    public function index(Request $request, Response $response): void
    {
        $response->json(['data' => $this->photos?->all(100, 0) ?? []]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $item = $this->photos?->find((int) ($params['id'] ?? 0));
        if (!$item) {
            $response->json(['error' => 'Photo not found'], 404);
            return;
        }
        $response->json(['data' => $item]);
    }

    public function store(Request $request, Response $response): void
    {
        $payload = $this->sanitize($request->json());
        if ($payload['title'] === '' || $payload['image'] === '') {
            $response->json(['error' => 'Judul dan gambar wajib diisi'], 422);
            return;
        }
        $id = $this->photos?->create($payload) ?? 0;
        $response->json($id ? ['data' => ['id' => $id]] : ['error' => 'Gagal menyimpan foto'], $id ? 201 : 500);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $payload = $this->sanitize($request->json(), false);
        if ($payload === []) {
            $response->json(['error' => 'Tidak ada data valid'], 422);
            return;
        }
        $ok = $this->photos?->update($id, $payload) ?? false;
        $response->json($ok ? ['data' => ['id' => $id, 'updated' => true]] : ['error' => 'Gagal memperbarui foto'], $ok ? 200 : 404);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $ok = $this->photos?->delete($id) ?? false;
        $response->json($ok ? ['data' => ['id' => $id, 'deleted' => true]] : ['error' => 'Gagal menghapus foto'], $ok ? 200 : 404);
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0 || $file['size'] > self::MAX_UPLOAD_SIZE) {
            $response->json(['error' => 'Upload tidak valid atau melebihi 5MB'], 422);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_IMAGE_TYPES, true) || @getimagesize($file['tmp_name']) === false) {
            $response->json(['error' => 'File gambar tidak valid'], 422);
            return;
        }
        $ext = match ($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', default => 'jpg' };
        $dir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $htaccess = $dir . '.htaccess';
        if (!is_file($htaccess)) file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        $filename = 'gallery-' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $response->json(['error' => 'Gagal menyimpan file'], 500);
            return;
        }
        $response->json(['data' => ['url' => self::UPLOAD_DIR . $filename, 'filename' => $filename]], 201);
    }

    /** @return array<string, string|int> */
    private function sanitize(array $body, bool $defaults = true): array
    {
        $allowedStatus = ['show', 'hide'];
        $payload = [];
        foreach (['title' => 255, 'caption' => 1000, 'image' => 1000] as $field => $limit) {
            if (!array_key_exists($field, $body)) {
                if ($defaults) $payload[$field] = '';
                continue;
            }
            $value = mb_substr(trim((string) $body[$field]), 0, $limit);
            $payload[$field] = $field === 'image' ? HtmlSanitizer::sanitizeUrl($value) : strip_tags($value);
        }
        if (array_key_exists('status', $body) || $defaults) {
            $status = strtolower(trim((string) ($body['status'] ?? 'show')));
            $payload['status'] = in_array($status, $allowedStatus, true) ? $status : 'show';
        }
        if (array_key_exists('sort_order', $body) || $defaults) {
            $payload['sort_order'] = max(0, (int) ($body['sort_order'] ?? 0));
        }
        return array_filter($payload, static fn($value) => $value !== null);
    }
}
