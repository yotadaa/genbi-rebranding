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
        $html = $this->loadHtml($relativeFile, $options);
        if ($html === null) {
            return '<!doctype html><title>500</title><h1>Konten tidak dapat dibaca</h1>';
        }

        return $html;
    }

    /** @return array{title: string, content: string, scripts: string, cmsPage: string, cmsMode: string}|null */
    public function extractAdminPage(string $relativeFile, array $options = []): ?array
    {
        $html = $this->loadHtml($relativeFile, $options);
        if ($html === null) {
            return null;
        }

        preg_match('/<title>(.*?)<\/title>/si', $html, $titleMatch);
        preg_match('/<body[^>]*data-cms-page="([^"]*)"[^>]*data-cms-mode="([^"]*)"[^>]*>/si', $html, $bodyMatch);
        preg_match('/<main[^>]*>(.*?)<\/main>/si', $html, $contentMatch);
        preg_match_all('/<script\b[^>]*>.*?<\/script>/si', $html, $scriptMatches);

        $scripts = array_filter($scriptMatches[0] ?? [], static function (string $script): bool {
            return !str_contains($script, '/assets/js/data.js')
                && !str_contains($script, '/assets/js/api-core.js')
                && !str_contains($script, '/assets/js/api.js')
                && !str_contains($script, '/assets/js/app.js')
                && !str_contains($script, '/assets/js/lib/ui.js')
                && !str_contains($script, '/assets/js/admin/admin.js');
        });

        $scripts = array_map(static function (string $script): string {
            $script = str_replace(
                ['../assets/js/admin/cms.js', '/assets/js/admin/cms.js'],
                '/assets/js/dist/admin/cms.js?v=20260519f',
                $script
            );

            if (str_contains($script, ' src=') && !preg_match('/\sdefer\b/i', $script)) {
                return preg_replace('/<script\b/i', '<script defer', $script, 1) ?? $script;
            }

            return $script;
        }, $scripts);

        return [
            'title' => trim(strip_tags($titleMatch[1] ?? 'Admin GenBI')),
            'content' => trim($contentMatch[1] ?? ''),
            'scripts' => implode('', $scripts),
            'cmsPage' => $bodyMatch[1] ?? '',
            'cmsMode' => $bodyMatch[2] ?? '',
        ];
    }

    private function loadHtml(string $relativeFile, array $options): ?string
    {
        $file = $this->rootPath . '/' . ltrim($relativeFile, '/');
        if (!is_file($file)) {
            return '<!doctype html><title>404</title><h1>404 - Halaman tidak ditemukan</h1>';
        }

        $html = file_get_contents($file);
        if (!is_string($html)) {
            return null;
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

        if (!empty($options['jsonld']) && is_string($options['jsonld'])) {
            $html = str_replace('</head>', '  ' . $options['jsonld'] . PHP_EOL . '</head>', $html);
        }

        if (!empty($options['slug'])) {
            $slug = htmlspecialchars((string) $options['slug'], ENT_QUOTES, 'UTF-8');
            $html = str_replace('<body>', '<body data-route-slug="' . $slug . '">', $html);
        }

        return $html;
    }
}
