<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class GenBIPoint
{
    public function __construct(private ?PDO $db = null) {}

    /** @param array<string, mixed> $filters @return array<int, array<string, mixed>> */
    public function membersWithPoints(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        [$where, $params] = $this->memberWhere($filters);
        $stmt = $this->db->prepare(
            'SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
             FROM teams t
             LEFT JOIN divisis d ON d.id = t.divisi_id
             LEFT JOIN komsats k ON k.id = t.komsat_id
             WHERE ' . $where . '
             ORDER BY t.tahun DESC, t.name ASC, t.id ASC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map([TeamMember::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        $totals = $this->pointTotalsForTeamIds(array_map(static fn(array $item): int => (int) ($item['id'] ?? 0), $items));

        return array_map(static function (array $item) use ($totals): array {
            $id = (int) ($item['id'] ?? 0);
            $point = $totals[$id] ?? ['presensi_points' => 0, 'manual_points' => 0, 'total_points' => 0];

            return array_merge($item, $point);
        }, $items);
    }

    /** @param array<string, mixed> $filters */
    public function countMembers(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        [$where, $params] = $this->memberWhere($filters);
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM teams t
             LEFT JOIN divisis d ON d.id = t.divisi_id
             LEFT JOIN komsats k ON k.id = t.komsat_id
             WHERE ' . $where
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return array<string, mixed>|null */
    public function memberWithPoints(int $teamId): ?array
    {
        if (!$this->db || $teamId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
             FROM teams t
             LEFT JOIN divisis d ON d.id = t.divisi_id
             LEFT JOIN komsats k ON k.id = t.komsat_id
             WHERE t.id = :id AND t.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->bindValue(':id', $teamId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $member = TeamMember::mapRow($row);
        $totals = $this->pointTotalsForTeamIds([$teamId]);

        return array_merge($member, $totals[$teamId] ?? ['presensi_points' => 0, 'manual_points' => 0, 'total_points' => 0]);
    }

    /** @param array<int, int> $teamIds @return array<int, array{presensi_points: int, manual_points: int, total_points: int}> */
    public function pointTotalsForTeamIds(array $teamIds): array
    {
        $teamIds = array_values(array_unique(array_filter(array_map('intval', $teamIds), static fn(int $id): bool => $id > 0)));
        $totals = [];
        foreach ($teamIds as $id) {
            $totals[$id] = ['presensi_points' => 0, 'manual_points' => 0, 'total_points' => 0];
        }
        if (!$this->db || $teamIds === []) {
            return $totals;
        }

        foreach ($this->presensiPointTotals($teamIds) as $teamId => $points) {
            $totals[$teamId]['presensi_points'] = $points;
        }
        foreach ($this->manualPointTotals($teamIds) as $teamId => $points) {
            $totals[$teamId]['manual_points'] = $points;
        }
        foreach ($totals as $teamId => $point) {
            $totals[$teamId]['total_points'] = (int) $point['presensi_points'] + (int) $point['manual_points'];
        }

        return $totals;
    }

    /** @param array<string, mixed> $filters @return array<int, array<string, mixed>> */
    public function activities(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            [$where, $params] = $this->activityWhere($filters);
            $stmt = $this->db->prepare(
                'SELECT a.*, t.*, t.name AS member_name, t.designation AS member_role, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM tbl_genbi_point_activity a
                 INNER JOIN teams t ON t.id = a.team_id
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE ' . $where . '
                 ORDER BY COALESCE(a.activity_date, DATE(a.created_at)) DESC, a.activity_id DESC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
            $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
            $stmt->execute();

            return array_map([$this, 'mapActivityRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $filters */
    public function countActivities(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        try {
            [$where, $params] = $this->activityWhere($filters);
            $stmt = $this->db->prepare(
                'SELECT COUNT(*)
                 FROM tbl_genbi_point_activity a
                 INNER JOIN teams t ON t.id = a.team_id
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

    public function findActivity(int $id): ?array
    {
        if (!$this->db || $id <= 0) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT a.*, t.*, t.name AS member_name, t.designation AS member_role, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM tbl_genbi_point_activity a
                 INNER JOIN teams t ON t.id = a.team_id
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE a.activity_id = :id AND a.deleted_at IS NULL
                 LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? $this->mapActivityRow($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function manualActivitiesForTeam(int $teamId, int $limit = 100): array
    {
        if (!$this->db || $teamId <= 0) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT a.*, t.*, t.name AS member_name, t.designation AS member_role, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
                 FROM tbl_genbi_point_activity a
                 INNER JOIN teams t ON t.id = a.team_id
                 LEFT JOIN divisis d ON d.id = t.divisi_id
                 LEFT JOIN komsats k ON k.id = t.komsat_id
                 WHERE a.team_id = :team_id AND a.deleted_at IS NULL AND t.deleted_at IS NULL
                 ORDER BY COALESCE(a.activity_date, DATE(a.created_at)) DESC, a.activity_id DESC
                 LIMIT :limit'
            );
            $stmt->bindValue(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', max(1, min(500, $limit)), PDO::PARAM_INT);
            $stmt->execute();

            return array_map([$this, 'mapActivityRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function presensiActivitiesForTeam(int $teamId, int $limit = 100): array
    {
        if (!$this->db || $teamId <= 0) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT ps.*, e.event_name, e.location, e.roles_json
                 FROM tbl_presensi_submission ps
                 INNER JOIN tbl_presensi_event e ON e.presensi_event_id = ps.presensi_event_id
                 WHERE ps.team_id = :team_id AND e.deleted_at IS NULL
                 ORDER BY ps.created_at DESC, ps.submission_id DESC
                 LIMIT :limit'
            );
            $stmt->bindValue(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', max(1, min(500, $limit)), PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static function (array $row): array {
                $status = strtolower((string) ($row['status'] ?? 'pending'));
                $role = (string) ($row['role'] ?? '');
                $score = $status === 'approved'
                    ? PresensiEvent::roleScore(['role_options' => $row['roles_json'] ?? ''], $role)
                    : 0;

                return [
                    'id' => (int) ($row['submission_id'] ?? 0),
                    'event_id' => (int) ($row['presensi_event_id'] ?? 0),
                    'event_name' => (string) ($row['event_name'] ?? ''),
                    'location' => (string) ($row['location'] ?? ''),
                    'role' => $role,
                    'points' => $score,
                    'status' => $status,
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'approved_at' => (string) ($row['approved_at'] ?? ''),
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $data */
    public function createActivity(array $data): int
    {
        if (!$this->db) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tbl_genbi_point_activity (team_id, activity_name, points, activity_date, created_by, created_at)
             VALUES (:team_id, :activity_name, :points, :activity_date, :created_by, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            ':team_id' => (int) ($data['team_id'] ?? 0),
            ':activity_name' => (string) ($data['activity_name'] ?? ''),
            ':points' => (int) ($data['points'] ?? 0),
            ':activity_date' => $data['activity_date'] ?: null,
            ':created_by' => !empty($data['created_by']) ? (int) $data['created_by'] : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function updateActivity(int $id, array $data): bool
    {
        if (!$this->db || $id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE tbl_genbi_point_activity
             SET team_id = :team_id,
                 activity_name = :activity_name,
                 points = :points,
                 activity_date = :activity_date,
                 updated_by = :updated_by,
                 updated_at = CURRENT_TIMESTAMP
             WHERE activity_id = :id AND deleted_at IS NULL'
        );

        return $stmt->execute([
            ':id' => $id,
            ':team_id' => (int) ($data['team_id'] ?? 0),
            ':activity_name' => (string) ($data['activity_name'] ?? ''),
            ':points' => (int) ($data['points'] ?? 0),
            ':activity_date' => $data['activity_date'] ?: null,
            ':updated_by' => !empty($data['updated_by']) ? (int) $data['updated_by'] : null,
        ]);
    }

    public function teamExists(int $teamId): bool
    {
        if (!$this->db || $teamId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM teams WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->bindValue(':id', $teamId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /** @param array<string, mixed> $filters @return array{0: string, 1: array<string, mixed>} */
    private function memberWhere(array $filters): array
    {
        $where = ['t.deleted_at IS NULL'];
        $params = [];
        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $search = '%' . $this->escapeLike(trim((string) $filters['q'])) . '%';
            $where[] = '(t.name LIKE :q_name OR t.designation LIKE :q_designation OR d.nama LIKE :q_division OR COALESCE(k.nama, t.komsat) LIKE :q_campus)';
            $params[':q_name'] = $search;
            $params[':q_designation'] = $search;
            $params[':q_division'] = $search;
            $params[':q_campus'] = $search;
        }

        return [implode(' AND ', $where), $params];
    }

    /** @param array<string, mixed> $filters @return array{0: string, 1: array<string, mixed>} */
    private function activityWhere(array $filters): array
    {
        $where = ['a.deleted_at IS NULL', 't.deleted_at IS NULL'];
        $params = [];
        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $search = '%' . $this->escapeLike(trim((string) $filters['q'])) . '%';
            $where[] = '(a.activity_name LIKE :q_activity OR t.name LIKE :q_name OR d.nama LIKE :q_division OR COALESCE(k.nama, t.komsat) LIKE :q_campus)';
            $params[':q_activity'] = $search;
            $params[':q_name'] = $search;
            $params[':q_division'] = $search;
            $params[':q_campus'] = $search;
        }

        return [implode(' AND ', $where), $params];
    }

    /** @param int[] $teamIds @return array<int, int> */
    private function presensiPointTotals(array $teamIds): array
    {
        try {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $stmt = $this->db->prepare(
                "SELECT ps.team_id, ps.role, e.roles_json
                 FROM tbl_presensi_submission ps
                 INNER JOIN tbl_presensi_event e ON e.presensi_event_id = ps.presensi_event_id
                 WHERE ps.status = 'approved'
                   AND e.deleted_at IS NULL
                   AND ps.team_id IN ($placeholders)"
            );
            $stmt->execute($teamIds);
            $totals = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $teamId = (int) ($row['team_id'] ?? 0);
                $score = PresensiEvent::roleScore(['role_options' => $row['roles_json'] ?? ''], (string) ($row['role'] ?? ''));
                $totals[$teamId] = ($totals[$teamId] ?? 0) + $score;
            }

            return $totals;
        } catch (Throwable) {
            return [];
        }
    }

    /** @param int[] $teamIds @return array<int, int> */
    private function manualPointTotals(array $teamIds): array
    {
        try {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $stmt = $this->db->prepare(
                "SELECT team_id, COALESCE(SUM(points), 0) AS total_points
                 FROM tbl_genbi_point_activity
                 WHERE deleted_at IS NULL AND team_id IN ($placeholders)
                 GROUP BY team_id"
            );
            $stmt->execute($teamIds);
            $totals = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $totals[(int) ($row['team_id'] ?? 0)] = (int) ($row['total_points'] ?? 0);
            }

            return $totals;
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function mapActivityRow(array $row): array
    {
        $member = TeamMember::mapRow($row);
        $id = (int) ($row['activity_id'] ?? $row['id'] ?? 0);

        return [
            'id' => $id,
            'activity_id' => $id,
            'team_id' => (int) ($row['team_id'] ?? 0),
            'member_name' => (string) ($row['member_name'] ?? $member['name'] ?? ''),
            'member' => $member,
            'activity_name' => (string) ($row['activity_name'] ?? ''),
            'points' => (int) ($row['points'] ?? 0),
            'activity_date' => (string) ($row['activity_date'] ?? ''),
            'created_by' => isset($row['created_by']) ? (int) $row['created_by'] : null,
            'updated_by' => isset($row['updated_by']) ? (int) $row['updated_by'] : null,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
