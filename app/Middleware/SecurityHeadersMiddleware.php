<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Adds security headers to all responses.
 */
final class SecurityHeadersMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        // Set security headers before the response is sent
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $next($request, $response);
    }
}
