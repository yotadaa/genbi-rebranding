<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Serve static public files (robots.txt, favicon.ico, etc.)
$staticFiles = ['robots.txt', 'favicon.ico', 'manifest.webmanifest', 'browserconfig.xml'];
$basename = ltrim($path, '/');
if (in_array($basename, $staticFiles, true) && is_file(__DIR__ . '/' . $basename)) {
    $mimeMap = [
        'txt' => 'text/plain; charset=UTF-8',
        'ico' => 'image/x-icon',
        'webmanifest' => 'application/manifest+json',
        'xml' => 'application/xml; charset=UTF-8',
    ];
    $ext = pathinfo($basename, PATHINFO_EXTENSION);
    header('Content-Type: ' . ($mimeMap[$ext] ?? 'application/octet-stream'));
    readfile(__DIR__ . '/' . $basename);
    return;
}

if (str_starts_with($path, '/assets/')) {
    $assetPath = realpath(__DIR__ . $path);
    $assetRoot = realpath(__DIR__ . '/assets');

    if ($assetPath !== false && $assetRoot !== false && str_starts_with($assetPath, $assetRoot) && is_file($assetPath)) {
        $types = [
            'css' => 'text/css; charset=UTF-8',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'js' => 'text/javascript; charset=UTF-8',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=3600');
        readfile($assetPath);
        return;
    }

    http_response_code(404);
    echo 'Asset not found';
    return;
}

[$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';

// Security headers for all dynamic responses
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

$router->dispatch($request, $response);
