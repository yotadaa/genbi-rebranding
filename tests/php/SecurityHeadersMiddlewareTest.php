<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Middleware\SecurityHeadersMiddleware;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

header_remove();

$middleware = new SecurityHeadersMiddleware();
$middleware->handle(new Request(), new Response(), static function (): void {
});

$headers = headers_list();

assert(in_array('X-Content-Type-Options: nosniff', $headers, true));
assert(in_array('X-Frame-Options: DENY', $headers, true));
assert(in_array('Referrer-Policy: strict-origin-when-cross-origin', $headers, true));

$hasCsp = false;
foreach ($headers as $header) {
    if (str_starts_with($header, 'Content-Security-Policy: ')) {
        $hasCsp = true;
        assert(str_contains($header, "default-src 'self'"));
        assert(str_contains($header, "frame-ancestors 'none'"));
        assert(str_contains($header, 'https://cdn.jsdelivr.net'));
    }
}

assert($hasCsp === true);

echo "PHP security headers middleware tests passed\n";
