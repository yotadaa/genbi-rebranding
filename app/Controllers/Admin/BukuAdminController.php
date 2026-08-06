<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Buku;

class BukuAdminController
{
    private const UPLOAD_DIR = '/uploads/buku/';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5 MB (Foto Cover)
    private const MAX_PDF_SIZE = 25 * 1024 * 1024;   // 25 MB (Dokumen PDF)
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];

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

        // 1. Validasi Server-Side (Wajib Isi Semua Input Pokok untuk Melindungi Database & Server)
        if (empty($payload['judul']) || strlen(trim((string) $payload['judul'])) < 3) {
            $response->json(['error' => 'Judul buku wajib diisi dan minimal 3 karakter.', 'field' => 'judul'], 422);
            return;
        }
        if (empty($payload['penulis'])) {
            $response->json(['error' => 'Penulis / Tim Penyusun wajib diisi.', 'field' => 'penulis'], 422);
            return;
        }
        if (empty($payload['penerbit'])) {
            $response->json(['error' => 'Penerbit / Instansi wajib diisi.', 'field' => 'penerbit'], 422);
            return;
        }
        if (empty($payload['sinopsis']) || strlen(trim((string) $payload['sinopsis'])) < 10) {
            $response->json(['error' => 'Sinopsis / ringkasan karya wajib diisi (minimal 10 karakter).', 'field' => 'sinopsis'], 422);
            return;
        }
        if (empty($payload['tahun']) || (int) $payload['tahun'] < 1900 || (int) $payload['tahun'] > 2100) {
            $response->json(['error' => 'Tahun terbit wajib diisi dengan rentang tahun yang logis (1900 - 2100).', 'field' => 'tahun'], 422);
            return;
        }
        if (empty($payload['halaman']) || (int) $payload['halaman'] < 1) {
            $response->json(['error' => 'Jumlah halaman wajib diisi (minimal 1 halaman).', 'field' => 'halaman'], 422);
            return;
        }
        if (empty($payload['path_flipbook'])) {
            $response->json(['error' => 'Tautan file Flipbook wajib diisi untuk kemudahan baca online pengunjung.', 'field' => 'path_flipbook'], 422);
            return;
        }
        if (empty($payload['cover'])) {
            $response->json(['error' => 'Foto cover buku wajib diunggah (maksimal 5 MB, format JPG/PNG/JPEG).', 'field' => 'cover'], 422);
            return;
        }
        if (empty($payload['file_path'])) {
            $response->json(['error' => 'File dokumen PDF wajib diunggah (maksimal 25 MB).', 'field' => 'file_path'], 422);
            return;
        }

        if (empty($payload['slug'])) {
            $payload['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $payload['judul']), '-'));
        }

        $id = $this->buku?->create($payload) ?? 0;
        $response->json($id ? ['data' => ['id' => $id]] : ['error' => 'Gagal menyimpan buku ke database.'], $id ? 201 : 500);
    }

    // Untuk memperbarui data buku berdasarkan ID (Mendukung Partial & Full Update)
    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $payload = $this->sanitize($request->json());

        if ($payload === []) {
            $response->json(['error' => 'Tidak ada data perubahan yang dikirim.'], 422);
            return;
        }

        // 1. Ambil data buku lama dari database untuk memastikan data ada dan untuk pembandingan file
        $oldBook = $this->buku?->find($id);
        if (!$oldBook) {
            $response->json(['error' => 'Data buku tidak ditemukan di database.'], 404);
            return;
        }

        // 2. Validasi Server-Side saat parameter dikirim dalam request (Mencegah input diganti jadi kosong)
        if (array_key_exists('judul', $payload) && (empty($payload['judul']) || strlen(trim((string) $payload['judul'])) < 3)) {
            $response->json(['error' => 'Judul buku tidak boleh kosong (minimal 3 karakter).', 'field' => 'judul'], 422);
            return;
        }
        if (array_key_exists('penulis', $payload) && empty($payload['penulis'])) {
            $response->json(['error' => 'Penulis / Tim Penyusun tidak boleh kosong.', 'field' => 'penulis'], 422);
            return;
        }
        if (array_key_exists('penerbit', $payload) && empty($payload['penerbit'])) {
            $response->json(['error' => 'Penerbit / Instansi tidak boleh kosong.', 'field' => 'penerbit'], 422);
            return;
        }
        if (array_key_exists('sinopsis', $payload) && (empty($payload['sinopsis']) || strlen(trim((string) $payload['sinopsis'])) < 10)) {
            $response->json(['error' => 'Sinopsis tidak boleh kosong (minimal 10 karakter).', 'field' => 'sinopsis'], 422);
            return;
        }
        if (array_key_exists('tahun', $payload) && ((int) $payload['tahun'] < 1900 || (int) $payload['tahun'] > 2100)) {
            $response->json(['error' => 'Tahun terbit tidak valid (range 1900 - 2100).', 'field' => 'tahun'], 422);
            return;
        }
        if (array_key_exists('halaman', $payload) && (int) $payload['halaman'] < 1) {
            $response->json(['error' => 'Jumlah halaman tidak valid (minimal 1 halaman).', 'field' => 'halaman'], 422);
            return;
        }
        if (array_key_exists('path_flipbook', $payload) && empty($payload['path_flipbook'])) {
            $response->json(['error' => 'Tautan file Flipbook tidak boleh dikosongkan.', 'field' => 'path_flipbook'], 422);
            return;
        }
        if (array_key_exists('cover', $payload) && empty($payload['cover']) && empty($oldBook['cover'])) {
            $response->json(['error' => 'Foto cover buku wajib ada (format JPG/PNG/JPEG maks 5 MB).', 'field' => 'cover'], 422);
            return;
        }
        if (array_key_exists('file_path', $payload) && empty($payload['file_path']) && empty($oldBook['file_path'])) {
            $response->json(['error' => 'File dokumen PDF wajib ada (maksimal 25 MB).', 'field' => 'file_path'], 422);
            return;
        }

        // 3. Penanganan Slug: jika judul diubah tapi slug kosong, buat otomatis atau pertahankan yang lama
        if (array_key_exists('judul', $payload) && empty($payload['slug'])) {
            $payload['slug'] = !empty($oldBook['slug']) ? $oldBook['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $payload['judul']), '-'));
        }

        // 4. Manajemen File Fisik: jika cover atau file dokumen berganti dengan URL baru, bersihkan file fisik lama di server
        if (!empty($payload['cover']) && $payload['cover'] !== ($oldBook['cover'] ?? '')) {
            $this->cleanupPhysicalFile((string) ($oldBook['cover'] ?? ''));
        }
        if (!empty($payload['file_path']) && $payload['file_path'] !== ($oldBook['file_path'] ?? '')) {
            $this->cleanupPhysicalFile((string) ($oldBook['file_path'] ?? ''));
        }

        // 5. Simpan pemutakhiran (hanya inputan yang diubah/dikirim yang akan diupdate ke MySQL, sisanya aman tidak berubah)
        $ok = $this->buku?->update($id, $payload) ?? false;
        $response->json($ok ? ['data' => ['id' => $id, 'updated' => true]] : ['error' => 'Gagal memperbarui data buku.'], $ok ? 200 : 500);
    }

    // Untuk menghapus data buku berdasarkan ID sekaligus membersihkan file fisik di server (c-panel storage)
    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        // 1. Ambil informasi buku terlebih dahulu sebelum di-delete dari database
        $book = $this->buku?->find($id);
        if (!$book) {
            $response->json(['error' => 'Data buku tidak ditemukan di dalam database'], 404);
            return;
        }

        // 2. Lakukan penghapusan permanen dari tabel MySQL (Hard Delete)
        $ok = $this->buku?->delete($id) ?? false;

        if ($ok) {
            // 3. Jika berhasil dihapus di DB, hapus juga file fisik di storage agar server tidak penuh file sampah
            $this->cleanupPhysicalFile((string) ($book['cover'] ?? ''));
            $this->cleanupPhysicalFile((string) ($book['file_path'] ?? ''));
            $response->json(['data' => ['id' => $id, 'deleted' => true]], 200);
        } else {
            $response->json(['error' => 'Gagal menghapus data dari database server'], 500);
        }
    }

    /**
     * Helper untuk membersihkan file fisik dari folder public server jika file ada
     */
    private function cleanupPhysicalFile(string $urlPath): void
    {
        if (empty($urlPath) || !str_starts_with($urlPath, '/uploads/')) {
            return; // Hanya hapus file yang disimpan di folder /uploads/
        }
        $physicalPath = dirname(__DIR__, 3) . '/public' . $urlPath;
        if (is_file($physicalPath)) {
            @unlink($physicalPath);
        }
    }

    // Untuk mengunggah file gambar (cover buku) atau dokumen (PDF)
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
        $maxSize = $isPdfUpload ? self::MAX_PDF_SIZE : self::MAX_UPLOAD_SIZE;
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
            $response->json(['error' => 'Terjadi kesalahan saat mengunggah file. Harap coba lagi.'], 422);
            return;
        }
        if ($file['size'] > $maxSize) {
            $response->json(['error' => 'Ukuran file melebihi batas maksimal (' . ($isPdfUpload ? '25 MB untuk PDF' : '5 MB untuk Foto Cover') . ')'], 422);
            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        // 2. Validasi Tipe File yang Ketat (Foto harus JPG/PNG/JPEG & PDF harus .pdf)
        if ($isPdfUpload) {
            if ($mime !== 'application/pdf') {
                $response->json(['error' => 'Format file dokumen wajib berupa PDF (.pdf) dan maksimal 25 MB.'], 422);
                return;
            }
            $ext = 'pdf';
            $prefix = 'ebook-';
        } else {
            if (!in_array($mime, self::ALLOWED_IMAGE_TYPES, true) || @getimagesize($file['tmp_name']) === false) {
                $response->json(['error' => 'Format file cover wajib berupa gambar (JPG, PNG, atau JPEG) dan maksimal 5 MB.'], 422);
                return;
            }
            $ext = match ($mime) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg'
            };
            $prefix = 'cover-';
        }

        // 3. Persiapan Folder Tujuan
        $dir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // Buat htaccess keamanan (hanya mematikan eksekusi script PHP)
        $htaccess = $dir . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        }

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
        $fields = ['judul', 'slug', 'file_path', 'path_flipbook', 'penulis', 'penerbit', 'deskripsi', 'sinopsis', 'cover', 'tahun', 'isbn', 'halaman', 'kategori', 'status'];

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
