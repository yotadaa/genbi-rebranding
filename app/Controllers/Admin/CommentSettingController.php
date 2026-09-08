<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Models\Setting;
use App\Services\CsrfService;

final class CommentSettingController
{
    private const ALLOWED_KEYS = [
        'comments.enabled' => 'bool',
        'comments.voting_enabled' => 'bool',
        'comments.replies_enabled' => 'bool',
        'comments.max_reply_depth' => 'int',
        'comments.replies_require_moderation' => 'bool',
        'comments.root_sort' => 'string',
        'comments.reply_sort' => 'string',
        'comments.rate_limit_per_ip_per_15min' => 'int',
        'comments.vote_rate_limit_per_ip_per_15min' => 'int',
    ];

    public function __construct(private ?Setting $settings = null, private ?ViewRenderer $viewRenderer = null)
    {
    }

    public function show(Request $request, Response $response): void
    {
        $data = $this->defaults();
        if ($this->settings instanceof Setting) {
            foreach (array_keys(self::ALLOWED_KEYS) as $key) {
                $data[$key] = $this->settings->get($key, $data[$key]);
            }
        }

        if (!$request->acceptsJson() && $this->viewRenderer instanceof ViewRenderer) {
            $response->html($this->viewRenderer->renderWithLayout('admin/comment-setting/index.php', 'layouts/admin.php', [
                'title' => 'Comment Settings | Admin GenBI',
                'csrfToken' => CsrfService::token(),
                'cmsPage' => 'comment-setting',
                'cmsMode' => 'list',
                'settingsData' => $data,
                'scripts' => '<script defer src="/assets/js/admin/cms.js"></script>',
            ]), 200, ['X-Robots-Tag' => 'noindex, nofollow']);
            return;
        }

        $response->json(['data' => $data]);
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->settings instanceof Setting) {
            $response->json(['error' => 'Comment settings tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $clean = [];

        foreach (self::ALLOWED_KEYS as $key => $type) {
            if (!array_key_exists($key, $body)) {
                continue;
            }

            $clean[$key] = match ($type) {
                'bool' => in_array($body[$key], [true, 1, '1', 'true', 'on'], true),
                'int' => (int) $body[$key],
                default => (string) $body[$key],
            };
        }

        if (isset($clean['comments.max_reply_depth'])) {
            $clean['comments.max_reply_depth'] = max(1, min(10, (int) $clean['comments.max_reply_depth']));
        }

        if (isset($clean['comments.root_sort']) && !in_array($clean['comments.root_sort'], ['newest_first', 'oldest_first', 'top_voted'], true)) {
            $clean['comments.root_sort'] = 'newest_first';
        }

        if (isset($clean['comments.reply_sort']) && !in_array($clean['comments.reply_sort'], ['newest_first', 'oldest_first', 'top_voted'], true)) {
            $clean['comments.reply_sort'] = 'oldest_first';
        }

        $this->settings->putMany($clean);
        $response->json(['data' => $clean]);
    }

    /** @return array<string, mixed> */
    private function defaults(): array
    {
        return [
            'comments.enabled' => true,
            'comments.voting_enabled' => true,
            'comments.replies_enabled' => true,
            'comments.max_reply_depth' => 3,
            'comments.replies_require_moderation' => true,
            'comments.root_sort' => 'newest_first',
            'comments.reply_sort' => 'oldest_first',
            'comments.rate_limit_per_ip_per_15min' => 20,
            'comments.vote_rate_limit_per_ip_per_15min' => 60,
        ];
    }
}
