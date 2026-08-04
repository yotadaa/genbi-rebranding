<?php

declare(strict_types=1);

namespace App\Models;

class Buku
{
    // Konstruktor menyuntikkan koneksi database (PDO)
    public function __construct(private ?\PDO $db = null) {}

    /**
     * Fungsi pembersih & pemetaan data (Mapping Row)
     * Mengubah nama kolom SQL mentah menjadi format rapi dan siap dipake oleh View Frontend
     */
    public static function mapRow(array $row): array
    {
        $bytes = (int) ($row['file_size_bytes'] ?? 0);

        // Konversi otomatis ukuran file byte menjadi KB/MB
        $fileSizeFormatted = '0 KB';
        if ($bytes > 1048576) {
            $fileSizeFormatted = round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes > 0) {
            $fileSizeFormatted = round($bytes / 1024, 0) . ' KB';
        }

        return [
            'id' => (int) ($row['buku_id'] ?? $row['id'] ?? 0),
            'judul' => (string) ($row['judul'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'file_path' => (string) ($row['file_path'] ?? ''),
            'penulis' => (string) ($row['penulis'] ?? 'GenBI Jambi'),
            'penerbit' => (string) ($row['penerbit'] ?? 'Bank Indonesia'),
            'deskripsi' => (string) ($row['deskripsi'] ?? ''),
            'sinopsis' => (string) ($row['sinopsis'] ?? 'Informasi Literasi & Karya GenBI Jambi'),
            // Menggunakan foto cover utama jika tersedia
            'cover' => (string) ($row['foto_cover_buku'] ?? $row['cover_image'] ?? ''),
            'tahun' => (string) ($row['tahun_terbit'] ?? date('Y')),
            'isbn' => (string) ($row['isbn'] ?? '-'),
            'halaman' => ((int) ($row['page_count'] ?? 0)) > 0 ? (int) $row['page_count'] . ' Halaman' : '-',
            'kategori' => (string) ($row['kategori'] ?? 'Publikasi'),
            'status' => (string) ($row['status'] ?? 'draft'),
            'view_count' => (int) ($row['view_count'] ?? 0),
            'download_count' => (int) ($row['download_count'] ?? 0),
            'file_size_formatted' => $fileSizeFormatted,
        ];
    }

    /**
     * Mengambil daftar buku yang terbit (published) dari database, lengkap dengan LIMIT & OFFSET
     */
    public function getPublished(int $limit = 12, int $offset = 0, ?string $kategori = null): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            // Kita siapkan query dasar
            $sql = "SELECT * FROM tbl_buku WHERE deleted_at IS NULL AND status = 'published'";

            // Jika ada permintaan filter kategori dari user
            if ($kategori !== null && $kategori !== '' && $kategori !== 'Semua') {
                $sql .= " AND kategori = :kategori";
            }

            $sql .= " ORDER BY tahun_terbit DESC, created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            if ($kategori !== null && $kategori !== '' && $kategori !== 'Semua') {
                $stmt->bindValue(':kategori', $kategori, \PDO::PARAM_STR);
            }

            // Bind parameter batas agar aman dari penyusup
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Map seluruh hasil query agar siap diteruskan
            return array_map([self::class, 'mapRow'], $results);
        } catch (\Throwable $exception) {
            error_log('[Model Buku Error] ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Menghitung total seluruh buku (Untuk keperluan halaman/pagination)
     */
    public function countPublished(?string $kategori = null): int
    {
        if (!$this->db) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) FROM tbl_buku WHERE deleted_at IS NULL AND status = 'published'";
            if ($kategori !== null && $kategori !== '' && $kategori !== 'Semua') {
                $sql .= " AND kategori = :kategori";
            }

            $stmt = $this->db->prepare($sql);
            if ($kategori !== null && $kategori !== '' && $kategori !== 'Semua') {
                $stmt->bindValue(':kategori', $kategori, \PDO::PARAM_STR);
            }

            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Mengambil daftar seluruh buku untuk halaman Admin (termasuk Draft)
     */
    public function allForAdmin(int $limit = 25, int $offset = 0, array $filters = []): array
    {
        if (!$this->db) return [];

        try {
            $sql = "SELECT * FROM tbl_buku WHERE deleted_at IS NULL";
            $params = [];

            // Filter status jika ada
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }

            // Filter pencarian kata kunci
            if (!empty($filters['q'])) {
                $sql .= " AND (judul LIKE :q OR penulis LIKE :q OR kategori LIKE :q)";
                $params[':q'] = "%" . trim($filters['q']) . "%";
            }

            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            error_log('[Buku Admin All Error] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Menghitung total buku untuk paginasi Admin
     */
    public function countForAdmin(array $filters = []): int
    {
        if (!$this->db) return 0;
        try {
            $sql = "SELECT COUNT(*) FROM tbl_buku WHERE deleted_at IS NULL";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['q'])) {
                $sql .= " AND (judul LIKE :q OR penulis LIKE :q OR kategori LIKE :q)";
                $params[':q'] = "%" . trim($filters['q']) . "%";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Mencari satu buku berdasarkan ID (Untuk Edit & Preview)
     */
    public function find(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM tbl_buku WHERE buku_id = :id AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? self::mapRow($row) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Menambahkan data buku baru ke database
     */
    public function create(array $data): int
    {
        if (!$this->db) return 0;
        try {
            $stmt = $this->db->prepare("INSERT INTO tbl_buku 
                (judul, slug, file_path, penulis, penerbit, deskripsi, sinopsis, foto_cover_buku, tahun_terbit, isbn, page_count, kategori, status, created_at) 
                VALUES 
                (:judul, :slug, :file_path, :penulis, :penerbit, :deskripsi, :sinopsis, :foto_cover, :tahun, :isbn, :page_count, :kategori, :status, NOW())");

            $stmt->execute([
                ':judul' => $data['judul'] ?? '',
                ':slug' => $data['slug'] ?? '',
                ':file_path' => $data['file_path'] ?? '',
                ':penulis' => $data['penulis'] ?? 'GenBI Jambi',
                ':penerbit' => $data['penerbit'] ?? 'Bank Indonesia',
                ':deskripsi' => $data['deskripsi'] ?? '',
                ':sinopsis' => $data['sinopsis'] ?? '',
                ':foto_cover' => $data['cover'] ?? '',
                ':tahun' => (int) ($data['tahun'] ?? date('Y')),
                ':isbn' => $data['isbn'] ?? '-',
                ':page_count' => (int) ($data['halaman'] ?? 0),
                ':kategori' => $data['kategori'] ?? 'Publikasi',
                ':status' => $data['status'] ?? 'published',
            ]);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[Buku Create Error] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Memperbarui data buku yang ada
     */
    public function update(int $id, array $data): bool
    {
        if (!$this->db || $data === []) return false;

        $map = [
            'judul' => 'judul',
            'slug' => 'slug',
            'file_path' => 'file_path',
            'penulis' => 'penulis',
            'penerbit' => 'penerbit',
            'deskripsi' => 'deskripsi',
            'sinopsis' => 'sinopsis',
            'cover' => 'foto_cover_buku',
            'tahun' => 'tahun_terbit',
            'isbn' => 'isbn',
            'halaman' => 'page_count',
            'kategori' => 'kategori',
            'status' => 'status',
        ];

        $fields = [];
        $params = [':id' => $id];

        foreach ($map as $key => $col) {
            if (array_key_exists($key, $data)) {
                $fields[] = "$col = :$key";
                $params[":$key"] = in_array($key, ['tahun', 'halaman']) ? (int) $data[$key] : $data[$key];
            }
        }

        if ($fields === []) return false;

        try {
            $sql = "UPDATE tbl_buku SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE buku_id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('[Buku Update Error] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus lembut (Soft Delete) dari database
     */
    public function delete(int $id): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("UPDATE tbl_buku SET deleted_at = NOW() WHERE buku_id = :id AND deleted_at IS NULL");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
