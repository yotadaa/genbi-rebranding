<?php

declare(strict_types=1);

namespace App\Core;

final class StaticPageRenderer
{
    public function __construct(private string $rootPath)
    {
    }

    public function render(string $relativeFile, array $options = []): string
    {
        $file = $this->rootPath . '/' . ltrim($relativeFile, '/');
        if (!is_file($file)) {
            return '<!doctype html><title>404</title><h1>404 - Halaman tidak ditemukan</h1>';
        }

        $html = file_get_contents($file);
        if (!is_string($html)) {
            return '<!doctype html><title>500</title><h1>Konten tidak dapat dibaca</h1>';
        }

        $prefix = str_starts_with($relativeFile, 'admin/') ? '../' : '';
        $html = str_replace('href="' . $prefix . 'assets/', 'href="/assets/', $html);
        $html = str_replace('src="' . $prefix . 'assets/', 'src="/assets/', $html);

        if (!empty($options['meta']) && is_string($options['meta'])) {
            // Remove existing <title>...</title> to avoid duplicate
            $html = preg_replace('/<title>[^<]*<\/title>/', '', $html, 1) ?? $html;
            // Remove existing <meta name="description"...> to avoid duplicate
            $html = preg_replace('/<meta\s+name="description"[^>]*>/', '', $html, 1) ?? $html;
            // Inject full meta block before </head>
            $html = str_replace('</head>', '  ' . $options['meta'] . PHP_EOL . '</head>', $html);
        }

        if (!empty($options['noindex'])) {
            $html = str_replace('</head>', '  <meta name="robots" content="noindex, nofollow" />' . PHP_EOL . '</head>', $html);
        }

        if (!empty($options['csrf_token']) && is_string($options['csrf_token'])) {
            $token = htmlspecialchars($options['csrf_token'], ENT_QUOTES, 'UTF-8');
            $html = str_replace('</head>', '  <meta name="csrf-token" content="' . $token . '" />' . PHP_EOL . '</head>', $html);
        }

        if (!empty($options['slug'])) {
            $slug = htmlspecialchars((string) $options['slug'], ENT_QUOTES, 'UTF-8');
            $html = str_replace('<body>', '<body data-route-slug="' . $slug . '">', $html);
        }

        return $html;
    }
}
