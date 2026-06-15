<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class PresensiSubmission
{
    public function __construct(private ?PDO $db = null) {}

    /** @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        $id = (int) ($row['submission_id'] ?? $row['id'] ?? 0);
        $photo = (string) ($row['photo_path'] ?? $row['photo'] ?? '');

        return [
            'id' => $id,
            'submission_id' => $id,
            'presensi_event_id' => (int) ($row['presensi_event_id'] ?? 0),
            'team_id' => (int) ($row['team_id'] ?? 0),
            'member_name' => (string) ($row['member_name'] ?? $row['name'] ?? ''),
            'member_role' => (string) ($row['member_role'] ?? $row['designation'] ?? ''),
            'role' => (string) ($row['role'] ?? ''),
            'photo_path' => $photo,
            'photo_url' => self::resolvePhotoUrl($photo),
            'status' => (string) ($row['status'] ?? 'pending'),
            'approved_by' => isset($row['approved_by']) ? (int) $row['approved_by'] : null,
            'approved_at' => $row['approved_at'] ?? null,
            'ip_address' => (string) ($row['ip_address'] ?? ''),
            'user_agent' => (string) ($row['user_agent'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ?int
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO tbl_presensi_submission (presensi_event_id, team_id, role, photo_path, status, ip_address, user_agent, created_at) VALUES (:event_id, :team_id, :role, :photo_path, :status, :ip_address, :user_agent, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                ':event_id' => (int) ($data['presensi_event_id'] ?? 0),
                ':team_id' => (int) ($data['team_id'] ?? 0),
                ':role' => strip_tags(mb_substr(trim((string) ($data['role'] ?? '')), 0, 120)),
                ':photo_path' => strip_tags(mb_substr(trim((string) ($data['photo_path'] ?? '')), 0, 255)),
                ':status' => 'pending',
                ':ip_address' => mb_substr(trim((string) ($data['ip_address'] ?? '')), 0, 45) ?: null,
                ':user_agent' => mb_substr(trim((string) ($data['user_agent'] ?? '')), 0, 255) ?: null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Throwable $error) {
            if ($error instanceof \PDOException && ($error->errorInfo[1] ?? null) === 1062) {
                return null;
            }
            if ($error instanceof \PDOException && ($error->getCode() === '23000')) {
                return null;
            }
            throw $error;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function forEvent(int $eventId): array
    {
        if (!$this->db || $eventId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE ps.presensi_event_id = :event_id ORDER BY ps.created_at DESC, ps.submission_id DESC');
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(int $id): ?array
    {
        if (!$this->db || $id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE ps.submission_id = :id LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::mapRow($row) : null;
    }

    public function approve(int $id, int $userId): bool
    {
        if (!$this->db || $id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE tbl_presensi_submission SET status = 'approved', approved_by = :approved_by, approved_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE submission_id = :id");
        $stmt->execute([':id' => $id, ':approved_by' => $userId > 0 ? $userId : null]);

        return $stmt->rowCount() > 0;
    }

    /** @param array<string, mixed> $data */
    public function createManualApproved(array $data): ?int
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tbl_presensi_submission
                    (presensi_event_id, team_id, role, photo_path, status, approved_by, approved_at, ip_address, user_agent, created_at, updated_at)
                 VALUES
                    (:event_id, :team_id, :role, '', 'approved', :approved_by, CURRENT_TIMESTAMP, :ip_address, :user_agent, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            $stmt->execute([
                ':event_id' => (int) ($data['presensi_event_id'] ?? 0),
                ':team_id' => (int) ($data['team_id'] ?? 0),
                ':role' => strip_tags(mb_substr(trim((string) ($data['role'] ?? '')), 0, 120)),
                ':approved_by' => !empty($data['approved_by']) ? (int) $data['approved_by'] : null,
                ':ip_address' => mb_substr(trim((string) ($data['ip_address'] ?? '')), 0, 45) ?: null,
                ':user_agent' => mb_substr(trim((string) ($data['user_agent'] ?? '')), 0, 255) ?: null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Throwable $error) {
            if ($error instanceof \PDOException && (($error->errorInfo[1] ?? null) === 1062 || $error->getCode() === '23000')) {
                return null;
            }
            throw $error;
        }
    }

    public function existsForEventMember(int $eventId, int $teamId): bool
    {
        if (!$this->db || $eventId <= 0 || $teamId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM tbl_presensi_submission WHERE presensi_event_id = :event_id AND team_id = :team_id LIMIT 1');
        $stmt->execute([':event_id' => $eventId, ':team_id' => $teamId]);

        return (bool) $stmt->fetchColumn();
    }

    private function baseSelect(): string
    {
        return 'SELECT ps.*, t.name AS member_name, t.designation AS member_role
            FROM tbl_presensi_submission ps
            LEFT JOIN teams t ON t.id = ps.team_id';
    }

    private static function resolvePhotoUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return str_replace('/public/uploads/', '/uploads/', $value);
        }

        return '/uploads/presensi/' . ltrim($value, '/');
    }
}
