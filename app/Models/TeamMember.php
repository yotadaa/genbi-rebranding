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
        $division = (string) ($row['division_name'] ?? $row['divisi_wilayah'] ?? $row['divisi_komsat'] ?? 'Umum');
        $commission = (string) ($row['commission_name'] ?? $row['komsat'] ?? 'GenBI Jambi');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => $name,
            'role' => $role,
            'division' => $division,
            'campus' => $commission,
            'commission' => $commission,
            'year' => (string) ($row['tahun'] ?? ''),
            'status' => 'Pengurus',
            'bio' => trim(strip_tags($detail)) ?: $role,
            'photo' => self::resolveImageUrl($photo),
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
                'SELECT t.*, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE ' . $where . '
                 ORDER BY t.tahun DESC, COALESCE(k.nama, t.komsat) ASC, COALESCE(d.nama, t.divisi_wilayah, t.divisi_komsat) ASC, t.id ASC
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
                'SELECT t.*, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE t.id = :id LIMIT 1'
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
    public function bpiCore(int $limit = 10): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT t.*, d.nama AS division_name, k.nama AS commission_name
                 FROM teams t
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE LOWER(COALESCE(d.nama, t.divisi_wilayah, t.divisi_komsat, t.designation, '')) LIKE '%badan pengurus inti%'
                    OR LOWER(COALESCE(t.designation, '')) REGEXP 'ketua|sekretaris|bendahara|koordinator'
                 ORDER BY t.tahun DESC, t.id ASC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
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
                'years' => array_map('strval', $this->db->query('SELECT DISTINCT tahun FROM teams ORDER BY tahun DESC')->fetchAll(PDO::FETCH_COLUMN)),
            ];
        } catch (Throwable) {
            return ['divisions' => [], 'campuses' => [], 'years' => []];
        }
    }

    /** @param array<string, string|null> $filters @return array{0: string, 1: array<string, string|int>} */
    private function buildPublicFilterSql(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['division'])) {
            $where[] = 'COALESCE(d.nama, t.divisi_wilayah, t.divisi_komsat) = :division';
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
            $where[] = '(t.name LIKE :q OR t.designation LIKE :q OR COALESCE(d.nama, t.divisi_wilayah, t.divisi_komsat) LIKE :q OR COALESCE(k.nama, t.komsat) LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        return [implode(' AND ', $where), $params];
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

        return 'https://genbijambi.com/public/uploads/' . $filename;
    }
}
