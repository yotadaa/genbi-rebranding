<?php

declare(strict_types=1);

namespace App\Models;

class Prestasi
{
    public function __construct(private ?\PDO $db = null) {}

    public static function mapRow(array $row): array
    {
        $title = $row['judul_prestasi'] ?? $row['title'] ?? '';
        $name = $row['nama_anggota'] ?? $row['member_name'] ?? $row['name'] ?? '';
        $category = $row['kategori'] ?? $row['category'] ?? '';
        $description = $row['deskripsi_singkat'] ?? $row['description'] ?? '';
        $rawDetail = $row['deskripsi_lengkap'] ?? $row['detail'] ?? $row['content'] ?? '';
        $detail = self::cleanDisplayDetail((string) $rawDetail);
        $images = self::resolveImageCollection($row);
        $image = $images[0] ?? '';
        $institution = $row['institusi_penyelenggara'] ?? $row['institution'] ?? '';

        return [
            'id' => (int) ($row['prestasi_id'] ?? $row['id'] ?? 0),
            'prestasi_id' => (int) ($row['prestasi_id'] ?? $row['id'] ?? 0),
            'slug' => $row['slug'] ?? '',
            'title' => $title,
            'name' => $name,
            'member_name' => $name,
            'campus' => $row['komisariat'] ?? $row['campus'] ?? '',
            'category' => $category,
            'year' => $row['tahun'] ?? $row['year'] ?? '',
            'description' => $description,
            'content' => $detail,
            'detail' => $detail,
            'image' => $image,
            'photo' => $image,
            'images' => $images,
            'institution' => $institution,
            'status' => $row['status'] ?? 'published',
            'meta_title' => $row['meta_title'] ?? '',
            'meta_keyword' => $row['meta_keyword'] ?? '',
            'meta_description' => $row['meta_description'] ?? '',
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
            'SELECT ' . $this->selectColumns() . ' FROM tbl_prestasi WHERE status = :status AND deleted_at IS NULL ORDER BY year DESC, created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':status', 'published', \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function countPublished(): int
    {
        if (!$this->db) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM tbl_prestasi WHERE status = :status AND deleted_at IS NULL'
        );
        $stmt->bindValue(':status', 'published', \PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countAll(?string $status = null): int
    {
        if (!$this->db) {
            return 0;
        }

        $sql = 'SELECT COUNT(*) FROM tbl_prestasi WHERE deleted_at IS NULL';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBySlugWithStatus($slug, true);
    }

    public function findAnyBySlug(string $slug): ?array
    {
        return $this->findBySlugWithStatus($slug, false);
    }

    private function findBySlugWithStatus(string $slug, bool $publishedOnly): ?array
    {
        if (!$this->db) {
            return null;
        }

        $statusSql = $publishedOnly ? 'AND status = :status' : '';
        $stmt = $this->db->prepare('SELECT ' . $this->selectColumns() . ' FROM tbl_prestasi WHERE slug = :slug ' . $statusSql . ' AND deleted_at IS NULL LIMIT 1');
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        if ($publishedOnly) {
            $stmt->bindValue(':status', 'published', \PDO::PARAM_STR);
        }
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
            'SELECT ' . $this->selectColumns() . ' FROM tbl_prestasi WHERE prestasi_id = :id AND deleted_at IS NULL LIMIT 1'
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
            'SELECT ' . $this->selectColumns() . ' FROM tbl_prestasi WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /** @param array{q?: string|null, category?: string|null, year?: string|null, status?: string|null} $filters */
    public function allForAdmin(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        $sql = 'SELECT ' . $this->selectColumns() . ' FROM tbl_prestasi WHERE deleted_at IS NULL';
        $params = [];
        $sql = $this->applyAdminFilters($sql, $params, $filters);
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /** @param array{q?: string|null, category?: string|null, year?: string|null, status?: string|null} $filters */
    public function countForAdmin(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        $sql = 'SELECT COUNT(*) FROM tbl_prestasi WHERE deleted_at IS NULL';
        $params = [];
        $sql = $this->applyAdminFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @param array<string, mixed> $params passed by reference */
    private function applyAdminFilters(string $sql, array &$params, array $filters): string
    {
        if (!empty($filters['q'])) {
            $sql .= ' AND (title LIKE :q OR member_name LIKE :q OR category LIKE :q OR institution LIKE :q OR description LIKE :q)';
            $params[':q'] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $filters['q']) . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['year'])) {
            $sql .= ' AND year = :year';
            $params[':year'] = $filters['year'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        return $sql;
    }

    public function create(array $data): int
    {
        if (!$this->db) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tbl_prestasi (title, slug, category, year, member_name, institution, description, detail, photo, status, meta_title, meta_keyword, meta_description, created_at) VALUES (:title, :slug, :category, :year, :name, :institution, :description, :content, :image, :status, :meta_title, :meta_keyword, :meta_description, NOW())'
        );
        $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':name' => $data['name'] ?? '',
            ':category' => $data['category'] ?? 'Prestasi',
            ':year' => $data['year'] ?? '',
            ':description' => $data['description'] ?? '',
            ':content' => $data['content'] ?? '',
            ':image' => $data['image'] ?? '',
            ':institution' => $data['institution'] ?? '',
            ':status' => $data['status'] ?? 'draft',
            ':meta_title' => $data['meta_title'] ?? null,
            ':meta_keyword' => $data['meta_keyword'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
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
            'title' => 'title',
            'slug' => 'slug',
            'name' => 'member_name',
            'category' => 'category',
            'year' => 'year',
            'description' => 'description',
            'content' => 'detail',
            'image' => 'photo',
            'institution' => 'institution',
            'status' => 'status',
            'meta_title' => 'meta_title',
            'meta_keyword' => 'meta_keyword',
            'meta_description' => 'meta_description',
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

    private static function resolveImageUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $driveId = self::extractDriveId($value);
        if ($driveId !== '') {
            return 'https://drive.google.com/thumbnail?id=' . rawurlencode($driveId) . '&sz=w1000';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return str_replace('/public/uploads/', '/uploads/', $value);
        }

        if (str_starts_with($value, '/public/uploads/')) {
            return str_replace('/public/uploads/', '/uploads/', $value);
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        return '/uploads/prestasi/' . ltrim($value, '/');
    }

    private static function cleanDisplayDetail(string $detail): string
    {
        return trim((string) preg_replace('/\n?Dokumentasi\s*:\s*[^\n<]*/iu', '', $detail));
    }

    private function selectColumns(): string
    {
        return 'tbl_prestasi.*, (SELECT s.payload_json FROM tbl_prestasi_submission s WHERE s.prestasi_id = tbl_prestasi.prestasi_id ORDER BY s.created_at DESC, s.submission_id DESC LIMIT 1) AS submission_payload_json';
    }

    /** @return string[] */
    private static function resolveImageCollection(array $row): array
    {
        $candidates = [];

        foreach (['foto', 'foto_prestasi', 'photo', 'image', 'certificate_photo'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $candidates[] = $value;
            }
        }

        foreach (self::extractDocumentationLinks((string) ($row['deskripsi_lengkap'] ?? $row['detail'] ?? $row['content'] ?? '')) as $link) {
            $candidates[] = $link;
        }

        foreach (self::extractSubmissionPhotoLinks((string) ($row['submission_payload_json'] ?? '')) as $link) {
            $candidates[] = $link;
        }

        $resolved = [];
        foreach ($candidates as $candidate) {
            $url = self::resolveImageUrl($candidate);
            if ($url !== '' && !in_array($url, $resolved, true)) {
                $resolved[] = $url;
            }
        }

        return $resolved;
    }

    /** @return string[] */
    private static function extractDocumentationLinks(string $detail): array
    {
        if ($detail === '') {
            return [];
        }

        $links = [];
        if (preg_match('/Dokumentasi\s*:\s*(.+)/iu', $detail, $matches)) {
            $segment = trim($matches[1]);
            foreach (preg_split('/\s*,\s*/', $segment) ?: [] as $part) {
                $candidate = trim($part);
                if ($candidate !== '') {
                    $links[] = $candidate;
                }
            }
        }

        preg_match_all('#https?://[^\s<>"]+#i', $detail, $allMatches);
        foreach ($allMatches[0] ?? [] as $match) {
            $candidate = rtrim($match, '.,)');
            if (self::looksLikeImageSource($candidate)) {
                $links[] = $candidate;
            }
        }

        return array_values(array_unique(array_filter($links, static fn(string $value): bool => $value !== '')));
    }

    /** @return string[] */
    private static function extractSubmissionPhotoLinks(string $payloadJson): array
    {
        if ($payloadJson === '') {
            return [];
        }

        $decoded = json_decode($payloadJson, true);
        if (!is_array($decoded) || !isset($decoded['photos']) || !is_array($decoded['photos'])) {
            return [];
        }

        $links = [];
        foreach ($decoded['photos'] as $photo) {
            if (!is_array($photo)) {
                continue;
            }

            $url = trim((string) ($photo['url'] ?? ''));
            if ($url !== '') {
                $links[] = $url;
            }
        }

        return array_values(array_unique($links));
    }

    private static function extractDriveId(string $value): string
    {
        if (!preg_match('/(?:drive\.google\.com|docs\.google\.com)/i', $value)) {
            return '';
        }

        if (preg_match('/[?&]id=([-\w]{10,})/i', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('#/file/d/([-\w]{10,})#i', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('/[-\w]{25,}/', $value, $matches)) {
            return $matches[0];
        }

        return '';
    }

    private static function looksLikeImageSource(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if (self::extractDriveId($value) !== '') {
            return true;
        }

        if (preg_match('/\.(?:jpg|jpeg|png|webp|gif)(?:\?.*)?$/i', $value)) {
            return true;
        }

        return str_starts_with($value, '/uploads/');
    }
}
