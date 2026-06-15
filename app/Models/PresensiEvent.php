<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class PresensiEvent
{
    private const STATUSES = ['draft', 'open', 'closed', 'archived'];

    public function __construct(private ?PDO $db = null) {}

    /** @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        $token = trim((string) ($row['public_token'] ?? ''));
        $id = (int) ($row['presensi_event_id'] ?? $row['id'] ?? 0);
        $roleOptions = self::normalizeRoleOptions($row['role_options'] ?? $row['roles'] ?? $row['roles_json'] ?? []);
        $roles = array_column($roleOptions, 'name');

        return [
            'id' => $id,
            'presensi_event_id' => $id,
            'slug' => (string) ($row['slug'] ?? ''),
            'public_token' => $token,
            'public_token_hash' => (string) ($row['public_token_hash'] ?? ''),
            'public_url' => $token !== '' ? '/presensi/' . rawurlencode($token) : (string) ($row['public_url'] ?? ''),
            'event_name' => (string) ($row['event_name'] ?? $row['name'] ?? ''),
            'name' => (string) ($row['event_name'] ?? $row['name'] ?? ''),
            'location' => (string) ($row['location'] ?? ''),
            'roles' => $roles,
            'role_options' => $roleOptions,
            'roles_json' => json_encode($roleOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'members' => is_array($row['members'] ?? null) ? $row['members'] : [],
            'member_count' => (int) ($row['member_count'] ?? 0),
            'submission_count' => (int) ($row['submission_count'] ?? 0),
            'pending_count' => (int) ($row['pending_count'] ?? 0),
            'approved_count' => (int) ($row['approved_count'] ?? 0),
            'status' => (string) ($row['status'] ?? 'open'),
            'created_by' => isset($row['created_by']) ? (int) $row['created_by'] : null,
            'updated_by' => isset($row['updated_by']) ? (int) $row['updated_by'] : null,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'deleted_at' => $row['deleted_at'] ?? null,
        ];
    }

    /** @return string[] */
    public static function normalizeRoles(mixed $roles): array
    {
        return array_column(self::normalizeRoleOptions($roles), 'name');
    }

    /** @return array<int, array{name: string, score: int}> */
    public static function normalizeRoleOptions(mixed $roles): array
    {
        if (is_string($roles)) {
            $decoded = json_decode($roles, true);
            $roles = is_array($decoded) ? $decoded : preg_split('/[\r\n,]+/', $roles);
        }

        if (!is_array($roles)) {
            return [];
        }

        $normalized = [];
        $seen = [];
        foreach ($roles as $role) {
            $score = 0;
            if (is_array($role)) {
                $value = (string) ($role['name'] ?? $role['label'] ?? $role['role'] ?? $role['title'] ?? '');
                $score = self::sanitizeRoleScore($role['score'] ?? $role['points'] ?? $role['skor'] ?? 0);
            } else {
                $value = (string) $role;
            }

            $value = strip_tags(mb_substr(trim($value), 0, 120));
            $key = mb_strtolower($value);
            if ($value !== '' && !isset($seen[$key])) {
                $normalized[] = ['name' => $value, 'score' => $score];
                $seen[$key] = true;
            }
            if (count($normalized) >= 20) {
                break;
            }
        }

        return $normalized;
    }

    public static function roleScore(array $event, string $role): int
    {
        $role = mb_strtolower(trim($role));
        if ($role === '') {
            return 0;
        }

        foreach (self::normalizeRoleOptions($event['role_options'] ?? $event['roles'] ?? []) as $option) {
            if (mb_strtolower($option['name']) === $role) {
                return (int) $option['score'];
            }
        }

        return 0;
    }

    /** @param array<string, mixed> $filters @return array<int, array<string, mixed>> */
    public function allForAdmin(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        [$where, $params] = $this->adminWhere($filters);
        $sql = $this->baseSelect() . ' WHERE ' . $where . ' ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @param array<string, mixed> $filters */
    public function countForAdmin(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        [$where, $params] = $this->adminWhere($filters);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tbl_presensi_event e WHERE ' . $where);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        if (!$this->db || $id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE e.presensi_event_id = :id AND e.deleted_at IS NULL LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $item = self::mapRow($row);
        $item['members'] = $this->membersForEvent($id);
        $item['member_count'] = count($item['members']);

        return $item;
    }

    public function findByPublicToken(string $token, bool $openOnly = false): ?array
    {
        if (!$this->db) {
            return null;
        }

        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $sql = $this->baseSelect() . ' WHERE e.public_token_hash = :hash AND e.deleted_at IS NULL';
        if ($openOnly) {
            $sql .= " AND e.status = 'open'";
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hash', hash('sha256', $token), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $item = self::mapRow($row);
        $item['members'] = $this->membersForEvent((int) $item['id']);
        $item['member_count'] = count($item['members']);

        return $item;
    }

    public function findAnyBySlug(string $slug): ?array
    {
        if (!$this->db || trim($slug) === '') {
            return null;
        }

        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE e.slug = :slug LIMIT 1');
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::mapRow($row) : null;
    }

    /** @param array<string, mixed> $data @return array{id: int, public_token: string, public_token_hash: string, public_url: string} */
    public function create(array $data): array
    {
        if (!$this->db) {
            return ['id' => 0, 'public_token' => '', 'public_token_hash' => '', 'public_url' => ''];
        }

        $eventName = strip_tags(mb_substr(trim((string) ($data['event_name'] ?? $data['name'] ?? '')), 0, 255));
        $location = strip_tags(mb_substr(trim((string) ($data['location'] ?? '')), 0, 255));
        $roles = self::normalizeRoleOptions($data['roles'] ?? []);
        $status = $this->sanitizeStatus((string) ($data['status'] ?? 'open'));
        $token = $this->generateUniqueToken();
        $hash = hash('sha256', $token);
        $slug = $this->generateUniqueSlug($eventName !== '' ? $eventName : 'presensi');

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO tbl_presensi_event (slug, public_token, public_token_hash, event_name, location, roles_json, status, created_by, created_at) VALUES (:slug, :public_token, :public_token_hash, :event_name, :location, :roles_json, :status, :created_by, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                ':slug' => $slug,
                ':public_token' => $token,
                ':public_token_hash' => $hash,
                ':event_name' => $eventName,
                ':location' => $location,
                ':roles_json' => json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':status' => $status,
                ':created_by' => !empty($data['created_by']) ? (int) $data['created_by'] : null,
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->replaceMembers($id, is_array($data['member_ids'] ?? null) ? $data['member_ids'] : []);
            $this->db->commit();

            return ['id' => $id, 'public_token' => $token, 'public_token_hash' => $hash, 'public_url' => '/presensi/' . rawurlencode($token)];
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        if (!$this->db || $id <= 0) {
            return false;
        }

        $fields = [];
        $params = [':id' => $id];
        if (array_key_exists('event_name', $data)) {
            $fields[] = 'event_name = :event_name';
            $params[':event_name'] = strip_tags(mb_substr(trim((string) $data['event_name']), 0, 255));
        }
        if (array_key_exists('location', $data)) {
            $fields[] = 'location = :location';
            $params[':location'] = strip_tags(mb_substr(trim((string) $data['location']), 0, 255));
        }
        if (array_key_exists('roles', $data)) {
            $fields[] = 'roles_json = :roles_json';
            $params[':roles_json'] = json_encode(self::normalizeRoleOptions($data['roles']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (array_key_exists('status', $data)) {
            $fields[] = 'status = :status';
            $params[':status'] = $this->sanitizeStatus((string) $data['status']);
        }
        if (array_key_exists('updated_by', $data)) {
            $fields[] = 'updated_by = :updated_by';
            $params[':updated_by'] = !empty($data['updated_by']) ? (int) $data['updated_by'] : null;
        }

        $this->db->beginTransaction();
        try {
            $updated = true;
            if ($fields !== []) {
                $fields[] = 'updated_at = CURRENT_TIMESTAMP';
                $stmt = $this->db->prepare('UPDATE tbl_presensi_event SET ' . implode(', ', $fields) . ' WHERE presensi_event_id = :id AND deleted_at IS NULL');
                $updated = $stmt->execute($params);
            }
            if (array_key_exists('member_ids', $data) && is_array($data['member_ids'])) {
                $this->replaceMembers($id, $data['member_ids']);
            }
            $this->db->commit();

            return $updated;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function softDelete(int $id, ?int $userId = null): bool
    {
        if (!$this->db || $id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE tbl_presensi_event SET deleted_at = CURRENT_TIMESTAMP, updated_by = :updated_by WHERE presensi_event_id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id, ':updated_by' => $userId]);

        return $stmt->rowCount() > 0;
    }

    /** @param array<int, mixed> $memberIds */
    public function syncMembers(int $eventId, array $memberIds): int
    {
        if (!$this->db || $eventId <= 0) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $count = $this->replaceMembers($eventId, $memberIds);
            $this->db->commit();

            return $count;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    /** @param array<int, mixed> $ids @return int[] */
    public function validTeamIds(array $ids): array
    {
        if (!$this->db) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM teams WHERE id IN ($placeholders) AND deleted_at IS NULL");
        $stmt->execute($ids);
        $found = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        return array_values(array_filter($ids, static fn(int $id): bool => in_array($id, $found, true)));
    }

    /** @return array<int, array<string, mixed>> */
    public function memberOptionsForEvent(int $eventId, string $query = '', int $limit = 12): array
    {
        if (!$this->db || $eventId <= 0) {
            return [];
        }

        $limit = max(1, min(25, $limit));
        $sql = 'SELECT t.*, d.id AS division_id, d.nama AS division_name, k.nama AS commission_name
            FROM tbl_presensi_event_member pem
            INNER JOIN teams t ON t.id = pem.team_id
            LEFT JOIN divisis d ON d.id = t.divisi_id
            LEFT JOIN komsats k ON k.id = t.komsat_id
            WHERE pem.presensi_event_id = :event_id AND t.deleted_at IS NULL';
        $params = [':event_id' => $eventId];
        if (trim($query) !== '') {
            $search = '%' . $this->escapeLike(trim($query)) . '%';
            $sql .= ' AND (t.name LIKE :q_name OR t.designation LIKE :q_designation OR COALESCE(k.nama, t.komsat) LIKE :q_campus OR d.nama LIKE :q_division)';
            $params[':q_name'] = $search;
            $params[':q_designation'] = $search;
            $params[':q_campus'] = $search;
            $params[':q_division'] = $search;
        }
        $sql .= ' ORDER BY t.name ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([TeamMember::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function memberBelongsToEvent(int $eventId, int $teamId): bool
    {
        if (!$this->db || $eventId <= 0 || $teamId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM tbl_presensi_event_member WHERE presensi_event_id = :event_id AND team_id = :team_id LIMIT 1');
        $stmt->execute([':event_id' => $eventId, ':team_id' => $teamId]);

        return (bool) $stmt->fetchColumn();
    }

    public function roleIsAllowed(array $event, string $role): bool
    {
        return in_array($role, self::normalizeRoles($event['role_options'] ?? $event['roles'] ?? []), true);
    }

    /** @param array<string, mixed>|null $oldData @param array<string, mixed>|null $newData */
    public function logAudit(string $action, string $entityType, string $entityId, ?int $userId = null, ?array $oldData = null, ?array $newData = null, ?string $ip = null, ?string $userAgent = null): void
    {
        if (!$this->db) {
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO tbl_audit_log (user_id, action, entity_type, entity_id, old_data, new_data, ip_address, user_agent, created_at) VALUES (:user_id, :action, :entity_type, :entity_id, :old_data, :new_data, :ip_address, :user_agent, CURRENT_TIMESTAMP)');
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ':new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ':ip_address' => $ip,
                ':user_agent' => $userAgent,
            ]);
        } catch (Throwable) {
            // Audit logging must not break the primary admin action.
        }
    }

    /** @param array<string, mixed> $filters @return array{0: string, 1: array<string, mixed>} */
    private function adminWhere(array $filters): array
    {
        $where = ['e.deleted_at IS NULL'];
        $params = [];
        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $search = '%' . $this->escapeLike(trim((string) $filters['q'])) . '%';
            $where[] = '(e.event_name LIKE :q_event_name OR e.location LIKE :q_location)';
            $params[':q_event_name'] = $search;
            $params[':q_location'] = $search;
        }
        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $where[] = 'e.status = :status';
            $params[':status'] = $this->sanitizeStatus((string) $filters['status']);
        }

        return [implode(' AND ', $where), $params];
    }

    private function baseSelect(): string
    {
        return "SELECT e.*,
            (SELECT COUNT(*) FROM tbl_presensi_event_member pem WHERE pem.presensi_event_id = e.presensi_event_id) AS member_count,
            (SELECT COUNT(*) FROM tbl_presensi_submission ps WHERE ps.presensi_event_id = e.presensi_event_id) AS submission_count,
            (SELECT COUNT(*) FROM tbl_presensi_submission ps WHERE ps.presensi_event_id = e.presensi_event_id AND ps.status = 'pending') AS pending_count,
            (SELECT COUNT(*) FROM tbl_presensi_submission ps WHERE ps.presensi_event_id = e.presensi_event_id AND ps.status = 'approved') AS approved_count
            FROM tbl_presensi_event e";
    }

    /** @return array<int, array<string, mixed>> */
    private function membersForEvent(int $eventId): array
    {
        return $this->memberOptionsForEvent($eventId, '', 500);
    }

    /** @param array<int, mixed> $memberIds */
    private function replaceMembers(int $eventId, array $memberIds): int
    {
        $validIds = $this->validTeamIds($memberIds);
        $delete = $this->db->prepare('DELETE FROM tbl_presensi_event_member WHERE presensi_event_id = :event_id');
        $delete->execute([':event_id' => $eventId]);

        if ($validIds === []) {
            return 0;
        }

        $insert = $this->db->prepare('INSERT INTO tbl_presensi_event_member (presensi_event_id, team_id, created_at) VALUES (:event_id, :team_id, CURRENT_TIMESTAMP)');
        $count = 0;
        foreach ($validIds as $teamId) {
            $insert->execute([':event_id' => $eventId, ':team_id' => $teamId]);
            $count++;
        }

        return $count;
    }

    private function sanitizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        return in_array($status, self::STATUSES, true) ? $status : 'open';
    }

    private static function sanitizeRoleScore(mixed $score): int
    {
        $score = filter_var($score, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

        return max(0, min(100000, (int) $score));
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = bin2hex(random_bytes(24));
            $stmt = $this->db->prepare('SELECT 1 FROM tbl_presensi_event WHERE public_token_hash = :hash LIMIT 1');
            $stmt->execute([':hash' => hash('sha256', $token)]);
        } while ($stmt->fetchColumn());

        return $token;
    }

    private function generateUniqueSlug(string $value): string
    {
        $base = $this->slugify($value);
        $slug = $base;
        $suffix = 1;
        while ($this->findAnyBySlug($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
            if ($suffix > 100) {
                return $base . '-' . bin2hex(random_bytes(4));
            }
        }

        return $slug;
    }

    private function slugify(string $value): string
    {
        $slug = mb_strtolower($value);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
        $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';

        return trim($slug, '-') ?: 'presensi';
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
