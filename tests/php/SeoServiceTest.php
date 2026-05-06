<?php

declare(strict_types=1);

use App\Services\SeoConfig;
use App\Services\SeoService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';
App\Core\Env::load(__DIR__ . '/fixtures/test.env');

// --- absoluteUrl ---
assert(SeoService::absoluteUrl('/news/test') === 'http://example.test/news/test');
assert(SeoService::absoluteUrl('https://example.com/img.jpg') === 'https://example.com/img.jpg');
assert(SeoService::absoluteUrl('/') === 'http://example.test/');

// --- imageUrl ---
assert(SeoService::imageUrl(null) === 'http://example.test/assets/images/default-og-genbi.jpg');
assert(SeoService::imageUrl('') === 'http://example.test/assets/images/default-og-genbi.jpg');
assert(SeoService::imageUrl('https://cdn.example.com/photo.jpg') === 'https://cdn.example.com/photo.jpg');
assert(SeoService::imageUrl('/uploads/news/photo.jpg') === 'http://example.test/uploads/news/photo.jpg');
assert(SeoService::imageUrl('/public/uploads/news/photo.jpg') === 'http://example.test/uploads/news/photo.jpg');
assert(SeoService::imageUrl('news/photo.jpg') === 'http://example.test/uploads/news/photo.jpg');

// --- cleanDescription ---
assert(SeoService::cleanDescription(null) === SeoConfig::DEFAULT_DESCRIPTION);
assert(SeoService::cleanDescription('') === SeoConfig::DEFAULT_DESCRIPTION);
assert(SeoService::cleanDescription('Short text') === 'Short text');
assert(SeoService::cleanDescription('<p>HTML <b>tags</b> removed</p>') === 'HTML tags removed');
$long = str_repeat('A', 200);
$cleaned = SeoService::cleanDescription($long);
assert(mb_strlen($cleaned) === 160);
assert(str_ends_with($cleaned, '...'));

// --- isoDate ---
assert(SeoService::isoDate(null) === '');
assert(SeoService::isoDate('') === '');
$iso = SeoService::isoDate('2026-05-06 12:00:00');
assert(str_starts_with($iso, '2026-05-06'));
assert(str_contains($iso, 'T'));

// --- forPage ---
$home = SeoService::forPage('index.html');
assert($home['title'] === 'GenBI Provinsi Jambi | Generasi Baru Indonesia');
assert($home['canonical'] === 'http://example.test/');
assert($home['og_type'] === 'website');
assert(str_contains($home['og_image'], 'default-og-genbi'));
assert($home['robots'] === 'index, follow');

$unknown = SeoService::forPage('nonexistent.html');
assert($unknown['title'] === SeoConfig::SITE_NAME);

// --- forNews ---
$news = SeoService::forNews([
    'news_title' => 'Talkshow Siginjai Fest 2026',
    'news_content_short' => 'Ringkasan berita talkshow.',
    'slug' => 'talkshow-siginjai-fest-2026-100',
    'photo' => 'talkshow.jpg',
    'news_date' => '2026-04-30',
    'updated_at' => '2026-05-01',
    'category_name' => 'BANK INDONESIA',
]);
assert(str_contains($news['title'], 'Talkshow Siginjai Fest 2026'));
assert(str_contains($news['title'], SeoConfig::SITE_NAME));
assert($news['canonical'] === 'http://example.test/news/talkshow-siginjai-fest-2026-100');
assert($news['og_type'] === 'article');
assert(str_contains($news['og_image'], 'talkshow.jpg'));
assert($news['og_article_section'] === 'BANK INDONESIA');
assert(!empty($news['og_article_published']));
assert(!empty($news['og_article_modified']));

// --- forNews with meta_title override ---
$newsWithMeta = SeoService::forNews([
    'meta_title' => 'Custom Meta Title',
    'news_title' => 'Original Title',
    'slug' => 'test-slug',
]);
assert(str_contains($newsWithMeta['title'], 'Custom Meta Title'));
assert(!str_contains($newsWithMeta['title'], 'Original Title'));

// --- forNews with no image (fallback) ---
$newsNoImage = SeoService::forNews([
    'news_title' => 'No Image News',
    'slug' => 'no-image',
]);
assert(str_contains($newsNoImage['og_image'], 'default-og-genbi'));

// --- forPrestasi ---
$prestasi = SeoService::forPrestasi([
    'title' => 'Juara KTI Nasional',
    'slug' => 'juara-kti-nasional-5',
    'description' => 'Prestasi mahasiswa GenBI.',
    'image' => '/uploads/prestasi/kti.jpg',
]);
assert(str_contains($prestasi['title'], 'Juara KTI Nasional'));
assert($prestasi['canonical'] === 'http://example.test/prestasi/juara-kti-nasional-5');
assert($prestasi['og_image'] === 'http://example.test/uploads/prestasi/kti.jpg');

// --- renderMetaBlock ---
$block = SeoService::renderMetaBlock($home);
assert(str_contains($block, '<title>'));
assert(str_contains($block, '<meta name="description"'));
assert(str_contains($block, '<link rel="canonical"'));
assert(str_contains($block, 'og:type'));
assert(str_contains($block, 'og:site_name'));
assert(str_contains($block, 'twitter:card'));
assert(str_contains($block, 'feed.xml'));

$newsBlock = SeoService::renderMetaBlock($news);
assert(str_contains($newsBlock, 'article:published_time'));
assert(str_contains($newsBlock, 'article:section'));

echo "PHP SEO service tests passed\n";
