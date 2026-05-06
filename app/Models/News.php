<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Slugger;
use PDO;
use Throwable;

final class News
{
    public function __construct(private PDO $db)
    {
    }

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function paginate(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $sql = 'SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.deleted_at IS NULL AND (n.status IS NULL OR n.status = :status)';
        $params = ['status' => 'published'];

        if (!empty($filters['category'])) {
            $sql .= ' AND c.category_name = :category';
            $params['category'] = $filters['category'];
        }

        $sql .= ' ORDER BY COALESCE(n.published_at, n.news_date, n.created_at) DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.news_id = :id AND n.deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? self::mapRow($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.slug = :slug AND n.deleted_at IS NULL LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return is_array($row) ? self::mapRow($row) : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function related(int $newsId, ?int $categoryId, int $limit = 3): array
    {
        $sql = 'SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.news_id <> :id AND n.deleted_at IS NULL AND (n.status IS NULL OR n.status = :status)';
        if ($categoryId !== null) {
            $sql .= ' AND n.category_id = :category_id';
        }
        $sql .= ' ORDER BY COALESCE(n.published_at, n.news_date, n.created_at) DESC LIMIT :limit';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $newsId, PDO::PARAM_INT);
        $statement->bindValue(':status', 'published');
        if ($categoryId !== null) {
            $statement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    public function incrementViews(int $id): void
    {
        try {
            $statement = $this->db->prepare('UPDATE tbl_news SET views = COALESCE(views, 0) + 1 WHERE news_id = :id');
            $statement->execute(['id' => $id]);
        } catch (Throwable) {
            // Some legacy installations may not have a views column yet.
        }
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        $title = (string) ($row['news_title'] ?? $row['title'] ?? 'Berita GenBI Jambi');
        $id = (int) ($row['news_id'] ?? $row['id'] ?? 0);
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '') {
            $slug = Slugger::slugify($title) . ($id > 0 ? '-' . $id : '');
        }

        return [
            'id' => $id,
            'news_id' => $id,
            'slug' => $slug,
            'title' => $title,
            'news_title' => $title,
            'content' => (string) ($row['news_content'] ?? $row['content'] ?? ''),
            'news_content' => (string) ($row['news_content'] ?? $row['content'] ?? ''),
            'excerpt' => (string) ($row['news_content_short'] ?? $row['meta_description'] ?? ''),
            'news_content_short' => (string) ($row['news_content_short'] ?? $row['meta_description'] ?? ''),
            'date' => (string) ($row['published_at'] ?? $row['news_date'] ?? $row['created_at'] ?? ''),
            'published_at' => $row['published_at'] ?? $row['news_date'] ?? $row['created_at'] ?? null,
            'image' => (string) ($row['photo'] ?? $row['banner'] ?? ''),
            'photo' => (string) ($row['photo'] ?? ''),
            'banner' => (string) ($row['banner'] ?? ''),
            'category' => (string) ($row['category_name'] ?? $row['category'] ?? 'Berita GenBI'),
            'category_name' => (string) ($row['category_name'] ?? $row['category'] ?? 'Berita GenBI'),
            'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
            'author' => (string) ($row['contributor_pewarta'] ?? 'Redaksi GenBI Jambi'),
            'contributor_redaksi' => (string) ($row['contributor_redaksi'] ?? ''),
            'contributor_pewarta' => (string) ($row['contributor_pewarta'] ?? ''),
            'contributor_editor' => (string) ($row['contributor_editor'] ?? ''),
            'editor' => (string) ($row['contributor_editor'] ?? ''),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_keyword' => (string) ($row['meta_keyword'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
            'status' => (string) ($row['status'] ?? 'published'),
        ];
    }
}
