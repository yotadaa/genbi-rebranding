<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Feature;

final class FeatureController
{
    private const UPLOAD_DIR = '/uploads/features/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const ICON_KEYS = ['sparkles', 'users', 'bank', 'chart', 'academic', 'calendar', 'heart', 'news', 'grid'];

    public function __construct(private ?Feature $feature = null)
    {
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->feature) {
            $response->json(['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 1]]);
            return;
        }

        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = max(1, min(100, (int) ($request->query('per_page') ?? 25)));
        $offset = ($page - 1) * $perPage;
        $filters = [
            'q' => $request->query('q'),
            'status' => $request->query('status'),
            'show_on_home' => $request->query('show_on_home'),
        ];

        $items = $this->feature->allForAdmin($filters, $perPage, $offset);
        $total = $this->feature->countForAdmin($filters);

        $response->json([
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->feature?->findById($id);
        if (!$item) {
            $response->json(['error' => 'Program Utama tidak ditemukan'], 404);
            return;
        }
        $response->json(['data' => $item]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->feature) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $errors = $this->validate($body);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $id = $this->feature->create($this->sanitizePayload($body));
        if ($id <= 0) {
            $response->json(['error' => 'Gagal menyimpan Program Utama'], 500);
            return;
        }

        $response->json(['data' => ['id' => $id]], 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->feature) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $body = $request->json();
        $errors = $this->validate($body, false);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $success = $this->feature->update($id, $this->sanitizePayload($body, false));
        if (!$success) {
            $response->json(['error' => 'Gagal memperbarui Program Utama'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'updated' => true]]);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->feature?->softDelete($id) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal menghapus Program Utama'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }

        $result = $this->storeUploadedFile($_FILES['image']);
        if (isset($result['error'])) {
            $response->json(['error' => $result['error']], 422);
            return;
        }

        $response->json(['data' => $result], 201);
    }

    public function deleteImage(Request $request, Response $response, array $params): void
    {
        $featureId = (int) ($params['id'] ?? 0);
        $imageId = (int) ($params['imageId'] ?? 0);
        $path = $this->feature?->deleteImage($featureId, $imageId);
        if ($path === null) {
            $response->json(['error' => 'Gambar tidak ditemukan'], 404);
            return;
        }

        $this->removeUploadedFile($path);
        $response->json(['data' => ['id' => $imageId, 'deleted' => true]]);
    }

    public function reorderImages(Request $request, Response $response, array $params): void
    {
        $featureId = (int) ($params['id'] ?? 0);
        $body = $request->json();
        $imageIds = array_values(array_filter(array_map('intval', $body['image_ids'] ?? [])));
        $success = $this->feature?->reorderImages($featureId, $imageIds) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal mengurutkan gambar'], 422);
            return;
        }
        $response->json(['data' => ['feature_id' => $featureId, 'reordered' => true]]);
    }

    /** @return array<int, string> */
    private function validate(array $body, bool $isCreate = true): array
    {
        $errors = [];
        $title = trim((string) ($body['title'] ?? ''));
        $name = trim((string) ($body['name'] ?? ''));

        if ($isCreate || array_key_exists('title', $body)) {
            if ($title === '') {
                $errors[] = 'Label singkat wajib diisi.';
            }
        }
        if ($isCreate || array_key_exists('name', $body)) {
            if ($name === '') {
                $errors[] = 'Nama Program Utama wajib diisi.';
            }
        }
        if (array_key_exists('icon_key', $body) && !in_array((string) $body['icon_key'], self::ICON_KEYS, true)) {
            $errors[] = 'Ikon Program Utama tidak valid.';
        }
        if (array_key_exists('images', $body) && is_array($body['images'])) {
            foreach ($body['images'] as $image) {
                $path = trim((string) (($image['path'] ?? $image['url'] ?? $image) ?: ''));
                if ($path === '') {
                    $errors[] = 'Setiap gambar harus memiliki path yang valid.';
                    break;
                }
            }
        }

        return $errors;
    }

    /** @return array<string, mixed> */
    private function sanitizePayload(array $body, bool $includeDefaults = true): array
    {
        $payload = [];
        $fields = [
            'title' => 120,
            'name' => 255,
            'description' => 5000,
            'focus' => 120,
            'icon_key' => 80,
        ];

        foreach ($fields as $field => $limit) {
            if (!array_key_exists($field, $body)) {
                if ($includeDefaults) {
                    $payload[$field] = '';
                }
                continue;
            }
            $payload[$field] = strip_tags(mb_substr(trim((string) $body[$field]), 0, $limit));
        }

        if (array_key_exists('show_on_home', $body) || $includeDefaults) {
            $payload['show_on_home'] = !empty($body['show_on_home']);
        }
        if (array_key_exists('sort_order', $body) || $includeDefaults) {
            $payload['sort_order'] = (int) ($body['sort_order'] ?? 0);
        }
        if (array_key_exists('status', $body) || $includeDefaults) {
            $status = strtolower(trim((string) ($body['status'] ?? 'draft')));
            $payload['status'] = in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft';
        }
        if (array_key_exists('images', $body) || $includeDefaults) {
            $payload['images'] = $body['images'] ?? [];
        }

        return $payload;
    }

    /** @param array<string, mixed> $file @return array<string, string> */
    private function storeUploadedFile(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload gagal.'];
        }
        if (($file['size'] ?? 0) > self::MAX_UPLOAD_SIZE) {
            return ['error' => 'Ukuran file melebihi batas 5MB.'];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $tmp !== '' ? (string) $finfo->file($tmp) : '';
        if (!in_array($mime, self::ALLOWED_IMAGE_TYPES, true) || @getimagesize($tmp) === false) {
            return ['error' => 'Hanya gambar JPEG, PNG, WebP, atau GIF yang diizinkan.'];
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $directory = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $htaccess = $directory . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        }

        $filename = 'feature-' . bin2hex(random_bytes(10)) . '.' . $extension;
        $destination = $directory . $filename;
        if (!move_uploaded_file($tmp, $destination)) {
            return ['error' => 'Gagal menyimpan file ke disk.'];
        }

        return [
            'url' => self::UPLOAD_DIR . $filename,
            'filename' => $filename,
            'path' => self::UPLOAD_DIR . $filename,
        ];
    }

    private function removeUploadedFile(string $path): void
    {
        if (!str_starts_with($path, self::UPLOAD_DIR)) {
            return;
        }
        $file = dirname(__DIR__, 3) . '/public' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
