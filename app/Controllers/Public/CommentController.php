<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Models\News;
use App\Models\NewsComment;
use Throwable;

final class CommentController
{
    public function __construct(private ?News $news, private ?NewsComment $comments)
    {
    }

    /** @param array{slug?: string} $params */
    public function index(Request $request, Response $response, array $params): void
    {
        $item = $this->findNews($params['slug'] ?? '');
        if ($item === null || !$this->comments instanceof NewsComment) {
            $response->json(['message' => 'News not found'], 404);
            return;
        }

        try {
            $response->json(['data' => $this->comments->forNews((int) $item['id'])]);
        } catch (Throwable) {
            $response->json(['data' => []]);
        }
    }

    /** @param array{slug?: string} $params */
    public function store(Request $request, Response $response, array $params): void
    {
        $item = $this->findNews($params['slug'] ?? '');
        if ($item === null || !$this->comments instanceof NewsComment) {
            $response->json(['message' => 'News not found'], 404);
            return;
        }

        $payload = $request->json();
        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? $payload['content'] ?? ''));
        if ($name === '' || $email === '' || $comment === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json(['message' => 'Invalid comment payload'], 422);
            return;
        }

        // Sanitize: strip HTML tags to prevent stored XSS, enforce length limits
        $name = strip_tags(mb_substr($name, 0, 100));
        $email = mb_substr($email, 0, 120);
        $comment = strip_tags(mb_substr($comment, 0, 5000));

        if ($name === '' || $comment === '') {
            $response->json(['message' => 'Invalid comment payload'], 422);
            return;
        }

        try {
            $created = $this->comments->create([
                'news_id' => (int) $item['id'],
                'name' => $name,
                'email' => $email,
                'content' => $comment,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $response->json(['ok' => true, 'message' => 'Komentar masuk antrean moderasi.', 'data' => $created], 201);
        } catch (Throwable) {
            $response->json(['message' => 'Comment could not be saved'], 500);
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
