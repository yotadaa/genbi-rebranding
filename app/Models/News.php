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
    public function paginate(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.deleted_at IS NULL AND (n.status IS NULL OR n.status = :status)';
        $params = ['status' => 'published'];

        if (!empty($filters['category'])) {
            $sql .= ' AND c.category_name = :category';
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['q'])) {
            $sql .= ' AND (n.news_title LIKE :q OR n.news_content_short LIKE :q OR n.news_content LIKE :q OR c.category_name LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
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

    /** @param array<string, string|null> $filters */
    public function countPublic(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.deleted_at IS NULL AND (n.status IS NULL OR n.status = :status)';
        $params = ['status' => 'published'];

        if (!empty($filters['category'])) {
            $sql .= ' AND c.category_name = :category';
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['q'])) {
            $sql .= ' AND (n.news_title LIKE :q OR n.news_content_short LIKE :q OR n.news_content LIKE :q OR c.category_name LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
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

    // --- Admin methods ---

    /** @return array<int, array<string, mixed>> */
    public function allForAdmin(int $limit = 50, int $offset = 0, ?string $status = null): array
    {
        $sql = 'SELECT n.*, c.category_name FROM tbl_news n LEFT JOIN tbl_category c ON c.category_id = n.category_id WHERE n.deleted_at IS NULL';
        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= ' AND n.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY n.news_id DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    public function countForAdmin(?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM tbl_news WHERE deleted_at IS NULL';
        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ?int
    {
        $statement = $this->db->prepare(
            'INSERT INTO tbl_news (news_title, news_content, news_content_short, news_date, photo, banner, category_id, comment, meta_title, meta_keyword, meta_description, slug, status, published_at, contributor_pewarta, contributor_editor, contributor_redaksi, published) VALUES (:title, :content, :excerpt, :date, :photo, :banner, :category_id, :comment, :meta_title, :meta_keyword, :meta_description, :slug, :status, :published_at, :pewarta, :editor, :redaksi, :published)'
        );

        $statement->execute([
            ':title' => $data['news_title'] ?? $data['title'] ?? '',
            ':content' => $data['news_content'] ?? $data['content'] ?? '',
            ':excerpt' => $data['news_content_short'] ?? $data['excerpt'] ?? '',
            ':date' => $data['news_date'] ?? $data['date'] ?? date('Y-m-d'),
            ':photo' => $data['photo'] ?? '',
            ':banner' => $data['banner'] ?? '',
            ':category_id' => (int) ($data['category_id'] ?? 0),
            ':comment' => $data['comment'] ?? 'On',
            ':meta_title' => $data['meta_title'] ?? '',
            ':meta_keyword' => $data['meta_keyword'] ?? '',
            ':meta_description' => $data['meta_description'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':status' => $data['status'] ?? 'draft',
            ':published_at' => ($data['status'] ?? '') === 'published' ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null,
            ':pewarta' => $data['contributor_pewarta'] ?? '',
            ':editor' => $data['contributor_editor'] ?? '',
            ':redaksi' => $data['contributor_redaksi'] ?? '',
            ':published' => ($data['status'] ?? '') === 'published' ? 1 : 0,
        ]);

        $id = (int) $this->db->lastInsertId();
        return $id > 0 ? $id : null;
    }

    /** @param array<string, mixed> $data */
    public function updateNews(int $id, array $data): bool
    {
        $allowedFields = [
            'news_title' => 'news_title',
            'title' => 'news_title',
            'news_content' => 'news_content',
            'content' => 'news_content',
            'news_content_short' => 'news_content_short',
            'excerpt' => 'news_content_short',
            'news_date' => 'news_date',
            'date' => 'news_date',
            'photo' => 'photo',
            'banner' => 'banner',
            'category_id' => 'category_id',
            'comment' => 'comment',
            'meta_title' => 'meta_title',
            'meta_keyword' => 'meta_keyword',
            'meta_description' => 'meta_description',
            'slug' => 'slug',
            'status' => 'status',
            'published_at' => 'published_at',
            'contributor_pewarta' => 'contributor_pewarta',
            'contributor_editor' => 'contributor_editor',
            'contributor_redaksi' => 'contributor_redaksi',
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

        // Auto-set published flag based on status
        if (isset($data['status'])) {
            $sets[] = 'published = :published_flag';
            $params[':published_flag'] = $data['status'] === 'published' ? 1 : 0;
            if ($data['status'] === 'published' && !isset($data['published_at'])) {
                $sets[] = 'published_at = COALESCE(published_at, NOW())';
            }
        }

        $sql = 'UPDATE tbl_news SET ' . implode(', ', $sets) . ' WHERE news_id = :id AND deleted_at IS NULL';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        try {
            $statement = $this->db->prepare('UPDATE tbl_news SET deleted_at = NOW(), published = 0 WHERE news_id = :id AND deleted_at IS NULL');
            $statement->execute([':id' => $id]);
            return $statement->rowCount() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function categories(): array
    {
        try {
            $statement = $this->db->query('SELECT category_id, category_name FROM tbl_category ORDER BY category_name');
            return $statement->fetchAll();
        } catch (Throwable) {
            return [];
        }
    }

    public function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = Slugger::slugify($title);
        $slug = $base;
        $suffix = 1;

        while (true) {
            $sql = 'SELECT news_id FROM tbl_news WHERE slug = :slug AND deleted_at IS NULL';
            $params = [':slug' => $slug];
            if ($excludeId !== null) {
                $sql .= ' AND news_id <> :exclude';
                $params[':exclude'] = $excludeId;
            }
            $sql .= ' LIMIT 1';
            $statement = $this->db->prepare($sql);
            $statement->execute($params);
            if (!$statement->fetch()) {
                break;
            }
            $slug = $base . '-' . $suffix;
            $suffix++;
            if ($suffix > 100) {
                $slug = $base . '-' . bin2hex(random_bytes(4));
                break;
            }
        }

        return $slug;
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
            'image' => self::resolveImageUrl((string) ($row['photo'] ?? $row['banner'] ?? '')),
            'photo' => self::resolveImageUrl((string) ($row['photo'] ?? '')),
            'banner' => self::resolveImageUrl((string) ($row['banner'] ?? '')),
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
            'related' => (string) ($row['related'] ?? ''),
            'status' => (string) ($row['status'] ?? 'published'),
        ];
    }

    private static function resolveImageUrl(string $filename): string
    {
        if ($filename === '') {
            return '';
        }
        // Already a full URL or absolute path
        if (str_starts_with($filename, 'http') || str_starts_with($filename, '/')) {
            return $filename;
        }
        return 'https://genbijambi.com/public/uploads/' . $filename;
    }
}
