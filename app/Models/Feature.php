<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class Feature
{
    public function __construct(private ?PDO $db = null)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function allForAdmin(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        $sql = 'SELECT * FROM tbl_feature WHERE ' . $this->activeWhere();
        $params = [];
        $sql = $this->applyFilters($sql, $params, $filters, false);
        $sql .= ' ORDER BY sort_order ASC, feature_id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->attachImages($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function countForAdmin(array $filters = []): int
    {
        if (!$this->db) {
            return 0;
        }

        $sql = 'SELECT COUNT(*) FROM tbl_feature WHERE ' . $this->activeWhere();
        $params = [];
        $sql = $this->applyFilters($sql, $params, $filters, false);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        if (!$this->db) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM tbl_feature WHERE feature_id = :id AND ' . $this->activeWhere() . ' LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapWithImages($row);
    }

    /** @return array<int, array<string, mixed>> */
    public function homeVisible(int $limit = 12): array
    {
        if (!$this->db) {
            return [];
        }

        $sql = 'SELECT * FROM tbl_feature WHERE ' . $this->publicWhere() . ' ORDER BY sort_order ASC, feature_id DESC LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->attachImages($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(array $data): int
    {
        if (!$this->db) {
            return 0;
        }

        $payload = $this->normalizePayload($data);
        $images = $payload['images'];
        unset($payload['images']);

        $columns = array_keys($payload);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $sql = 'INSERT INTO tbl_feature (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($payload as $column => $value) {
                $stmt->bindValue(':' . $column, $value);
            }
            $stmt->execute();
            $featureId = (int) $this->db->lastInsertId();
            $this->syncImages($featureId, $images);
            $this->db->commit();
            return $featureId;
        } catch (Throwable) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->db) {
            return false;
        }

        $payload = $this->normalizePayload($data, false);
        $images = $payload['images'] ?? null;
        unset($payload['images']);

        if ($payload === [] && $images === null) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            if ($payload !== []) {
                $sets = [];
                $params = [':id' => $id];
                foreach ($payload as $column => $value) {
                    $sets[] = $column . ' = :' . $column;
                    $params[':' . $column] = $value;
                }
                $sets[] = 'updated_at = NOW()';
                $sql = 'UPDATE tbl_feature SET ' . implode(', ', $sets) . ' WHERE feature_id = :id AND ' . $this->activeWhere();
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            if (is_array($images)) {
                $this->syncImages($id, $images);
            }

            $this->db->commit();
            return true;
        } catch (Throwable) {
            $this->db->rollBack();
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('UPDATE tbl_feature SET deleted_at = NOW(), show_on_home = 0, updated_at = NOW() WHERE feature_id = :id AND ' . $this->activeWhere());
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function deleteImage(int $featureId, int $imageId): ?string
    {
        if (!$this->db) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT image_path FROM tbl_feature_image WHERE id = :id AND feature_id = :feature_id LIMIT 1');
        $stmt->execute([':id' => $imageId, ':feature_id' => $featureId]);
        $path = $stmt->fetchColumn();
        if (!$path) {
            return null;
        }

        $delete = $this->db->prepare('DELETE FROM tbl_feature_image WHERE id = :id AND feature_id = :feature_id');
        $delete->execute([':id' => $imageId, ':feature_id' => $featureId]);

        return (string) $path;
    }

    /** @param array<int, int> $imageIds */
    public function reorderImages(int $featureId, array $imageIds): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE tbl_feature_image SET sort_order = :sort_order, updated_at = NOW() WHERE id = :id AND feature_id = :feature_id');
        foreach (array_values($imageIds) as $index => $imageId) {
            $stmt->execute([
                ':sort_order' => $index,
                ':id' => $imageId,
                ':feature_id' => $featureId,
            ]);
        }

        return true;
    }

    /** @return array<int, array<string, mixed>> */
    public function imageRows(int $featureId): array
    {
        if (!$this->db) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT id, feature_id, image_path, sort_order FROM tbl_feature_image WHERE feature_id = :feature_id ORDER BY sort_order ASC, id ASC');
        $stmt->execute([':feature_id' => $featureId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): array => [
            'id' => (int) $row['id'],
            'feature_id' => (int) $row['feature_id'],
            'path' => (string) $row['image_path'],
            'url' => self::resolveImageUrl((string) $row['image_path']),
            'sort_order' => (int) $row['sort_order'],
        ], $rows);
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(string $sql, array &$params, array $filters, bool $publicOnly): string
    {
        if (!empty($filters['q'])) {
            $sql .= ' AND (title LIKE :q OR name LIKE :q OR description LIKE :q OR focus LIKE :q)';
            $params[':q'] = '%' . trim((string) $filters['q']) . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = trim((string) $filters['status']);
        }
        if (!$publicOnly && isset($filters['show_on_home']) && $filters['show_on_home'] !== '') {
            $sql .= ' AND show_on_home = :show_on_home';
            $params[':show_on_home'] = (int) (bool) $filters['show_on_home'];
        }
        return $sql;
    }

    private function activeWhere(): string
    {
        return 'deleted_at IS NULL';
    }

    private function publicWhere(): string
    {
        return "deleted_at IS NULL AND show_on_home = 1 AND status = 'published'";
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['feature_id'] ?? $row['id'] ?? 0),
            'feature_id' => (int) ($row['feature_id'] ?? $row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'focus' => (string) ($row['focus'] ?? ''),
            'icon_key' => (string) ($row['icon_key'] ?? 'sparkles'),
            'show_on_home' => (int) ($row['show_on_home'] ?? 0) === 1,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'status' => (string) ($row['status'] ?? 'draft'),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    /** @param array<int, array<string, mixed>> $rows @return array<int, array<string, mixed>> */
    private function attachImages(array $rows): array
    {
        $features = [];
        $ids = [];
        foreach ($rows as $row) {
            $mapped = self::mapRow($row);
            $mapped['images'] = [];
            $mapped['image'] = '';
            $features[$mapped['id']] = $mapped;
            $ids[] = $mapped['id'];
        }

        if ($ids !== []) {
            $imageMap = $this->loadImagesForFeatures($ids);
            foreach ($imageMap as $featureId => $images) {
                if (!isset($features[$featureId])) {
                    continue;
                }
                $features[$featureId]['images'] = $images;
                $features[$featureId]['image'] = $images[0]['url'] ?? '';
            }
        }

        return array_values($features);
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function mapWithImages(array $row): array
    {
        $mapped = self::mapRow($row);
        $mapped['images'] = $this->imageRows($mapped['id']);
        $mapped['image'] = $mapped['images'][0]['url'] ?? '';
        return $mapped;
    }

    /** @param array<int, int> $featureIds @return array<int, array<int, array<string, mixed>>> */
    private function loadImagesForFeatures(array $featureIds): array
    {
        if (!$this->db || $featureIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($featureIds), '?'));
        $stmt = $this->db->prepare("SELECT id, feature_id, image_path, sort_order FROM tbl_feature_image WHERE feature_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
        foreach ($featureIds as $index => $featureId) {
            $stmt->bindValue($index + 1, $featureId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $featureId = (int) $row['feature_id'];
            $map[$featureId] ??= [];
            $map[$featureId][] = [
                'id' => (int) $row['id'],
                'feature_id' => $featureId,
                'path' => (string) $row['image_path'],
                'url' => self::resolveImageUrl((string) $row['image_path']),
                'sort_order' => (int) $row['sort_order'],
            ];
        }

        return $map;
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function normalizePayload(array $data, bool $includeDefaults = true): array
    {
        $payload = [];
        $textFields = [
            'title' => 120,
            'name' => 255,
            'description' => 5000,
            'focus' => 120,
            'icon_key' => 80,
        ];
        foreach ($textFields as $field => $limit) {
            if (!array_key_exists($field, $data)) {
                if ($includeDefaults) {
                    $payload[$field] = '';
                }
                continue;
            }
            $payload[$field] = strip_tags(mb_substr(trim((string) $data[$field]), 0, $limit));
        }

        if (array_key_exists('show_on_home', $data) || $includeDefaults) {
            $payload['show_on_home'] = !empty($data['show_on_home']) ? 1 : 0;
        }
        if (array_key_exists('sort_order', $data) || $includeDefaults) {
            $payload['sort_order'] = (int) ($data['sort_order'] ?? 0);
        }
        if (array_key_exists('status', $data) || $includeDefaults) {
            $status = strtolower(trim((string) ($data['status'] ?? 'draft')));
            $payload['status'] = in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft';
        }

        if ($includeDefaults) {
            $payload['created_at'] = date('Y-m-d H:i:s');
        }
        $payload['updated_at'] = date('Y-m-d H:i:s');

        if (array_key_exists('images', $data)) {
            $payload['images'] = $this->normalizeImages($data['images']);
        } elseif ($includeDefaults) {
            $payload['images'] = [];
        }

        return $payload;
    }

    /** @param mixed $images @return array<int, array<string, mixed>> */
    private function normalizeImages(mixed $images): array
    {
        if (!is_array($images)) {
            return [];
        }

        $normalized = [];
        foreach (array_values($images) as $index => $image) {
            if (is_string($image)) {
                $path = trim($image);
                if ($path === '') {
                    continue;
                }
                $normalized[] = ['id' => 0, 'path' => $this->normalizeImagePath($path), 'sort_order' => $index];
                continue;
            }
            if (!is_array($image)) {
                continue;
            }
            $path = trim((string) ($image['path'] ?? $image['url'] ?? ''));
            if ($path === '') {
                continue;
            }
            $normalized[] = [
                'id' => (int) ($image['id'] ?? 0),
                'path' => $this->normalizeImagePath($path),
                'sort_order' => (int) ($image['sort_order'] ?? $index),
            ];
        }

        return $normalized;
    }

    /** @param array<int, array<string, mixed>> $images */
    private function syncImages(int $featureId, array $images): void
    {
        $existing = $this->imageRows($featureId);
        $existingIds = array_column($existing, 'id');
        $incomingIds = array_filter(array_map(static fn(array $image): int => (int) ($image['id'] ?? 0), $images));
        $deleteIds = array_diff($existingIds, $incomingIds);

        if ($deleteIds !== []) {
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            $stmt = $this->db?->prepare("DELETE FROM tbl_feature_image WHERE feature_id = ? AND id IN ($placeholders)");
            if ($stmt) {
                $stmt->bindValue(1, $featureId, PDO::PARAM_INT);
                $paramIndex = 2;
                foreach ($deleteIds as $deleteId) {
                    $stmt->bindValue($paramIndex++, $deleteId, PDO::PARAM_INT);
                }
                $stmt->execute();
            }
        }

        $insertStmt = $this->db?->prepare('INSERT INTO tbl_feature_image (feature_id, image_path, sort_order, created_at, updated_at) VALUES (:feature_id, :image_path, :sort_order, NOW(), NOW())');
        $updateStmt = $this->db?->prepare('UPDATE tbl_feature_image SET image_path = :image_path, sort_order = :sort_order, updated_at = NOW() WHERE id = :id AND feature_id = :feature_id');

        foreach (array_values($images) as $index => $image) {
            $sortOrder = (int) ($image['sort_order'] ?? $index);
            $path = (string) ($image['path'] ?? '');
            $imageId = (int) ($image['id'] ?? 0);
            if ($imageId > 0 && $updateStmt) {
                $updateStmt->execute([
                    ':id' => $imageId,
                    ':feature_id' => $featureId,
                    ':image_path' => $path,
                    ':sort_order' => $sortOrder,
                ]);
                continue;
            }
            if ($insertStmt) {
                $insertStmt->execute([
                    ':feature_id' => $featureId,
                    ':image_path' => $path,
                    ':sort_order' => $sortOrder,
                ]);
            }
        }
    }

    private function normalizeImagePath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, '/uploads/features/')) {
            return $path;
        }
        return '/uploads/features/' . ltrim(str_replace('/uploads/features/', '', $path), '/');
    }

    private static function resolveImageUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }
        return '/uploads/features/' . ltrim($path, '/');
    }
}
