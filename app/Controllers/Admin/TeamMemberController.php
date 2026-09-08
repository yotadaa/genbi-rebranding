<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\TeamMember;

final class TeamMemberController
{
    public function __construct(private ?TeamMember $team = null) {}

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = min(100, max(1, (int) ($request->query('per_page') ?? 12)));
        $filters = [
            'q' => $request->query('q'),
            'division' => $request->query('division'),
            'campus' => $request->query('campus'),
            'year' => $request->query('year'),
        ];
        $offset = ($page - 1) * $perPage;

        $items = $this->team?->allForAdmin($filters, $perPage, $offset) ?? [];
        $total = $this->team?->countPublic($filters) ?? count($items);
        $options = $this->team?->filterOptions() ?? ['divisions' => [], 'campuses' => [], 'years' => []];

        $response->json(['data' => $items, 'filters' => $options, 'options' => $this->team?->formOptions() ?? ['divisions' => [], 'commissions' => []], 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total]]);
    }

    public function options(Request $request, Response $response): void
    {
        $query = trim((string) ($request->query('q') ?? ''));
        $divisionId = (int) ($request->query('division_id') ?? $request->query('divisi_id') ?? 0);
        $activeOnly = (string) ($request->query('active_only') ?? '') === '1';
        if ($query !== '' || $divisionId > 0 || $activeOnly || $request->query('type') === 'member') {
            $limit = min(500, max(1, (int) ($request->query('limit') ?? 12)));
            $filters = [];
            if ($divisionId > 0) {
                $filters['division_id'] = $divisionId;
            }
            if ($activeOnly) {
                $filters['active_only'] = true;
            }
            $response->json(['data' => $this->team?->searchOptions($query, $limit, $filters) ?? []]);
            return;
        }

        $response->json(['data' => $this->team?->formOptions() ?? ['divisions' => [], 'commissions' => []]]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->team?->findById($id);
        if (!$item) {
            $response->json(['error' => 'Anggota tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => $item]);
    }

    public function store(Request $request, Response $response): void
    {
        $body = $request->json();
        $errors = $this->validate($body);
        if ($errors) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $id = $this->team?->create($this->sanitize($body));
        if (!$id) {
            $response->json(['error' => 'Gagal menyimpan anggota'], 500);
            return;
        }

        $response->json(['data' => ['id' => $id]], 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $body = $request->json();
        $errors = $this->validate($body);
        if ($errors) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $success = $this->team?->update($id, $this->sanitize($body));
        if (!$success) {
            $response->json(['error' => 'Gagal memperbarui anggota'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'updated' => true]]);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->team?->softDelete($id);
        if (!$success) {
            $response->json(['error' => 'Gagal menghapus anggota'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    public function alumni(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $count = $this->team?->setAlumniStatus([$id]) ?? 0;
        if ($count < 1) {
            $response->json(['error' => 'Gagal menjadikan anggota alumni'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'alumni' => true]]);
    }

    public function setHome(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $visible = (bool) ($request->json()['show_on_home'] ?? true);
        $count = $this->team?->setHomeVisibility([$id], $visible) ?? 0;
        if ($count < 1) {
            $response->json(['error' => 'Gagal memperbarui pilihan BPI landing'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'show_on_home' => $visible]]);
    }

    public function bulk(Request $request, Response $response): void
    {
        $body = $request->json();
        $ids = is_array($body['ids'] ?? null) ? array_map('intval', $body['ids']) : [];
        $action = (string) ($body['action'] ?? '');
        if (!$ids) {
            $response->json(['error' => 'Pilih minimal satu anggota'], 422);
            return;
        }

        $affected = match ($action) {
            'home_add' => $this->team?->setHomeVisibility($ids, true) ?? 0,
            'home_remove' => $this->team?->setHomeVisibility($ids, false) ?? 0,
            'delete' => $this->team?->bulkDelete($ids) ?? 0,
            'alumni' => $this->team?->setAlumniStatus($ids) ?? 0,
            default => -1,
        };

        if ($affected < 0) {
            $response->json(['error' => 'Aksi batch tidak valid'], 422);
            return;
        }

        $response->json(['data' => ['affected' => $affected, 'action' => $action]]);
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }

        $file = $_FILES['image'];
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if (!is_array($file) || $uploadError !== UPLOAD_ERR_OK) {
            $errorMsg = match ($uploadError) {
                UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas konfigurasi server (php.ini).',
                UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form.',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah.',
                default => 'Upload gagal (Error Code: ' . $uploadError . ').',
            };
            $response->json(['error' => $errorMsg], 422);
            return;
        }

        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > 10 * 1024 * 1024) {
            $response->json(['error' => 'Upload tidak valid atau melebihi batas 10MB'], 422);
            return;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = is_file($tmp) && $finfo ? (string) finfo_file($finfo, $tmp) : '';
        
        $imageSize = @getimagesize($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true) || $imageSize === false) {
            $response->json(['error' => 'File harus berupa gambar valid'], 422);
            return;
        }

        if ($imageSize[0] !== $imageSize[1]) {
            $response->json(['error' => 'Rasio foto harus 1:1 (lebar dan tinggi sama)'], 422);
            return;
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $directory = dirname(__DIR__, 3) . '/public/uploads/team';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $htaccess = $directory . '/.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\nOptions -ExecCGI\nAddType text/plain .php .phtml .php3 .php4 .php5\n");
        }
        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $directory . '/' . $filename)) {
            $response->json(['error' => 'Gagal menyimpan file'], 500);
            return;
        }

        $response->json(['data' => ['url' => '/uploads/team/' . $filename, 'filename' => 'team/' . $filename]]);
    }

    /** @return array<int, string> */
    private function validate(array $body): array
    {
        $errors = [];
        if (trim((string) ($body['name'] ?? '')) === '') $errors[] = 'Nama anggota wajib diisi';
        if (mb_strlen((string) ($body['name'] ?? '')) > 255) $errors[] = 'Nama maksimal 255 karakter';
        
        if (trim((string) ($body['designation'] ?? '')) === '') $errors[] = 'Jabatan anggota wajib diisi';
        if (empty($body['komsat_id'])) $errors[] = 'Komisariat wajib dipilih';
        if (empty($body['divisi_id'])) $errors[] = 'Divisi wajib dipilih';
        if (empty($body['tahun'])) $errors[] = 'Tahun wajib diisi';
        
        if (!empty($body['email']) && !filter_var((string) $body['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
        return $errors;
    }

    /** @return array<string, mixed> */
    private function sanitize(array $body): array
    {
        $text = static fn(string $key, int $limit = 255): string => strip_tags(mb_substr(trim((string) ($body[$key] ?? '')), 0, $limit));

        return [
            'name' => $text('name'),
            'designation' => $text('designation'),
            'detail' => strip_tags(mb_substr(trim((string) ($body['detail'] ?? '')), 0, 5000), '<p><br><strong><em><ul><ol><li>'),
            'photo' => $text('photo', 500),
            'instagram' => $text('instagram', 255),
            'facebook' => $text('facebook', 255),
            'linkedin' => $text('linkedin', 255),
            'phone' => $text('phone', 80),
            'email' => $text('email', 255),
            'website' => $text('website', 255),
            'komsat_id' => (int) ($body['komsat_id'] ?? 0),
            'divisi_id' => (int) ($body['divisi_id'] ?? 0),
            'komsat' => $text('komsat', 255),
            'tahun' => max(2000, min(2100, (int) ($body['tahun'] ?? date('Y')))),
            'show_on_home' => !empty($body['show_on_home']),
            'home_sort_order' => (int) ($body['home_sort_order'] ?? 0),
        ];
    }
}
