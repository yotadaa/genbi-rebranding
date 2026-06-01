<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

class TeamMember
{
    public function __construct(private ?PDO $db = null) {}

    /** Map a teams row to the public frontend shape. */
    public static function mapRow(array $row): array
    {
        $name = (string) ($row['name'] ?? '');
        $role = (string) ($row['designation'] ?? $row['jabatan_wilayah'] ?? $row['jabatan_komsat'] ?? 'Anggota');
        $detail = (string) ($row['detail'] ?? '');
        $photo = (string) ($row['photo'] ?? '');
        $division = (string) ($row['division_name'] ?? 'Belum ada divisi');
        $commission = (string) ($row['commission_name'] ?? $row['komsat'] ?? 'GenBI Jambi');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => $name,
            'role' => $role,
            'designation' => $role,
            'division_id' => isset($row['division_id']) ? (int) $row['division_id'] : (int) ($row['divisi_id'] ?? 0),
            'division' => $division,
            'divisi_id' => isset($row['divisi_id']) ? (int) $row['divisi_id'] : (int) ($row['division_id'] ?? 0),
            'campus' => $commission,
            'commission' => $commission,
            'komsat_id' => isset($row['komsat_id']) ? (int) $row['komsat_id'] : 0,
            'year' => (string) ($row['tahun'] ?? ''),
            'tahun' => (string) ($row['tahun'] ?? ''),
            'status' => 'Pengurus',
            'bio' => trim(strip_tags($detail)) ?: $role,
            'detail' => $detail,
            'photo' => self::resolveImageUrl($photo),
            'photo_raw' => $photo,
            'show_on_home' => (int) ($row['show_on_home'] ?? 0) === 1,
            'home_sort_order' => (int) ($row['home_sort_order'] ?? 0),
            'email' => (string) ($row['email'] ?? ''),
            'instagram' => (string) ($row['instagram'] ?? ''),
            'facebook' => (string) ($row['facebook'] ?? ''),
            'linkedin' => (string) ($row['linkedin'] ?? ''),
            'phone' => (string) ($row['phone'] ?? ''),
        ];
    }

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function allActive(array $filters = [], int $limit = 200, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            [$where, $params] = $this->buildPublicFilterSql($filters);
            $stmt = $this->db->prepare(
                "SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE " . $where . "
                 ORDER BY
                    CASE
                      WHEN LOWER(COALESCE(d.nama, '')) LIKE '%badan pengurus inti%' OR LOWER(COALESCE(d.nama, '')) LIKE '%bpi%' THEN 0
                      ELSE 1
                    END ASC,
                    t.tahun DESC, COALESCE(k.nama, t.komsat) ASC, d.nama ASC, t.id ASC
                 LIMIT :limit OFFSET :offset"
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, string|null> $filters */
    public function countPublic(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        try {
            [$where, $params] = $this->buildPublicFilterSql($filters);
            $stmt = $this->db->prepare(
                'SELECT COUNT(*)
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE ' . $where
            );
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    public function findById(int $id): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                  WHERE t.id = :id AND t.deleted_at IS NULL LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? self::mapRow($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function bpiCore(?int $limit = 10): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $yearStmt = $this->db->query('SELECT MAX(CAST(tahun AS UNSIGNED)) FROM teams WHERE deleted_at IS NULL');
            $latestYear = (int) $yearStmt->fetchColumn();
            if ($latestYear < 1) {
                return [];
            }

            $limitSql = $limit !== null && $limit > 0 ? ' LIMIT :limit' : '';

            $manualStmt = $this->db->prepare(
                "SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE t.deleted_at IS NULL
                   AND CAST(t.tahun AS UNSIGNED) = :year
                   AND t.show_on_home = 1
                  ORDER BY
                    CASE
                      WHEN LOWER(COALESCE(d.nama, '')) LIKE '%badan pengurus inti%' OR LOWER(COALESCE(d.nama, '')) LIKE '%bpi%' THEN 0
                      ELSE 1
                    END ASC,
                    t.home_sort_order ASC, t.id ASC" . $limitSql
            );
            $manualStmt->bindValue(':year', $latestYear, PDO::PARAM_INT);
            if ($limitSql !== '') {
                $manualStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $manualStmt->execute();
            $manualRows = $manualStmt->fetchAll(PDO::FETCH_ASSOC);
            if ($manualRows !== []) {
                return array_map([self::class, 'mapRow'], $manualRows);
            }

            $stmt = $this->db->prepare(
                "SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE t.deleted_at IS NULL
                   AND CAST(t.tahun AS UNSIGNED) = :year
                   AND LOWER(COALESCE(d.nama, '')) LIKE '%badan pengurus inti%'
                  ORDER BY t.home_sort_order ASC, t.id ASC" . $limitSql
            );
            $stmt->bindValue(':year', $latestYear, PDO::PARAM_INT);
            if ($limitSql !== '') {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array{divisions: array<int, string>, campuses: array<int, string>, years: array<int, string>} */
    public function filterOptions(): array
    {
        if (!$this->db) {
            return ['divisions' => [], 'campuses' => [], 'years' => []];
        }

        try {
            return [
                'divisions' => $this->db->query('SELECT nama FROM divisis ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN),
                'campuses' => $this->db->query('SELECT nama FROM komsats ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN),
                'years' => array_map('strval', $this->db->query('SELECT DISTINCT tahun FROM teams WHERE deleted_at IS NULL ORDER BY tahun DESC')->fetchAll(PDO::FETCH_COLUMN)),
            ];
        } catch (Throwable) {
            return ['divisions' => [], 'campuses' => [], 'years' => []];
        }
    }

    /** @return array{divisions: array<int, array<string, mixed>>, commissions: array<int, array<string, mixed>>} */
    public function formOptions(): array
    {
        if (!$this->db) {
            return ['divisions' => [], 'commissions' => []];
        }

        try {
            return [
                'divisions' => $this->db->query('SELECT id, nama, komsat_id FROM divisis ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC),
                'commissions' => $this->db->query('SELECT id, nama FROM komsats ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Throwable) {
            return ['divisions' => [], 'commissions' => []];
        }
    }

    /** @param array<string, string|null> $filters @return array{0: string, 1: array<string, string|int>} */
    private function buildPublicFilterSql(array $filters): array
    {
        $where = ['t.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['division'])) {
            $where[] = 'd.nama = :division';
            $params['division'] = (string) $filters['division'];
        }

        if (!empty($filters['campus'])) {
            $where[] = 'COALESCE(k.nama, t.komsat) = :campus';
            $params['campus'] = (string) $filters['campus'];
        }

        if (!empty($filters['year'])) {
            $where[] = 't.tahun = :year';
            $params['year'] = (int) $filters['year'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(t.name LIKE :q_name OR t.designation LIKE :q_designation OR d.nama LIKE :q_division OR COALESCE(k.nama, t.komsat) LIKE :q_campus)';
            $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $filters['q']) . '%';
            $params['q_name'] = $search;
            $params['q_designation'] = $search;
            $params['q_division'] = $search;
            $params['q_campus'] = $search;
        }

        return [implode(' AND ', $where), $params];
    }

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function allForAdmin(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        if (!$this->db) return [];

        try {
            [$where, $params] = $this->buildPublicFilterSql($filters);
            $stmt = $this->db->prepare(
                'SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE ' . $where . '
                 ORDER BY t.show_on_home DESC, t.tahun DESC, t.id DESC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    public function create(array $data): ?int
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO teams (name, designation, photo, detail, instagram, facebook, linkedin, phone, email, website, komsat_id, divisi_id, komsat, tahun, show_on_home, home_sort_order, created_at, updated_at)
                 VALUES (:name, :designation, :photo, :detail, :instagram, :facebook, :linkedin, :phone, :email, :website, :komsat_id, :divisi_id, :komsat, :tahun, :show_on_home, :home_sort_order, NOW(), NOW())'
            );
            $stmt->execute($this->bindablePayload($data));

            return (int) $this->db->lastInsertId();
        } catch (Throwable) {
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->db || $id <= 0) return false;

        try {
            $payload = $this->bindablePayload($data);
            $payload['id'] = $id;
            $stmt = $this->db->prepare(
                'UPDATE teams SET name = :name, designation = :designation, photo = :photo, detail = :detail, instagram = :instagram, facebook = :facebook, linkedin = :linkedin, phone = :phone, email = :email, website = :website, komsat_id = :komsat_id, divisi_id = :divisi_id, komsat = :komsat, tahun = :tahun, show_on_home = :show_on_home, home_sort_order = :home_sort_order, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute($payload);

            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        if (!$this->db || $id <= 0) return false;

        try {
            $stmt = $this->db->prepare('UPDATE teams SET deleted_at = NOW(), show_on_home = 0, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            $stmt->execute(['id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<int, int> $ids */
    public function bulkDelete(array $ids): int
    {
        return $this->bulkSetDeleted($ids);
    }

    /** @param array<int, int> $ids */
    public function setHomeVisibility(array $ids, bool $visible): int
    {
        if (!$this->db || empty($ids)) return 0;

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if (empty($ids)) return 0;

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE teams SET show_on_home = ?, updated_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
            $stmt->execute(array_merge([$visible ? 1 : 0], $ids));

            return $stmt->rowCount();
        } catch (Throwable) {
            return 0;
        }
    }

    /** @param array<int, int> $ids */
    private function bulkSetDeleted(array $ids): int
    {
        if (!$this->db || empty($ids)) return 0;

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if (empty($ids)) return 0;

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE teams SET deleted_at = NOW(), show_on_home = 0, updated_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
            $stmt->execute($ids);

            return $stmt->rowCount();
        } catch (Throwable) {
            return 0;
        }
    }

    /** @return array<string, mixed> */
    private function bindablePayload(array $data): array
    {
        return [
            'name' => (string) ($data['name'] ?? ''),
            'designation' => (string) ($data['designation'] ?? $data['role'] ?? ''),
            'photo' => (string) ($data['photo'] ?? ''),
            'detail' => (string) ($data['detail'] ?? $data['bio'] ?? ''),
            'instagram' => (string) ($data['instagram'] ?? ''),
            'facebook' => (string) ($data['facebook'] ?? ''),
            'linkedin' => (string) ($data['linkedin'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'website' => (string) ($data['website'] ?? ''),
            'komsat_id' => !empty($data['komsat_id']) ? (int) $data['komsat_id'] : null,
            'divisi_id' => !empty($data['divisi_id']) ? (int) $data['divisi_id'] : null,
            'komsat' => (string) ($data['komsat'] ?? $data['commission'] ?? ''),
            'tahun' => (int) ($data['tahun'] ?? $data['year'] ?? date('Y')),
            'show_on_home' => !empty($data['show_on_home']) ? 1 : 0,
            'home_sort_order' => (int) ($data['home_sort_order'] ?? 0),
        ];
    }

    private static function resolveImageUrl(string $filename): string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return '';
        }
        if (str_starts_with($filename, 'http') || str_starts_with($filename, '/')) {
            return $filename;
        }

        return '/uploads/' . ltrim($filename, '/');
    }
}
