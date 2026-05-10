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
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' https:; font-src 'self' https: data:; connect-src 'self'; frame-src https://www.google.com https://google.com https://maps.google.com; object-src 'none'; form-action 'self'");

        // HSTS — only effective over HTTPS in production
        if (!empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Public page caching (admin routes handled separately)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (!str_starts_with($uri, '/admin') && !str_starts_with($uri, '/login') && !str_starts_with($uri, '/api/')) {
            header('Cache-Control: public, max-age=60, s-maxage=300');
        }

        $next($request, $response);
    }
}
