<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Models\News;
use App\Services\SeoConfig;
use App\Services\SeoService;

class FeedController
{
    public function __construct(private ?News $news = null) {}

    public function news(Request $request, Response $response): void
    {
        $base = rtrim(SeoService::absoluteUrl('/'), '/');
        $items = $this->news?->paginate([]) ?? [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . PHP_EOL;
        $xml .= '<channel>' . PHP_EOL;
        $xml .= '  <title>' . $this->e('Berita ' . SeoConfig::SITE_NAME) . '</title>' . PHP_EOL;
        $xml .= '  <link>' . $this->e($base . '/news') . '</link>' . PHP_EOL;
        $xml .= '  <description>' . $this->e(SeoConfig::DEFAULT_DESCRIPTION) . '</description>' . PHP_EOL;
        $xml .= '  <language>id</language>' . PHP_EOL;
        $xml .= '  <atom:link href="' . $this->e($base . '/feed.xml') . '" rel="self" type="application/rss+xml" />' . PHP_EOL;

        if (!empty($items)) {
            $latestDate = $items[0]['updated_at'] ?? $items[0]['news_date'] ?? $items[0]['date'] ?? '';
            if (!empty($latestDate)) {
                $xml .= '  <lastBuildDate>' . $this->e(date('r', strtotime($latestDate) ?: time())) . '</lastBuildDate>' . PHP_EOL;
            }
        }

        foreach ($items as $item) {
            $slug = $item['slug'] ?? '';
            if (empty($slug)) {
                continue;
            }

            $title = $item['news_title'] ?? $item['title'] ?? '';
            $description = $item['news_content_short'] ?? $item['excerpt'] ?? '';
            $link = $base . '/news/' . $slug;
            $pubDate = $item['published_at'] ?? $item['news_date'] ?? $item['date'] ?? '';
            $image = $item['photo'] ?? $item['banner'] ?? $item['image'] ?? '';

            $xml .= '  <item>' . PHP_EOL;
            $xml .= '    <title>' . $this->e($title) . '</title>' . PHP_EOL;
            $xml .= '    <link>' . $this->e($link) . '</link>' . PHP_EOL;
            $xml .= '    <guid isPermaLink="true">' . $this->e($link) . '</guid>' . PHP_EOL;
            $xml .= '    <description>' . $this->e($description) . '</description>' . PHP_EOL;

            if (!empty($pubDate)) {
                $ts = strtotime($pubDate);
                if ($ts !== false) {
                    $xml .= '    <pubDate>' . $this->e(date('r', $ts)) . '</pubDate>' . PHP_EOL;
                }
            }

            if (!empty($image)) {
                $image = preg_replace('#^/?uploads/#', '', $image);
                $imageUrl = str_starts_with($image, 'http') ? $image : $base . '/uploads/' . ltrim($image, '/');
                $xml .= '    <enclosure url="' . $this->e($imageUrl) . '" type="image/jpeg" />' . PHP_EOL;
            }

            $xml .= '  </item>' . PHP_EOL;
        }

        $xml .= '</channel>' . PHP_EOL;
        $xml .= '</rss>';

        $response->xml($xml);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
