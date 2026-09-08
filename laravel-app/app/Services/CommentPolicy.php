<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class CommentPolicy
{
    public function __construct(private Setting $settings)
    {
    }

    /** @param array<string, mixed> $news */
    public function forNews(array $news): array
    {
        return [
            'comments_enabled' => $this->resolveBool($news['comments_enabled'] ?? null, 'comments.enabled', true),
            'voting_enabled' => $this->resolveBool($news['voting_enabled'] ?? null, 'comments.voting_enabled', true),
            'replies_enabled' => $this->resolveBool($news['replies_enabled'] ?? null, 'comments.replies_enabled', true),
            'max_reply_depth' => max(1, min(10, $this->resolveInt($news['max_reply_depth'] ?? null, 'comments.max_reply_depth', 3))),
            'replies_require_moderation' => $this->resolveBool(null, 'comments.replies_require_moderation', true),
            'root_sort' => (string) $this->settings->get('comments.root_sort', 'newest_first'),
            'reply_sort' => (string) $this->settings->get('comments.reply_sort', 'oldest_first'),
            'rate_limit_per_ip_per_15min' => max(1, (int) $this->settings->get('comments.rate_limit_per_ip_per_15min', 20)),
            'vote_rate_limit_per_ip_per_15min' => max(1, (int) $this->settings->get('comments.vote_rate_limit_per_ip_per_15min', 60)),
        ];
    }

    public static function hashVoter(string $ip, string $ua, string $salt): string
    {
        return hash('sha256', trim($ip) . '|' . trim($ua) . '|' . $salt);
    }

    private function resolveBool(mixed $override, string $key, bool $default): bool
    {
        if ($override !== null && $override !== '') {
            return in_array((string) $override, ['1', 'true', 'on', 'yes'], true);
        }

        return (bool) $this->settings->get($key, $default);
    }

    private function resolveInt(mixed $override, string $key, int $default): int
    {
        if ($override !== null && $override !== '') {
            return (int) $override;
        }

        return (int) $this->settings->get($key, $default);
    }
}
