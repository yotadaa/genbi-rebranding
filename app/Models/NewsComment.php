<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

final class NewsComment
{
    public function __construct(private PDO $db, private ?NewsCommentVote $votes = null)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function forNews(int $newsId): array
    {
        $statement = $this->db->prepare("SELECT c.*, n.news_title FROM tbl_news_comment c LEFT JOIN tbl_news n ON n.news_id = c.news_id WHERE c.news_id = :news_id AND c.status = 'approved' AND c.deleted_at IS NULL ORDER BY c.created_at DESC");
        $statement->execute(['news_id' => $newsId]);

        return array_map([self::class, 'mapRow'], $statement->fetchAll());
    }

    /** @return array<int, array<string, mixed>> */
    public function treeForNews(int $newsId, string $rootSort = 'newest_first', string $replySort = 'oldest_first', int $maxDepth = 3): array
    {
        $statement = $this->db->prepare("SELECT c.*, n.news_title FROM tbl_news_comment c LEFT JOIN tbl_news n ON n.news_id = c.news_id WHERE c.news_id = :news_id AND c.status = 'approved' AND c.deleted_at IS NULL ORDER BY c.created_at ASC");
        $statement->execute(['news_id' => $newsId]);

        $rows = array_map([self::class, 'mapRow'], $statement->fetchAll());
        $voteCounts = $this->votes?->countsForNews($newsId) ?? [];
        $nodes = [];

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $counts = $voteCounts[$id] ?? ['up' => 0, 'down' => 0, 'score' => 0];
            $nodes[$id] = [
                ...$row,
                'up_votes' => $counts['up'],
                'down_votes' => $counts['down'],
                'score' => $counts['score'],
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($nodes as $id => &$node) {
            $parentId = (int) ($node['parent_id'] ?? 0);
            if ($parentId > 0 && isset($nodes[$parentId]) && $parentId !== $id) {
                $nodes[$parentId]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        $sortNodes = function (array &$items, string $sort, int $depth) use (&$sortNodes, $replySort, $maxDepth): void {
            usort($items, static function (array $a, array $b) use ($sort): int {
                return match ($sort) {
                    'oldest_first' => strcmp((string) ($a['created_at'] ?? ''), (string) ($b['created_at'] ?? '')),
                    'top_voted' => (($b['score'] ?? 0) <=> ($a['score'] ?? 0)) ?: strcmp((string) ($a['created_at'] ?? ''), (string) ($b['created_at'] ?? '')),
                    default => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')),
                };
            });

            foreach ($items as &$item) {
                $item['depth'] = $depth;
                if ($depth >= $maxDepth) {
                    continue;
                }
                $sortNodes($item['children'], $replySort, $depth + 1);
            }
            unset($item);
        };

        $sortNodes($tree, $rootSort, 0);

        return $tree;
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function create(array $data): array
    {
        $newsId = (int) $data['news_id'];
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;

        if ($parentId !== null && $parentId > 0 && $this->findApprovedParent($newsId, $parentId) === null) {
            throw new RuntimeException('Invalid parent_id');
        }

        if ($parentId !== null && $parentId <= 0) {
            $parentId = null;
        }

        $statement = $this->db->prepare('INSERT INTO tbl_news_comment (news_id, parent_id, name, email, website, content, status, ip_address, user_agent, created_at) VALUES (:news_id, :parent_id, :name, :email, :website, :content, :status, :ip_address, :user_agent, CURRENT_TIMESTAMP)');
        $statement->execute([
            'news_id' => $newsId,
            'parent_id' => $parentId,
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
            'parent_id' => $parentId,
            'status' => (string) ($data['status'] ?? 'pending'),
        ];
    }

    /** @param array<string, string|null> $filters @return array<int, array<string, mixed>> */
    public function paginateForAdmin(array $filters = [], int $limit = 50): array
    {
        $sql = 'SELECT c.*, n.news_title, p.content AS parent_content, p.name AS parent_name FROM tbl_news_comment c LEFT JOIN tbl_news n ON n.news_id = c.news_id LEFT JOIN tbl_news_comment p ON p.comment_id = c.parent_id WHERE c.deleted_at IS NULL';
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
            'parent_id' => isset($row['parent_id']) ? (int) $row['parent_id'] : null,
            'name' => (string) ($row['name'] ?? 'Pembaca'),
            'email' => (string) ($row['email'] ?? ''),
            'website' => (string) ($row['website'] ?? ''),
            'text' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'comment' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'content' => (string) ($row['content'] ?? $row['comment'] ?? ''),
            'status' => (string) ($row['status'] ?? 'pending'),
            'article' => (string) ($row['news_title'] ?? ''),
            'news_title' => (string) ($row['news_title'] ?? ''),
            'parent_excerpt' => (string) mb_substr(strip_tags((string) ($row['parent_content'] ?? '')), 0, 120),
            'parent_name' => (string) ($row['parent_name'] ?? ''),
            'date' => (string) ($row['created_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'up_votes' => (int) ($row['up_votes'] ?? 0),
            'down_votes' => (int) ($row['down_votes'] ?? 0),
            'score' => (int) ($row['score'] ?? (($row['up_votes'] ?? 0) - ($row['down_votes'] ?? 0))),
        ];
    }

    public function existsApprovedForNews(int $newsId, int $commentId): bool
    {
        $statement = $this->db->prepare("SELECT 1 FROM tbl_news_comment WHERE comment_id = :comment_id AND news_id = :news_id AND status = 'approved' AND deleted_at IS NULL LIMIT 1");
        $statement->execute([
            ':comment_id' => $commentId,
            ':news_id' => $newsId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function depthForComment(int $commentId): int
    {
        $depth = 0;
        $currentId = $commentId;

        while ($currentId > 0) {
            $statement = $this->db->prepare('SELECT parent_id FROM tbl_news_comment WHERE comment_id = :id AND deleted_at IS NULL LIMIT 1');
            $statement->execute([':id' => $currentId]);
            $parentId = $statement->fetchColumn();

            if ($parentId === false || $parentId === null) {
                break;
            }

            $depth++;
            $currentId = (int) $parentId;
            if ($depth > 20) {
                break;
            }
        }

        return $depth;
    }

    /** @return array<string, mixed>|null */
    private function findApprovedParent(int $newsId, int $parentId): ?array
    {
        $statement = $this->db->prepare("SELECT * FROM tbl_news_comment WHERE comment_id = :parent_id AND news_id = :news_id AND status = 'approved' AND deleted_at IS NULL LIMIT 1");
        $statement->execute([
            ':parent_id' => $parentId,
            ':news_id' => $newsId,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
