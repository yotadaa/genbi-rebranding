<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Session;
use PDO;
use Throwable;

final class NewsCommentVote
{
    private const SESSION_KEY = '_comment_vote_fallback';

    public function __construct(private PDO $db)
    {
    }

    public function upsert(int $commentId, int $newsId, string $voterKey, int $value, ?string $ip, ?string $ua): void
    {
        $value = max(-1, min(1, $value));

        try {
            $current = $this->db->prepare('SELECT vote_id FROM tbl_news_comment_vote WHERE comment_id = :comment_id AND voter_key = :voter_key LIMIT 1');
            $current->execute([
                ':comment_id' => $commentId,
                ':voter_key' => $voterKey,
            ]);

            $existingId = $current->fetchColumn();

            if ($existingId !== false) {
                $statement = $this->db->prepare('UPDATE tbl_news_comment_vote SET news_id = :news_id, value = :value, ip_address = :ip_address, user_agent = :user_agent, updated_at = CURRENT_TIMESTAMP WHERE vote_id = :vote_id');
                $statement->execute([
                    ':vote_id' => (int) $existingId,
                    ':news_id' => $newsId,
                    ':value' => $value,
                    ':ip_address' => $ip,
                    ':user_agent' => $ua,
                ]);
                return;
            }

            $statement = $this->db->prepare('INSERT INTO tbl_news_comment_vote (comment_id, news_id, voter_key, value, ip_address, user_agent, created_at, updated_at) VALUES (:comment_id, :news_id, :voter_key, :value, :ip_address, :user_agent, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
            $statement->execute([
                ':comment_id' => $commentId,
                ':news_id' => $newsId,
                ':voter_key' => $voterKey,
                ':value' => $value,
                ':ip_address' => $ip,
                ':user_agent' => $ua,
            ]);
        } catch (Throwable) {
            $this->storeFallbackVote($commentId, $newsId, $voterKey, $value);
        }
    }

    public function currentValue(int $commentId, string $voterKey): int
    {
        try {
            $statement = $this->db->prepare('SELECT value FROM tbl_news_comment_vote WHERE comment_id = :comment_id AND voter_key = :voter_key LIMIT 1');
            $statement->execute([
                ':comment_id' => $commentId,
                ':voter_key' => $voterKey,
            ]);

            $value = $statement->fetchColumn();

            return $value === false ? 0 : (int) $value;
        } catch (Throwable) {
            return $this->fallbackCurrentValue($commentId, $voterKey);
        }
    }

    /** @return array<int, int> */
    public function valuesForNews(int $newsId, string $voterKey): array
    {
        try {
            $statement = $this->db->prepare('SELECT comment_id, value FROM tbl_news_comment_vote WHERE news_id = :news_id AND voter_key = :voter_key');
            $statement->execute([
                ':news_id' => $newsId,
                ':voter_key' => $voterKey,
            ]);

            $values = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $values[(int) $row['comment_id']] = (int) $row['value'];
            }

            return $values;
        } catch (Throwable) {
            return $this->fallbackValuesForNews($newsId, $voterKey);
        }
    }

    /** @return array<int, array{up:int, down:int, score:int}> */
    public function countsForNews(int $newsId): array
    {
        try {
            $statement = $this->db->prepare('SELECT comment_id, SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) AS up_votes, SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) AS down_votes, SUM(CASE WHEN value = 1 THEN 1 WHEN value = -1 THEN -1 ELSE 0 END) AS score FROM tbl_news_comment_vote WHERE news_id = :news_id GROUP BY comment_id');
            $statement->execute([':news_id' => $newsId]);

            $counts = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $counts[(int) $row['comment_id']] = [
                    'up' => (int) ($row['up_votes'] ?? 0),
                    'down' => (int) ($row['down_votes'] ?? 0),
                    'score' => (int) ($row['score'] ?? 0),
                ];
            }

            return $counts;
        } catch (Throwable) {
            return $this->fallbackCountsForNews($newsId);
        }
    }

    private function storeFallbackVote(int $commentId, int $newsId, string $voterKey, int $value): void
    {
        if ($voterKey === '') {
            return;
        }

        $store = Session::get(self::SESSION_KEY, []);
        if (!is_array($store)) {
            $store = [];
        }

        $newsKey = (string) $newsId;
        $commentKey = (string) $commentId;
        if (!isset($store[$newsKey]) || !is_array($store[$newsKey])) {
            $store[$newsKey] = [];
        }
        if (!isset($store[$newsKey][$commentKey]) || !is_array($store[$newsKey][$commentKey])) {
            $store[$newsKey][$commentKey] = [];
        }

        $store[$newsKey][$commentKey][$voterKey] = max(-1, min(1, $value));
        $_SESSION[self::SESSION_KEY] = $store;
    }

    private function fallbackCurrentValue(int $commentId, string $voterKey): int
    {
        if ($voterKey === '') {
            return 0;
        }

        $store = Session::get(self::SESSION_KEY, []);
        if (!is_array($store)) {
            return 0;
        }

        foreach ($store as $comments) {
            if (!is_array($comments)) {
                continue;
            }

            $values = $comments[(string) $commentId] ?? null;
            if (is_array($values) && array_key_exists($voterKey, $values)) {
                return (int) $values[$voterKey];
            }
        }

        return 0;
    }

    /** @return array<int, int> */
    private function fallbackValuesForNews(int $newsId, string $voterKey): array
    {
        if ($voterKey === '') {
            return [];
        }

        $store = Session::get(self::SESSION_KEY, []);
        $comments = is_array($store) ? ($store[(string) $newsId] ?? []) : [];
        if (!is_array($comments)) {
            return [];
        }

        $values = [];
        foreach ($comments as $commentId => $voters) {
            if (is_array($voters) && array_key_exists($voterKey, $voters)) {
                $values[(int) $commentId] = (int) $voters[$voterKey];
            }
        }

        return $values;
    }

    /** @return array<int, array{up:int, down:int, score:int}> */
    private function fallbackCountsForNews(int $newsId): array
    {
        $store = Session::get(self::SESSION_KEY, []);
        $comments = is_array($store) ? ($store[(string) $newsId] ?? []) : [];
        if (!is_array($comments)) {
            return [];
        }

        $counts = [];
        foreach ($comments as $commentId => $voters) {
            if (!is_array($voters)) {
                continue;
            }

            $up = 0;
            $down = 0;
            $score = 0;
            foreach ($voters as $vote) {
                $intVote = (int) $vote;
                if ($intVote === 1) {
                    $up++;
                    $score++;
                }
                if ($intVote === -1) {
                    $down++;
                    $score--;
                }
            }

            $counts[(int) $commentId] = [
                'up' => $up,
                'down' => $down,
                'score' => $score,
            ];
        }

        return $counts;
    }
}
