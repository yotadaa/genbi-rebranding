<?php

declare(strict_types=1);

namespace App\Models;

class PhotoGallery
{
    public function __construct(private ?\PDO $db = null) {}

    public static function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['photo_id'] ?? $row['id'] ?? 0),
            'photo_id' => (int) ($row['photo_id'] ?? $row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'image' => (string) ($row['image_url'] ?? $row['image'] ?? ''),
            'caption' => (string) ($row['caption'] ?? ''),
            'status' => (string) ($row['status'] ?? 'show'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'created_at' => $row['created_at'] ?? '',
        ];
    }

    public function all(int $limit = 100, int $offset = 0): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare('SELECT * FROM tbl_photo_gallery WHERE deleted_at IS NULL ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable) {
            return [];
        }
    }

    public function find(int $id): ?array
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare('SELECT * FROM tbl_photo_gallery WHERE photo_id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    public function create(array $data): int
    {
        if (!$this->db) return 0;
        $stmt = $this->db->prepare('INSERT INTO tbl_photo_gallery (title, image_url, caption, status, sort_order, created_at) VALUES (:title, :image_url, :caption, :status, :sort_order, NOW())');
        $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':image_url' => $data['image'] ?? '',
            ':caption' => $data['caption'] ?? '',
            ':status' => $data['status'] ?? 'show',
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->db || $data === []) return false;
        $map = ['title' => 'title', 'image' => 'image_url', 'caption' => 'caption', 'status' => 'status', 'sort_order' => 'sort_order'];
        $fields = [];
        $params = [':id' => $id];
        foreach ($map as $key => $column) {
            if (array_key_exists($key, $data)) {
                $fields[] = $column . ' = :' . $key;
                $params[':' . $key] = $key === 'sort_order' ? (int) $data[$key] : $data[$key];
            }
        }
        if ($fields === []) return false;
        $stmt = $this->db->prepare('UPDATE tbl_photo_gallery SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE photo_id = :id AND deleted_at IS NULL');
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare('UPDATE tbl_photo_gallery SET deleted_at = NOW() WHERE photo_id = :id AND deleted_at IS NULL');
        return $stmt->execute([':id' => $id]);
    }
}
