<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class NewsComment
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function forNews(int $newsId): array
    {
        $statement = $this->db->prepare("SELECT c.*, n.news_title FROM tbl_news_comment c LEFT JOIN tbl_news n ON n.news_id = c.news_id WHERE c.news_id = :news_id AND c.status = 'approved' AND c.deleted_at IS NULL ORDER BY c.created_at DESC");
        $statement->execute(['news_id' => $newsId]);

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function create(array $data): array
    {
        $statement = $this->db->prepare('INSERT INTO tbl_news_comment (news_id, name, email, website, content, status, ip_address, user_agent, created_at) VALUES (:news_id, :name, :email, :website, :content, :status, :ip_address, :user_agent, NOW())');
        $statement->execute([
            'news_id' => (int) $data['news_id'],
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'website' => $data['website'] ?? null,
            'content' => (string) $data['content'],
            'status' => $data['status'] ?? 'pending',
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        return [
            'id' => (int) $this->db->lastInsertId(),
            'status' => 'pending',
        ];
    }

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function paginateForAdmin(array $filters = [], int $limit = 50): array
    {
        $sql = 'SELECT c.*, n.news_title FROM tbl_news_comment c LEFT JOIN tbl_news n ON n.news_id = c.news_id WHERE c.deleted_at IS NULL';
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= ' AND c.status = :status';
            $params['status'] = strtolower((string) $filters['status']);
        }
        $sql .= ' ORDER BY c.created_at DESC LIMIT :limit';

        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    public function approve(int $id, ?int $moderatorId = null): bool
    {
        return $this->updateStatus($id, 'approved', $moderatorId);
    }

    public function reject(int $id, ?int $moderatorId = null): bool
    {
        return $this->updateStatus($id, 'rejected', $moderatorId);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('UPDATE tbl_news_comment SET deleted_at = NOW() WHERE comment_id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function updateStatus(int $id, string $status, ?int $moderatorId): bool
    {
        $statement = $this->db->prepare('UPDATE tbl_news_comment SET status = :status, moderated_by = :moderated_by, moderated_at = NOW(), updated_at = NOW() WHERE comment_id = :id AND deleted_at IS NULL');
        $statement->execute([
            'id' => $id,
            'status' => $status,
            'moderated_by' => $moderatorId,
        ]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    public static function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['comment_id'] ?? $row['id'] ?? 0),
            'comment_id' => (int) ($row['comment_id'] ?? $row['id'] ?? 0),
            'news_id' => (int) ($row['news_id'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Pembaca'),
            'email' => (string) ($row['email'] ?? ''),
            'website' => (string) ($row['website'] ?? ''),
            'text' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'comment' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'content' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'status' => (string) ($row['status'] ?? 'pending'),
            'article' => (string) ($row['news_title'] ?? ''),
            'news_title' => (string) ($row['news_title'] ?? ''),
            'date' => (string) ($row['created_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
