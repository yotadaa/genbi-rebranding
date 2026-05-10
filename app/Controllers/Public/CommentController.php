<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\NewsCommentVote;
use App\Services\CommentPolicy;
use App\Services\CommentThrottleService;
use Throwable;

final class CommentController
{
    public function __construct(
        private ?News $news,
        private ?NewsComment $comments,
        private ?NewsCommentVote $votes = null,
        private ?CommentPolicy $policy = null,
        private ?CommentThrottleService $throttle = null,
    )
    {
    }

    /** @param array{slug?: string} $params */
    public function index(Request $request, Response $response, array $params): void
    {
        $item = $this->findNews($params['slug'] ?? '');
        if ($item === null || !$this->comments instanceof NewsComment || !$this->policy instanceof CommentPolicy) {
            $response->json(['message' => 'News not found'], 404);
            return;
        }

        try {
            $policy = $this->policy->forNews($item);
            $salt = Env::get('COMMENT_VOTE_SALT', '');
            $voterKey = $salt !== '' ? CommentPolicy::hashVoter((string) ($request->ip() ?? ''), (string) ($request->userAgent() ?? ''), $salt) : '';
            $votes = $voterKey !== '' && $this->votes instanceof NewsCommentVote
                ? $this->votes->valuesForNews((int) $item['id'], $voterKey)
                : [];

            $response->json([
                'data' => $this->comments->treeForNews((int) $item['id'], (string) $policy['root_sort'], (string) $policy['reply_sort'], (int) $policy['max_reply_depth']),
                'policy' => $policy,
                'voter' => ['votes' => $votes],
            ]);
        } catch (Throwable) {
            $response->json(['data' => [], 'policy' => [], 'voter' => ['votes' => []]]);
        }
    }

    /** @param array{slug?: string} $params */
    public function store(Request $request, Response $response, array $params): void
    {
        $item = $this->findNews($params['slug'] ?? '');
        if ($item === null || !$this->comments instanceof NewsComment || !$this->policy instanceof CommentPolicy || !$this->throttle instanceof CommentThrottleService) {
            $response->json(['message' => 'News not found'], 404);
            return;
        }

        $payload = $request->json();
        $policy = $this->policy->forNews($item);
        if (!$policy['comments_enabled']) {
            $response->json(['message' => 'Comments disabled for this article'], 403);
            return;
        }

        $ip = (string) ($request->ip() ?? '');
        if ($this->throttle->tooManyAttempts('comment-submit', $ip, (int) $policy['rate_limit_per_ip_per_15min'])) {
            $response->json(['message' => 'Too many submissions, try again later'], 429);
            return;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? $payload['content'] ?? ''));
        $parentId = isset($payload['parent_id']) ? (int) $payload['parent_id'] : 0;
        if ($name === '' || $email === '' || $comment === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json(['message' => 'Invalid comment payload'], 422);
            return;
        }

        if ($parentId > 0) {
            if (!$policy['replies_enabled']) {
                $response->json(['message' => 'Replies disabled for this article'], 403);
                return;
            }

            if (!$this->comments->existsApprovedForNews((int) $item['id'], $parentId)) {
                $response->json(['message' => 'Invalid parent_id'], 422);
                return;
            }

            $depth = $this->comments->depthForComment($parentId) + 1;
            if ($depth > (int) $policy['max_reply_depth']) {
                $response->json(['message' => 'Reply depth exceeded'], 422);
                return;
            }
        }

        // Sanitize: strip HTML tags to prevent stored XSS, enforce length limits
        $name = strip_tags(mb_substr($name, 0, 100));
        $email = mb_substr($email, 0, 120);
        $comment = strip_tags(mb_substr($comment, 0, 5000));
        $website = isset($payload['website']) ? strip_tags(mb_substr(trim((string) $payload['website']), 0, 180)) : null;

        if ($name === '' || $comment === '') {
            $response->json(['message' => 'Invalid comment payload'], 422);
            return;
        }

        try {
            $created = $this->comments->create([
                'news_id' => (int) $item['id'],
                'parent_id' => $parentId > 0 ? $parentId : null,
                'name' => $name,
                'email' => $email,
                'website' => $website,
                'content' => $comment,
                'status' => $parentId > 0 && !$policy['replies_require_moderation'] ? 'approved' : 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $this->throttle->recordAttempt('comment-submit', $ip);
            $response->json(['ok' => true, 'message' => 'Komentar masuk antrean moderasi.', 'data' => $created], 201);
        } catch (Throwable) {
            $response->json(['message' => 'Comment could not be saved'], 500);
        }
    }

    /** @param array{slug?: string,id?: string} $params */
    public function vote(Request $request, Response $response, array $params): void
    {
        $item = $this->findNews($params['slug'] ?? '');
        $commentId = (int) ($params['id'] ?? 0);
        if ($item === null || $commentId < 1 || !$this->comments instanceof NewsComment || !$this->votes instanceof NewsCommentVote || !$this->policy instanceof CommentPolicy || !$this->throttle instanceof CommentThrottleService) {
            $response->json(['message' => 'News not found'], 404);
            return;
        }

        $policy = $this->policy->forNews($item);
        if (!$policy['voting_enabled']) {
            $response->json(['message' => 'Voting disabled for this article'], 403);
            return;
        }

        if (!$this->comments->existsApprovedForNews((int) $item['id'], $commentId)) {
            $response->json(['message' => 'Comment not found'], 404);
            return;
        }

        $ip = (string) ($request->ip() ?? '');
        if ($this->throttle->tooManyAttempts('comment-vote', $ip, (int) $policy['vote_rate_limit_per_ip_per_15min'])) {
            $response->json(['message' => 'Too many votes, try again later'], 429);
            return;
        }

        $payload = $request->json();
        $value = $payload['value'] ?? null;
        if (!in_array($value, [-1, 0, 1, '-1', '0', '1'], true)) {
            $response->json(['message' => 'Invalid vote value'], 422);
            return;
        }

        $salt = Env::get('COMMENT_VOTE_SALT', 'genbi-comment-vote-local-fallback');

        try {
            $voterKey = CommentPolicy::hashVoter($ip, (string) ($request->userAgent() ?? ''), $salt);
            $this->votes->upsert($commentId, (int) $item['id'], $voterKey, (int) $value, $ip, $request->userAgent());
            $this->throttle->recordAttempt('comment-vote', $ip);
            $counts = $this->votes->countsForNews((int) $item['id']);
            $current = $counts[$commentId] ?? ['up' => 0, 'down' => 0, 'score' => 0];
            $response->json(['ok' => true, 'data' => [...$current, 'myValue' => $this->votes->currentValue($commentId, $voterKey)]]);
        } catch (Throwable) {
            $response->json(['message' => 'Vote could not be saved'], 500);
        }
    }

    /** @return array<string, mixed>|null */
    private function findNews(string $slug): ?array
    {
        if (!$this->news instanceof News) {
            return null;
        }

        try {
            return $this->news->findPublicBySlug($slug);
        } catch (Throwable) {
            return null;
        }
    }
}
