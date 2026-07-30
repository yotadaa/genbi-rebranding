<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/HotReload.php';
require_once dirname(__DIR__) . '/app/Core/Request.php';
require_once dirname(__DIR__) . '/app/Core/Response.php';
require_once dirname(__DIR__) . '/app/Core/ViewRenderer.php';
require_once dirname(__DIR__) . '/app/Core/ErrorHandler.php';
require_once dirname(__DIR__) . '/app/Config/App.php';
require_once dirname(__DIR__) . '/app/Core/Env.php';

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
\App\Core\Env::load($rootPath . '/.env');

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
    // public/assets is the only deployable and editable asset directory.
    $assetPath = realpath(__DIR__ . $path);
    $assetRoot = realpath(__DIR__ . '/assets');

    if (
        $assetPath !== false
        && is_file($assetPath)
        && $assetRoot !== false
        && str_starts_with($assetPath, $assetRoot)
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
        // Source assets are deployed directly. Revalidate them so a CSS/JS upload is
        // visible without generating a new minified bundle or manually changing a hash.
        header('Cache-Control: no-cache, must-revalidate');
        readfile($assetPath);
        return;
    }

    http_response_code(404);
    \App\Core\ErrorHandler::log('Asset not found', ['path' => $path]);
    \App\Core\ErrorHandler::render(new \App\Core\Response(), 404, 'Asset tidak ditemukan', 'File aset yang diminta tidak tersedia.');
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
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        readfile($uploadPath);
        return;
    }

    \App\Core\ErrorHandler::log('Upload not found', ['path' => $path]);
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($extension, ['gif', 'jpg', 'jpeg', 'png', 'webp', 'svg'], true)) {
        http_response_code(404);
        header('Content-Type: image/svg+xml; charset=UTF-8');
        header('Cache-Control: public, max-age=300');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="Gambar tidak tersedia"><rect width="1200" height="630" fill="#edf4fb"/><circle cx="600" cy="260" r="72" fill="#114b9a" opacity=".16"/><path d="M520 356h160l-46-60-34 42-24-30-56 48Z" fill="#114b9a" opacity=".45"/><text x="600" y="430" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#114b9a">Gambar tidak tersedia</text></svg>';
        return;
    }

    \App\Core\ErrorHandler::render(new \App\Core\Response(), 404, 'Upload tidak ditemukan', 'File unggahan yang diminta tidak tersedia.');
    return;
}

try {
    [$router, $request, $response] = require dirname(__DIR__) . '/bootstrap/app.php';
    $router->dispatch($request, $response);
} catch (\Throwable $error) {
    \App\Core\ErrorHandler::renderThrowable(new \App\Core\Response(), $error);
}
