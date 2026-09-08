<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Models\Event;
use App\Models\News;
use App\Models\Prestasi;
use App\Services\SeoConfig;
use App\Services\SeoService;

class SitemapController
{
    public function __construct(
        private ?News $news = null,
        private ?Prestasi $prestasi = null,
        private ?Event $event = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $now = date('Y-m-d');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        $xml .= $this->sitemapEntry($base . '/sitemap-pages.xml', $now);
        $xml .= $this->sitemapEntry($base . '/sitemap-news.xml', $now);
        $xml .= $this->sitemapEntry($base . '/sitemap-events.xml', $now);
        $xml .= $this->sitemapEntry($base . '/sitemap-prestasi.xml', $now);
        $xml .= $this->sitemapEntry($base . '/sitemap-images.xml', $now);
        $xml .= '</sitemapindex>';

        $response->xml($xml);
    }

    public function pages(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $pages = SeoConfig::pages();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($pages as $config) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . $config['path']) . '</loc>' . PHP_EOL;
            $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . ($config['path'] === '/' ? '1.0' : '0.7') . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        $response->xml($xml);
    }

    public function news(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $items = $this->news?->paginate([]) ?? [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        foreach ($items as $item) {
            $slug = $item['slug'] ?? '';
            if (empty($slug)) {
                continue;
            }

            $lastmod = $item['updated_at'] ?? $item['published_at'] ?? $item['news_date'] ?? $item['date'] ?? '';
            $image = $item['photo'] ?? $item['banner'] ?? $item['image'] ?? '';

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/news/' . $slug) . '</loc>' . PHP_EOL;
            if (!empty($lastmod)) {
                $xml .= '    <lastmod>' . $this->e(substr($lastmod, 0, 10)) . '</lastmod>' . PHP_EOL;
            }
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.8</priority>' . PHP_EOL;

            if (!empty($image)) {
                $image = preg_replace('#^/?uploads/#', '', $image);
                $imageUrl = str_starts_with($image, 'http') ? $image : $base . '/uploads/' . ltrim($image, '/');
                $xml .= '    <image:image>' . PHP_EOL;
                $xml .= '      <image:loc>' . $this->e($imageUrl) . '</image:loc>' . PHP_EOL;
                $xml .= '      <image:caption>' . $this->e($item['news_title'] ?? $item['title'] ?? '') . '</image:caption>' . PHP_EOL;
                $xml .= '    </image:image>' . PHP_EOL;
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        $response->xml($xml);
    }

    public function prestasi(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $items = $this->prestasi?->published(100, 0) ?? [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($items as $item) {
            $slug = $item['slug'] ?? '';
            if (empty($slug)) {
                continue;
            }

            $lastmod = $item['updated_at'] ?? $item['created_at'] ?? '';

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/prestasi/' . $slug) . '</loc>' . PHP_EOL;
            if (!empty($lastmod)) {
                $xml .= '    <lastmod>' . $this->e(substr($lastmod, 0, 10)) . '</lastmod>' . PHP_EOL;
            }
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.6</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        $response->xml($xml);
    }

    public function events(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $items = $this->event?->paginate([], 100, 0) ?? [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($items as $item) {
            $slug = $item['slug'] ?? '';
            if (empty($slug)) {
                continue;
            }

            $lastmod = $item['updated_at'] ?? $item['event_start_date'] ?? $item['start_date'] ?? '';

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/event/' . $slug) . '</loc>' . PHP_EOL;
            if (!empty($lastmod)) {
                $xml .= '    <lastmod>' . $this->e(substr($lastmod, 0, 10)) . '</lastmod>' . PHP_EOL;
            }
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.7</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        $response->xml($xml);
    }

    public function images(Request $request, Response $response): void
    {
        $base = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        foreach ($this->imageEntries($base) as $entry) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($entry['loc']) . '</loc>' . PHP_EOL;
            foreach ($entry['images'] as $image) {
                $xml .= '    <image:image>' . PHP_EOL;
                $xml .= '      <image:loc>' . $this->e($image['loc']) . '</image:loc>' . PHP_EOL;
                if (!empty($image['caption'])) {
                    $xml .= '      <image:caption>' . $this->e($image['caption']) . '</image:caption>' . PHP_EOL;
                }
                $xml .= '    </image:image>' . PHP_EOL;
            }
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        $response->xml($xml);
    }

    private function sitemapEntry(string $loc, string $lastmod): string
    {
        return '  <sitemap>' . PHP_EOL
            . '    <loc>' . $this->e($loc) . '</loc>' . PHP_EOL
            . '    <lastmod>' . $this->e($lastmod) . '</lastmod>' . PHP_EOL
            . '  </sitemap>' . PHP_EOL;
    }

    private function baseUrl(): string
    {
        return rtrim(SeoService::absoluteUrl('/'), '/');
    }

    /** @return array<int, array{loc: string, images: array<int, array{loc: string, caption: string}>}> */
    private function imageEntries(string $base): array
    {
        $entries = [];

        foreach (($this->news?->paginate([]) ?? []) as $item) {
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $image = $this->imageUrl($base, (string) ($item['photo'] ?? $item['banner'] ?? $item['image'] ?? ''));
            if ($image !== '') {
                $entries[] = [
                    'loc' => $base . '/news/' . $slug,
                    'images' => [[
                        'loc' => $image,
                        'caption' => (string) ($item['news_title'] ?? $item['title'] ?? ''),
                    ]],
                ];
            }
        }

        foreach (($this->prestasi?->published(100, 0) ?? []) as $item) {
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $image = $this->imageUrl($base, (string) ($item['image'] ?? ''));
            if ($image !== '') {
                $entries[] = [
                    'loc' => $base . '/prestasi/' . $slug,
                    'images' => [[
                        'loc' => $image,
                        'caption' => (string) ($item['title'] ?? 'Prestasi GenBI Jambi'),
                    ]],
                ];
            }
        }

        foreach (($this->event?->paginate([], 100, 0) ?? []) as $item) {
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $image = $this->imageUrl($base, (string) ($item['image'] ?? $item['banner'] ?? ''));
            if ($image !== '') {
                $entries[] = [
                    'loc' => $base . '/event/' . $slug,
                    'images' => [[
                        'loc' => $image,
                        'caption' => (string) ($item['title'] ?? 'Agenda GenBI Jambi'),
                    ]],
                ];
            }
        }

        return $entries;
    }

    private function imageUrl(string $base, string $image): string
    {
        $image = trim($image);
        if ($image === '') {
            return '';
        }

        $image = str_replace('/public/uploads/', '/uploads/', $image);
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        $image = preg_replace('#^/?uploads/#', '', $image) ?? $image;
        return $base . '/uploads/' . ltrim($image, '/');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
