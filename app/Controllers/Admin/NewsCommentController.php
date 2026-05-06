<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\NewsComment;
use Throwable;

final class NewsCommentController
{
    public function __construct(private ?NewsComment $comments)
    {
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->comments instanceof NewsComment) {
            $response->json(['data' => []], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
            return;
        }

        try {
            $response->json(['data' => $this->comments->paginateForAdmin([
                'status' => $request->query('status'),
            ])], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
        } catch (Throwable) {
            $response->json(['data' => []], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
        }
    }

    /** @param array{id?: string} $params */
    public function action(Request $request, Response $response, array $params, string $action): void
    {
        if (!$this->comments instanceof NewsComment) {
            $response->json(['ok' => true, 'mode' => 'fallback'], 200, ['X-Robots-Tag' => 'noindex, nofollow']);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $user = Session::get('_auth_user');
        $moderatorId = (int) ($user['id'] ?? 0);
        $ok = false;

        try {
            $ok = match ($action) {
                'approve' => $this->comments->approve($id, $moderatorId ?: null),
                'reject' => $this->comments->reject($id, $moderatorId ?: null),
                'delete' => $this->comments->delete($id),
                default => false,
            };
        } catch (Throwable) {
            $ok = false;
        }

        $response->json(['ok' => $ok, 'id' => $id, 'action' => $action], $ok ? 200 : 404, ['X-Robots-Tag' => 'noindex, nofollow']);
    }
}
