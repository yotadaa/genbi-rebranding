<?php

declare(strict_types=1);

namespace App\Models;

class Prestasi
{
    public function __construct(private ?\PDO $db = null) {}

    public static function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['prestasi_id'] ?? $row['id'] ?? 0),
            'prestasi_id' => (int) ($row['prestasi_id'] ?? $row['id'] ?? 0),
            'slug' => $row['slug'] ?? '',
            'title' => $row['judul_prestasi'] ?? $row['title'] ?? '',
            'name' => $row['nama_anggota'] ?? $row['name'] ?? '',
            'campus' => $row['komisariat'] ?? $row['campus'] ?? '',
            'category' => $row['kategori'] ?? $row['category'] ?? '',
            'year' => $row['tahun'] ?? $row['year'] ?? '',
            'description' => $row['deskripsi_singkat'] ?? $row['description'] ?? '',
            'content' => $row['deskripsi_lengkap'] ?? $row['content'] ?? '',
            'image' => $row['foto'] ?? $row['image'] ?? '',
            'institution' => $row['institusi_penyelenggara'] ?? $row['institution'] ?? '',
            'status' => $row['status'] ?? 'published',
            'created_at' => $row['created_at'] ?? '',
            'updated_at' => $row['updated_at'] ?? '',
        ];
    }

    public function published(int $limit = 20, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi WHERE status = :status AND deleted_at IS NULL ORDER BY tahun DESC, created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':status', 'published', \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function findBySlug(string $slug): ?array
    {
        if (!$this->db) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi WHERE slug = :slug AND status = :status AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        $stmt->bindValue(':status', 'published', \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    public function findById(int $id): ?array
    {
        if (!$this->db) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi WHERE prestasi_id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    public function all(int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function create(array $data): int
    {
        if (!$this->db) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tbl_prestasi (judul_prestasi, slug, nama_anggota, komisariat, kategori, tahun, deskripsi_singkat, deskripsi_lengkap, foto, institusi_penyelenggara, status, created_at) VALUES (:title, :slug, :name, :campus, :category, :year, :description, :content, :image, :institution, :status, NOW())'
        );
        $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':name' => $data['name'] ?? '',
            ':campus' => $data['campus'] ?? '',
            ':category' => $data['category'] ?? '',
            ':year' => $data['year'] ?? '',
            ':description' => $data['description'] ?? '',
            ':content' => $data['content'] ?? '',
            ':image' => $data['image'] ?? '',
            ':institution' => $data['institution'] ?? '',
            ':status' => $data['status'] ?? 'draft',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->db) {
            return false;
        }

        $fields = [];
        $params = [':id' => $id];
        $map = [
            'title' => 'judul_prestasi',
            'slug' => 'slug',
            'name' => 'nama_anggota',
            'campus' => 'komisariat',
            'category' => 'kategori',
            'year' => 'tahun',
            'description' => 'deskripsi_singkat',
            'content' => 'deskripsi_lengkap',
            'image' => 'foto',
            'institution' => 'institusi_penyelenggara',
            'status' => 'status',
        ];

        foreach ($map as $key => $column) {
            if (array_key_exists($key, $data)) {
                $fields[] = "$column = :$key";
                $params[":$key"] = $data[$key];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE tbl_prestasi SET ' . implode(', ', $fields) . ' WHERE prestasi_id = :id AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDelete(int $id): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE tbl_prestasi SET deleted_at = NOW() WHERE prestasi_id = :id AND deleted_at IS NULL');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}
