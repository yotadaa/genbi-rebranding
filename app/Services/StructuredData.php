<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;

final class StructuredData
{
    /** Organization schema for site-wide injection. */
    public static function organization(): string
    {
        $baseUrl = self::baseUrl();
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'GenBI Provinsi Jambi',
            'alternateName' => 'Generasi Baru Indonesia Provinsi Jambi',
            'url' => $baseUrl,
            'logo' => $baseUrl . '/assets/images/logo-genbi.png',
            'description' => 'Komunitas penerima beasiswa Bank Indonesia di Provinsi Jambi.',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Jambi',
                'addressRegion' => 'Jambi',
                'addressCountry' => 'ID',
            ],
            'sameAs' => [
                'https://www.instagram.com/genbijambi/',
            ],
        ];

        return self::script($data);
    }

    /** BreadcrumbList schema. */
    public static function breadcrumbs(array $items): string
    {
        if (count($items) < 2) {
            return '';
        }

        $list = [];
        foreach ($items as $i => $item) {
            $baseUrl = self::baseUrl();
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => str_starts_with($item['url'], 'http') ? $item['url'] : $baseUrl . $item['url'],
            ];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];

        return self::script($data);
    }

    /** NewsArticle schema for news detail pages. */
    public static function newsArticle(array $news): string
    {
        $title = $news['title'] ?? $news['news_title'] ?? '';
        $baseUrl = self::baseUrl();
        $slug = $news['slug'] ?? '';
        $description = $news['excerpt'] ?? $news['news_content_short'] ?? $news['meta_description'] ?? '';
        $image = $news['image'] ?? $news['photo'] ?? $news['banner'] ?? '';
        $published = $news['published_at'] ?? $news['date'] ?? '';
        $author = !empty($news['author']) ? $news['author'] : (!empty($news['contributor_pewarta']) ? $news['contributor_pewarta'] : 'Redaksi GenBI Jambi');

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => mb_substr($title, 0, 110),
            'description' => mb_substr(strip_tags($description), 0, 200),
            'url' => $baseUrl . '/news/' . $slug,
            'datePublished' => self::isoDate($published),
            'author' => [
                '@type' => 'Person',
                'name' => $author,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'GenBI Provinsi Jambi',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $baseUrl . '/assets/images/logo-genbi.png',
                ],
            ],
        ];

        if ($image !== '') {
            $image = str_replace('/public/uploads/', '/uploads/', $image);
            $data['image'] = str_starts_with($image, 'http') ? $image : $baseUrl . $image;
        }

        return self::script($data);
    }

    /** Event schema for event detail pages. */
    public static function event(array $event): string
    {
        $title = $event['title'] ?? $event['event_title'] ?? '';
        $baseUrl = self::baseUrl();
        $description = $event['excerpt'] ?? $event['event_content_short'] ?? '';
        $startDate = $event['start_date'] ?? $event['event_start_date'] ?? '';
        $endDate = $event['end_date'] ?? $event['event_end_date'] ?? '';
        $location = $event['location'] ?? $event['event_location'] ?? '';
        $image = $event['image'] ?? $event['photo'] ?? '';
        $id = $event['id'] ?? $event['event_id'] ?? 0;
        $slug = $event['slug'] ?? '';

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $title,
            'description' => mb_substr(strip_tags($description), 0, 200),
            'url' => $baseUrl . '/event/' . ($slug !== '' ? $slug : $id),
            'startDate' => self::isoDate($startDate),
            'organizer' => [
                '@type' => 'Organization',
                'name' => 'GenBI Provinsi Jambi',
                'url' => $baseUrl,
            ],
        ];

        if ($endDate !== '' && $endDate !== $startDate) {
            $data['endDate'] = self::isoDate($endDate);
        }

        if ($location !== '') {
            $data['location'] = [
                '@type' => 'Place',
                'name' => $location,
                'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Jambi', 'addressCountry' => 'ID'],
            ];
        }

        if ($image !== '') {
            $image = str_replace('/public/uploads/', '/uploads/', $image);
            $data['image'] = str_starts_with($image, 'http') ? $image : $baseUrl . $image;
        }

        return self::script($data);
    }

    private static function isoDate(string $date): string
    {
        if ($date === '') {
            return '';
        }
        $ts = strtotime($date);
        return $ts !== false ? date('Y-m-d', $ts) : $date;
    }

    private static function script(array $data): string
    {
        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    }

    private static function baseUrl(): string
    {
        return rtrim(App::config()['url'], '/');
    }
}
