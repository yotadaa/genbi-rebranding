<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/HotReload.php';

if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'GenBI CMS requires PHP 8.2 or newer. Current PHP version: ' . PHP_VERSION;
    return;
}

if (!extension_loaded('pdo_mysql')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'GenBI CMS requires the PHP pdo_mysql extension. Enable pdo_mysql in cPanel PHP extensions.';
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$rootPath = dirname(__DIR__);

if (\App\Core\HotReload::enabled() && $path === \App\Core\HotReload::endpoint()) {
    http_response_code(200);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode([
        'token' => \App\Core\HotReload::token($rootPath),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return;
}

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
    $assetPath = realpath(__DIR__ . $path) ?: realpath(dirname(__DIR__) . $path);
    $assetRoot = realpath(__DIR__ . '/assets');
    $legacyAssetRoot = realpath(dirname(__DIR__) . '/assets');

    if (
        $assetPath !== false
        && is_file($assetPath)
        && (($assetRoot !== false && str_starts_with($assetPath, $assetRoot)) || ($legacyAssetRoot !== false && str_starts_with($assetPath, $legacyAssetRoot)))
    ) {
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

if (str_starts_with($path, '/uploads/')) {
    $uploadPath = realpath(__DIR__ . $path) ?: realpath(__DIR__ . '/public' . $path);
    $uploadRoot = realpath(__DIR__ . '/uploads');

    if ($uploadPath !== false && $uploadRoot !== false && str_starts_with($uploadPath, $uploadRoot) && is_file($uploadPath)) {
        $types = [
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $extension = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        readfile($uploadPath);
        return;
    }

    http_response_code(404);
    echo 'Upload not found';
    return;
}

[$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

$router->dispatch($request, $response);
