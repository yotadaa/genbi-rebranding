<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class Event
{
    public function __construct(private ?PDO $db = null) {}

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function paginate(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $sql = 'SELECT * FROM tbl_event WHERE ' . $this->buildPublicVisibilityWhere();
            $params = [];

            if (!empty($filters['q'])) {
                $sql .= ' AND (event_title LIKE :q OR event_content_short LIKE :q OR event_location LIKE :q)';
                $params['q'] = '%' . $filters['q'] . '%';
            }

            $sql .= ' ORDER BY event_start_date DESC, event_id DESC LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    public function countPublic(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        try {
            $sql = 'SELECT COUNT(*) FROM tbl_event WHERE ' . $this->buildPublicVisibilityWhere();
            $params = [];

            if (!empty($filters['q'])) {
                $sql .= ' AND (event_title LIKE :q OR event_content_short LIKE :q OR event_location LIKE :q)';
                $params['q'] = '%' . $filters['q'] . '%';
            }

            $stmt = $this->db->prepare($sql);
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
            $stmt = $this->db->prepare('SELECT * FROM tbl_event WHERE event_id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? self::mapRow($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function findPublicById(int $id): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $statement = $this->db->prepare('SELECT * FROM tbl_event WHERE event_id = :id AND ' . $this->buildPublicVisibilityWhere() . ' LIMIT 1');
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return $row ? self::mapRow($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function allForAdmin(int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare('SELECT * FROM tbl_event ORDER BY event_id DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ?int
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO tbl_event (event_title, event_content, event_content_short, event_start_date, event_end_date, event_location, event_map, photo, banner, meta_title, meta_keyword, meta_description) VALUES (:title, :content, :excerpt, :start, :end, :location, :map, :photo, :banner, :meta_title, :meta_keyword, :meta_description)'
            );
            $stmt->execute([
                ':title' => $data['event_title'] ?? $data['title'] ?? '',
                ':content' => $data['event_content'] ?? $data['content'] ?? '',
                ':excerpt' => $data['event_content_short'] ?? $data['excerpt'] ?? '',
                ':start' => $data['event_start_date'] ?? $data['start_date'] ?? date('Y-m-d'),
                ':end' => $data['event_end_date'] ?? $data['end_date'] ?? date('Y-m-d'),
                ':location' => $data['event_location'] ?? $data['location'] ?? '',
                ':map' => $data['event_map'] ?? $data['map'] ?? '',
                ':photo' => $data['photo'] ?? '',
                ':banner' => $data['banner'] ?? '',
                ':meta_title' => $data['meta_title'] ?? '',
                ':meta_keyword' => $data['meta_keyword'] ?? '',
                ':meta_description' => $data['meta_description'] ?? '',
            ]);

            $id = (int) $this->db->lastInsertId();
            return $id > 0 ? $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        if (!$this->db) {
            return false;
        }

        $allowedFields = [
            'event_title' => 'event_title', 'title' => 'event_title',
            'event_content' => 'event_content', 'content' => 'event_content',
            'event_content_short' => 'event_content_short', 'excerpt' => 'event_content_short',
            'event_start_date' => 'event_start_date', 'start_date' => 'event_start_date',
            'event_end_date' => 'event_end_date', 'end_date' => 'event_end_date',
            'event_location' => 'event_location', 'location' => 'event_location',
            'event_map' => 'event_map', 'map' => 'event_map',
            'photo' => 'photo', 'banner' => 'banner',
            'meta_title' => 'meta_title', 'meta_keyword' => 'meta_keyword', 'meta_description' => 'meta_description',
        ];

        $sets = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $column = $allowedFields[$key] ?? null;
            if ($column === null) {
                continue;
            }
            $paramKey = ':' . str_replace('.', '_', $column);
            $sets[] = "{$column} = {$paramKey}";
            $params[$paramKey] = $value;
        }

        if (empty($sets)) {
            return false;
        }

        try {
            $sql = 'UPDATE tbl_event SET ' . implode(', ', $sets) . ' WHERE event_id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM tbl_event WHERE event_id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        $title = (string) ($row['event_title'] ?? '');
        $id = (int) ($row['event_id'] ?? 0);

        return [
            'id' => $id,
            'event_id' => $id,
            'title' => $title,
            'event_title' => $title,
            'content' => (string) ($row['event_content'] ?? ''),
            'excerpt' => (string) ($row['event_content_short'] ?? ''),
            'start_date' => (string) ($row['event_start_date'] ?? ''),
            'end_date' => (string) ($row['event_end_date'] ?? ''),
            'location' => (string) ($row['event_location'] ?? ''),
            'map' => (string) ($row['event_map'] ?? ''),
            'image' => self::resolveImageUrl((string) ($row['photo'] ?? $row['banner'] ?? '')),
            'photo' => self::resolveImageUrl((string) ($row['photo'] ?? '')),
            'banner' => self::resolveImageUrl((string) ($row['banner'] ?? '')),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_keyword' => (string) ($row['meta_keyword'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
            'status' => self::deriveStatus((string) ($row['event_end_date'] ?? '')),
        ];
    }

    private static function resolveImageUrl(string $filename): string
    {
        if ($filename === '') {
            return '';
        }
        if (str_starts_with($filename, 'http') || str_starts_with($filename, '/')) {
            return $filename;
        }

        return '/uploads/' . ltrim($filename, '/');
    }

    private static function deriveStatus(string $endDate): string
    {
        if ($endDate === '') {
            return 'Upcoming';
        }

        $end = strtotime($endDate);
        if ($end === false) {
            return 'Upcoming';
        }

        return $end >= strtotime('today') ? 'Upcoming' : 'Past Event';
    }

    private function buildPublicVisibilityWhere(): string
    {
        $conditions = ['1=1'];

        // Legacy databases may still be missing publishing columns, so public
        // visibility only depends on the constraints that exist locally.
        if ($this->hasColumn('deleted_at')) {
            $conditions[] = 'deleted_at IS NULL';
        }
        if ($this->hasColumn('status')) {
            $conditions[] = "status = 'published'";
        } elseif ($this->hasColumn('published')) {
            $conditions[] = 'published = 1';
        }

        return implode(' AND ', $conditions);
    }

    private function hasColumn(string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $statement = $this->db?->query('DESCRIBE tbl_event');
                $columns = $statement ? array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'Field') : [];
            } catch (Throwable) {
                try {
                    $statement = $this->db?->query('PRAGMA table_info(tbl_event)');
                    $rows = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
                    $columns = array_column($rows, 'name');
                } catch (Throwable) {
                    $columns = [];
                }
            }
        }

        return in_array($column, $columns, true);
    }
}
