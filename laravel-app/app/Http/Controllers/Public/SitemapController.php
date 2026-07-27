<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Event;
use App\Models\Prestasi;

class SitemapController extends Controller
{
    private function baseUrl(): string
    {
        return url('/');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }

    public function index()
    {
        $base = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        $sitemaps = [
            '/sitemap-pages.xml',
            '/sitemap-news.xml',
            '/sitemap-events.xml',
            '/sitemap-prestasi.xml',
            '/sitemap-images.xml'
        ];

        foreach ($sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . $sitemap) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';
        return response($xml)->header('Content-Type', 'text/xml');
    }

    public function pages()
    {
        $base = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        $pages = [
            '/' => 'daily',
            '/about' => 'monthly',
            '/news' => 'daily',
            '/event' => 'weekly',
            '/prestasi' => 'weekly',
            '/team' => 'monthly',
            '/contact' => 'monthly',
        ];

        foreach ($pages as $path => $freq) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . $path) . '</loc>' . PHP_EOL;
            $xml .= '    <changefreq>' . $freq . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . ($path === '/' ? '1.0' : '0.8') . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'text/xml');
    }

    public function news()
    {
        $base = $this->baseUrl();
        $items = News::published()->latest('news_date')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        foreach ($items as $item) {
            if (empty($item->slug)) continue;

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/news/' . $item->slug) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $this->e(substr($item->news_date, 0, 10)) . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.8</priority>' . PHP_EOL;

            $image = $item->photo ?: $item->banner;
            if ($image) {
                $imageUrl = str_starts_with($image, 'http') ? $image : $base . '/uploads/' . ltrim(str_replace('uploads/', '', $image), '/');
                $xml .= '    <image:image>' . PHP_EOL;
                $xml .= '      <image:loc>' . $this->e($imageUrl) . '</image:loc>' . PHP_EOL;
                $xml .= '      <image:caption>' . $this->e($item->news_title) . '</image:caption>' . PHP_EOL;
                $xml .= '    </image:image>' . PHP_EOL;
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'text/xml');
    }

    public function events()
    {
        $base = $this->baseUrl();
        $items = Event::latest('event_start_date')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($items as $item) {
            if (empty($item->slug)) continue;

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/event/' . $item->slug) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $this->e(substr($item->event_start_date ?? date('Y-m-d'), 0, 10)) . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.7</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'text/xml');
    }

    public function prestasi()
    {
        $base = $this->baseUrl();
        $items = Prestasi::where('status', 'published')->latest('year')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($items as $item) {
            if (empty($item->slug)) continue;

            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . $this->e($base . '/prestasi/' . $item->slug) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $this->e(substr($item->updated_at ?? date('Y-m-d'), 0, 10)) . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.6</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'text/xml');
    }

    public function images()
    {
        $base = $this->baseUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;
        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'text/xml');
    }
}
