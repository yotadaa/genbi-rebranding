<?php

declare(strict_types=1);

use App\Controllers\Admin\FeatureController;
use App\Models\Feature;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_feature_image(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$feature = new Feature(null);
$normalizePath = new ReflectionMethod(Feature::class, 'normalizeImagePath');
$normalizePath->setAccessible(true);

expect_feature_image(
    $normalizePath->invoke($feature, '/uploads/features/safe.webp') === '/uploads/features/safe.webp',
    'Safe feature upload paths should remain usable.'
);
expect_feature_image(
    $normalizePath->invoke($feature, 'safe.webp') === '/uploads/features/safe.webp',
    'Bare feature filenames should normalize into the feature upload directory.'
);
expect_feature_image(
    $normalizePath->invoke($feature, 'https://cdn.example.test/feature.webp') === 'https://cdn.example.test/feature.webp',
    'External feature image URLs should remain non-local references.'
);

$unsafePaths = [
    '/uploads/features/../../index.php',
    '/uploads/features/%2e%2e/%2e%2e/index.php',
    '/uploads/features/..\\..\\index.php',
    '/uploads/features_evil/../features/safe.webp',
    "feature-\0.webp",
];

foreach ($unsafePaths as $unsafePath) {
    expect_feature_image(
        $normalizePath->invoke($feature, $unsafePath) === '',
        'Unsafe feature image path should be rejected: ' . $unsafePath
    );
}

$publicDir = dirname(__DIR__, 2) . '/public';
$uploadDir = $publicDir . '/uploads/features';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$safeFile = $uploadDir . '/security-delete-safe.txt';
$outsideFile = $publicDir . '/security-delete-outside.txt';
file_put_contents($safeFile, 'safe');
file_put_contents($outsideFile, 'outside');

$controller = new FeatureController(null);
$removeUploadedFile = new ReflectionMethod(FeatureController::class, 'removeUploadedFile');
$removeUploadedFile->setAccessible(true);

try {
    $removeUploadedFile->invoke($controller, '/uploads/features/../../security-delete-outside.txt');
    expect_feature_image(is_file($outsideFile), 'Traversal delete must not remove files outside public/uploads/features.');

    $removeUploadedFile->invoke($controller, '/uploads/features/security-delete-safe.txt');
    expect_feature_image(!is_file($safeFile), 'Safe feature upload file should still be removable.');
} finally {
    if (is_file($safeFile)) {
        @unlink($safeFile);
    }
    if (is_file($outsideFile)) {
        @unlink($outsideFile);
    }
}

echo "PHP feature image security tests passed\n";
