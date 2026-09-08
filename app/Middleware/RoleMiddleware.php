<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class RoleMiddleware implements Middleware
{
    /** @param array<string> $allowed */
    public function __construct(private array $allowed = ['superadmin', 'admin']) {}

    public function handle(Request $request, Response $response, callable $next): void
    {
        $user = Session::get('_auth_user');
        $role = $user['role'] ?? '';
        if (!in_array($role, $this->allowed, true)) {
            $response->json(['error' => 'Insufficient privileges'], 403);
            return;
        }
        $next($request, $response);
    }
}
