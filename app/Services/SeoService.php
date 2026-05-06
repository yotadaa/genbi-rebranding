<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;

final class SeoService
{
    public static function forPage(string $file): array
    {
        $pages = SeoConfig::pages();
        $config = $pages[$file] ?? null;

        if (!$config) {
            return self::defaults('/');
        }

        return [
            'title' => $config['title'],
            'description' => $config['description'],
            'canonical' => self::absoluteUrl($config['path']),
            'robots' => 'index, follow',
            'og_type' => 'website',
            'og_title' => $config['title'],
            'og_description' => $config['description'],
            'og_url' => self::absoluteUrl($config['path']),
            'og_image' => self::absoluteUrl(SeoConfig::DEFAULT_OG_IMAGE),
            'og_image_width' => '1200',
            'og_image_height' => '630',
            'og_image_alt' => SeoConfig::SITE_NAME,
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $config['title'],
            'twitter_description' => $config['description'],
            'twitter_image' => self::absoluteUrl(SeoConfig::DEFAULT_OG_IMAGE),
        ];
    }

    public static function forNews(array $news, ?string $categoryName = null): array
    {
        $rawTitle = self::pick($news, 'meta_title', 'news_title', 'title');
        $title = $rawTitle . ' | ' . SeoConfig::SITE_NAME;
        $description = self::cleanDescription(self::pick($news, 'meta_description', 'news_content_short', 'excerpt'));
        $slug = $news['slug'] ?? '';
        $canonical = self::absoluteUrl('/news/' . $slug);
        $image = self::imageUrl(self::pick($news, 'photo', 'banner', 'image'));
        $publishedIso = self::isoDate(self::pick($news, 'published_at', 'news_date', 'date'));
        $modifiedIso = self::isoDate($news['updated_at'] ?? $news['published_at'] ?? '');
        $category = $categoryName ?? $news['category_name'] ?? $news['category'] ?? 'Berita GenBI';

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index, follow',
            'og_type' => 'article',
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $canonical,
            'og_image' => $image,
            'og_image_width' => '1200',
            'og_image_height' => '630',
            'og_image_alt' => $rawTitle,
            'og_article_published' => $publishedIso,
            'og_article_modified' => $modifiedIso,
            'og_article_section' => $category,
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $image,
            'twitter_image_alt' => $rawTitle,
        ];
    }

    public static function forPrestasi(array $prestasi): array
    {
        $rawTitle = $prestasi['title'] ?? 'Prestasi';
        $title = $rawTitle . ' | ' . SeoConfig::SITE_NAME;
        $description = self::cleanDescription($prestasi['description'] ?? '');
        $slug = $prestasi['slug'] ?? '';
        $canonical = self::absoluteUrl('/prestasi/' . $slug);
        $image = self::imageUrl($prestasi['image'] ?? '');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index, follow',
            'og_type' => 'article',
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $canonical,
            'og_image' => $image,
            'og_image_width' => '1200',
            'og_image_height' => '630',
            'og_image_alt' => $rawTitle,
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $image,
        ];
    }

    public static function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(App::config()['url'], '/') . '/' . ltrim($path, '/');
    }

    public static function imageUrl(?string $path): string
    {
        if (empty($path)) {
            return self::absoluteUrl(SeoConfig::DEFAULT_OG_IMAGE);
        }

        $path = str_replace('/public/uploads/', '/uploads/', $path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/uploads/' . ltrim($path, '/');
        }

        return self::absoluteUrl($path);
    }

    public static function cleanDescription(?string $text, int $limit = 160): string
    {
        if (empty($text)) {
            return SeoConfig::DEFAULT_DESCRIPTION;
        }

        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = trim($text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 3) . '...';
    }

    public static function isoDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        $ts = strtotime($date);
        if ($ts === false) {
            return '';
        }

        return date('c', $ts);
    }

    public static function renderMetaBlock(array $seo): string
    {
        $e = static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $lines = [];

        $lines[] = '<title>' . $e($seo['title'] ?? '') . '</title>';
        $lines[] = '<meta name="description" content="' . $e($seo['description'] ?? '') . '">';
        $lines[] = '<link rel="canonical" href="' . $e($seo['canonical'] ?? '') . '">';
        $lines[] = '<meta name="robots" content="' . $e($seo['robots'] ?? 'index, follow') . '">';

        // Open Graph
        $lines[] = '<meta property="og:type" content="' . $e($seo['og_type'] ?? 'website') . '">';
        $lines[] = '<meta property="og:site_name" content="' . $e(SeoConfig::SITE_NAME) . '">';
        $lines[] = '<meta property="og:title" content="' . $e($seo['og_title'] ?? '') . '">';
        $lines[] = '<meta property="og:description" content="' . $e($seo['og_description'] ?? '') . '">';
        $lines[] = '<meta property="og:url" content="' . $e($seo['og_url'] ?? '') . '">';
        $lines[] = '<meta property="og:image" content="' . $e($seo['og_image'] ?? '') . '">';
        $lines[] = '<meta property="og:image:width" content="' . $e($seo['og_image_width'] ?? '1200') . '">';
        $lines[] = '<meta property="og:image:height" content="' . $e($seo['og_image_height'] ?? '630') . '">';
        $lines[] = '<meta property="og:image:alt" content="' . $e($seo['og_image_alt'] ?? '') . '">';

        if (!empty($seo['og_article_published'])) {
            $lines[] = '<meta property="article:published_time" content="' . $e($seo['og_article_published']) . '">';
        }
        if (!empty($seo['og_article_modified'])) {
            $lines[] = '<meta property="article:modified_time" content="' . $e($seo['og_article_modified']) . '">';
        }
        if (!empty($seo['og_article_section'])) {
            $lines[] = '<meta property="article:section" content="' . $e($seo['og_article_section']) . '">';
        }

        // Twitter Card
        $lines[] = '<meta name="twitter:card" content="' . $e($seo['twitter_card'] ?? 'summary_large_image') . '">';
        $lines[] = '<meta name="twitter:title" content="' . $e($seo['twitter_title'] ?? '') . '">';
        $lines[] = '<meta name="twitter:description" content="' . $e($seo['twitter_description'] ?? '') . '">';
        $lines[] = '<meta name="twitter:image" content="' . $e($seo['twitter_image'] ?? '') . '">';
        if (!empty($seo['twitter_image_alt'])) {
            $lines[] = '<meta name="twitter:image:alt" content="' . $e($seo['twitter_image_alt']) . '">';
        }

        // RSS feed link
        $lines[] = '<link rel="alternate" type="application/rss+xml" title="Berita ' . $e(SeoConfig::SITE_NAME) . '" href="' . $e(self::absoluteUrl('/feed.xml')) . '">';

        return implode(PHP_EOL . '  ', $lines);
    }

    private static function pick(array $data, string ...$keys): string
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                return (string) $data[$key];
            }
        }
        return '';
    }

    private static function defaults(string $path): array
    {
        return [
            'title' => SeoConfig::SITE_NAME,
            'description' => SeoConfig::DEFAULT_DESCRIPTION,
            'canonical' => self::absoluteUrl($path),
            'robots' => 'index, follow',
            'og_type' => 'website',
            'og_title' => SeoConfig::SITE_NAME,
            'og_description' => SeoConfig::DEFAULT_DESCRIPTION,
            'og_url' => self::absoluteUrl($path),
            'og_image' => self::absoluteUrl(SeoConfig::DEFAULT_OG_IMAGE),
            'og_image_width' => '1200',
            'og_image_height' => '630',
            'og_image_alt' => SeoConfig::SITE_NAME,
            'twitter_card' => 'summary_large_image',
            'twitter_title' => SeoConfig::SITE_NAME,
            'twitter_description' => SeoConfig::DEFAULT_DESCRIPTION,
            'twitter_image' => self::absoluteUrl(SeoConfig::DEFAULT_OG_IMAGE),
        ];
    }
}
