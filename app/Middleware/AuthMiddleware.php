<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if (!Session::has('_auth_user')) {
            if ($request->acceptsJson()) {
                $response->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $response->redirect('/admin/login');
            return;
        }

        $next($request, $response);
    }
}
