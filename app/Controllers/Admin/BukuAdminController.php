<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Buku;

class BukuAdminController
{
    private const UPLOAD_DIR = '/uploads/buku/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private ?Buku $buku = null) {}

    // Untuk menampilkan daftar buku dengan pagination dan filter
    public function index(Request $request, Response $response): void
    {
        $limit = (int) ($request->query('per_page') ?: 25);
        $offset = (int) ($request->query('page') ? (($request->query('page') - 1) * $limit) : 0);
        $filters = [
            'status' => (string) $request->query('status'),
            'q' => (string) $request->query('q'),
        ];

        $response->json([
            'data' => $this->buku?->allForAdmin($limit, $offset, $filters) ?? [],
            'total' => $this->buku?->countForAdmin($filters) ?? 0,
        ]);
    }

    // Untuk menampilkan detail buku berdasarkan ID
    public function show(Request $request, Response $response, array $params): void
    {
        $item = $this->buku?->find((int) ($params['id'] ?? 0));
        if (!$item) {
            $response->json(['error' => 'Buku tidak ditemukan'], 404);
            return;
        }
        $response->json(['data' => $item]);
    }

    // Untuk menyimpan data buku baru
    public function store(Request $request, Response $response): void
    {
        $payload = $this->sanitize($request->json());
        if (empty($payload['judul'])) {
            $response->json(['error' => 'Judul buku wajib diisi'], 422);
            return;
        }

        if (empty($payload['slug'])) {
            $payload['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $payload['judul']), '-'));
        }

        $id = $this->buku?->create($payload) ?? 0;
        $response->json($id ? ['data' => ['id' => $id]] : ['error' => 'Gagal menyimpan buku'], $id ? 201 : 500);
    }

    // Untuk memperbarui data buku berdasarkan ID
    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $payload = $this->sanitize($request->json());

        if (array_key_exists('judul', $payload) && empty($payload['slug'])) {
            $payload['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $payload['judul']), '-'));
        }

        if ($payload === []) {
            $response->json(['error' => 'Tidak ada data perubahan yang dikirim'], 422);
            return;
        }

        $ok = $this->buku?->update($id, $payload) ?? false;
        $response->json($ok ? ['data' => ['id' => $id, 'updated' => true]] : ['error' => 'Gagal memperbarui data buku'], $ok ? 200 : 404);
    }

    // Untuk menghapus data buku berdasarkan ID
    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $ok = $this->buku?->delete($id) ?? false;
        $response->json($ok ? ['data' => ['id' => $id, 'deleted' => true]] : ['error' => 'Gagal menghapus buku'], $ok ? 200 : 404);
    }

    // Untuk mengunggah file gambar (cover buku)
    public function upload(Request $request, Response $response): void
    {
        // 1. Cek apakah ada file yang diupload (bisa bernama 'cover', 'image', atau 'pdf', 'file')
        if (empty($_FILES['cover']) && empty($_FILES['image']) && empty($_FILES['pdf']) && empty($_FILES['file'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }
        $file = $_FILES['cover'] ?? $_FILES['image'] ?? $_FILES['pdf'] ?? $_FILES['file'];
        $isPdfUpload = !empty($_FILES['pdf']) || !empty($_FILES['file']);
        // Batasan ukuran: Cover Foto maks 5MB, Dokumen PDF maks 25MB
        $maxSize = $isPdfUpload ? (25 * 1024 * 1024) : (5 * 1024 * 1024);
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0 || $file['size'] > $maxSize) {
            $response->json(['error' => 'Upload gagal atau ukuran file melebihi batas maksimal (' . ($isPdfUpload ? '25MB' : '5MB') . ')'], 422);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        // 2. Validasi Tipe File
        if ($isPdfUpload) {
            if ($mime !== 'application/pdf') {
                $response->json(['error' => 'Format file dokumen harus berupa PDF (.pdf)'], 422);
                return;
            }
            $ext = 'pdf';
            $prefix = 'ebook-';
        } else {
            if (!in_array($mime, self::ALLOWED_IMAGE_TYPES, true) || @getimagesize($file['tmp_name']) === false) {
                $response->json(['error' => 'Format file harus berupa gambar (JPG, PNG, WEBP)'], 422);
                return;
            }
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg'
            };
            $prefix = 'cover-';
        }
        // 3. Persiapan Folder Tujuan
        $dir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        // Buat htaccess keamanan (hanya mematikan eksekusi script PHP)
        $htaccess = $dir . '.htaccess';
        if (!is_file($htaccess)) file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        // 4. Simpan dengan nama acak aman
        $filename = $prefix . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $response->json(['error' => 'Gagal menyimpan file ke sistem server'], 500);
            return;
        }
        $response->json(['data' => ['url' => self::UPLOAD_DIR . $filename, 'filename' => $filename]], 201);
    }

    // Fungsi untuk membersihkan dan memvalidasi data input
    private function sanitize(array $body): array
    {
        $clean = [];
        $fields = ['judul', 'slug', 'file_path', 'penulis', 'penerbit', 'deskripsi', 'sinopsis', 'cover', 'tahun', 'isbn', 'halaman', 'kategori', 'status'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $body)) {
                if (in_array($field, ['tahun', 'halaman'], true)) {
                    $clean[$field] = (int) $body[$field];
                } else {
                    $clean[$field] = trim(strip_tags((string) $body[$field]));
                }
            }
        }
        return $clean;
    }
}
