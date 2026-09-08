<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Services\CsrfService;

final class CsrfMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if ($request->method() !== 'POST') {
            $next($request, $response);
            return;
        }

        // Prefer the AJAX header so JSON bodies are not needed for token lookup.
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // Fallback to form body or JSON body for non-AJAX submissions.
        if ($token === null) {
            $token = $_POST['_csrf_token'] ?? null;
        }
        if ($token === null) {
            $json = $request->json();
            $token = $json['_csrf_token'] ?? null;
        }

        if (!CsrfService::validate($token)) {
            if ($request->acceptsJson()) {
                $response->json(['error' => 'CSRF token invalid'], 403);
                return;
            }
            $response->html('<!doctype html><title>403</title><h1>403 - Forbidden</h1><p>CSRF token tidak valid. Silakan muat ulang halaman.</p>', 403);
            return;
        }

        $next($request, $response);
    }
}
