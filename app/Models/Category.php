<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Category
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $statement = $this->db->query('SELECT category_id, category_name, category_banner, meta_title, meta_keyword, meta_description FROM tbl_category ORDER BY category_name ASC');

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT category_id, category_name, category_banner, meta_title, meta_keyword, meta_description FROM tbl_category WHERE category_id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? self::mapRow($row) : null;
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM tbl_category WHERE LOWER(category_name) = LOWER(:name)';
        $params = ['name' => $name];
        if ($excludeId !== null) {
            $sql .= ' AND category_id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(string $name): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO tbl_category (category_name, category_banner, meta_title, meta_keyword, meta_description)
             VALUES (:name, :banner, :meta_title, :meta_keyword, :meta_description)'
        );
        $statement->execute([
            'name' => $name,
            'banner' => '',
            'meta_title' => '',
            'meta_keyword' => '',
            'meta_description' => '',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name): bool
    {
        $statement = $this->db->prepare('UPDATE tbl_category SET category_name = :name WHERE category_id = :id');
        $statement->execute(['name' => $name, 'id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function newsCount(int $id): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM tbl_news WHERE category_id = :id');
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM tbl_category WHERE category_id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private static function mapRow(array $row): array
    {
        return [
            'id' => isset($row['category_id']) ? (int) $row['category_id'] : 0,
            'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : 0,
            'name' => (string) ($row['category_name'] ?? ''),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'banner' => (string) ($row['category_banner'] ?? ''),
            'category_banner' => (string) ($row['category_banner'] ?? ''),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_keyword' => (string) ($row['meta_keyword'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
        ];
    }
}
