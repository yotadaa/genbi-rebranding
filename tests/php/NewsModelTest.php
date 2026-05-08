<?php

declare(strict_types=1);

use App\Core\Slugger;
use App\Models\News;

require dirname(__DIR__, 2) . '/bootstrap/app.php';
App\Core\Env::load(__DIR__ . '/fixtures/test.env');

$row = News::mapRow([
    'news_id' => 94,
    'slug' => '',
    'news_title' => 'GenBI Jambi Hadiri Diseminasi Kajian UMKM KCBN Muara Jambi',
    'news_content' => 'Isi lengkap berita.',
    'news_content_short' => 'Ringkasan berita.',
    'news_date' => '2026-05-06',
    'photo' => '/uploads/news/photo.jpg',
    'banner' => '/uploads/news/banner.jpg',
    'category_id' => 7,
    'category_name' => 'BANK INDONESIA',
    'contributor_redaksi' => 'Redaksi',
    'contributor_pewarta' => 'Pewarta',
    'contributor_editor' => 'Editor',
    'meta_title' => 'Meta title',
    'meta_keyword' => 'genbi,jambi',
    'meta_description' => 'Meta description',
    'status' => 'published',
]);

assert($row['id'] === 94);
assert($row['news_id'] === 94);
assert($row['slug'] === 'genbi-jambi-hadiri-diseminasi-kajian-umkm-kcbn-muara-jambi-94');
assert($row['title'] === 'GenBI Jambi Hadiri Diseminasi Kajian UMKM KCBN Muara Jambi');
assert($row['excerpt'] === 'Ringkasan berita.');
assert($row['image'] === '/uploads/news/photo.jpg');
assert($row['category'] === 'BANK INDONESIA');
assert($row['author'] === 'Pewarta');
assert($row['editor'] === 'Editor');
assert($row['meta_description'] === 'Meta description');

$relativeImageRow = News::mapRow([
    'news_id' => 98,
    'news_title' => 'Talkshow Ekonomi Syariah Siginjai Fest',
    'photo' => 'news-98.jpeg',
]);
assert($relativeImageRow['photo'] === '/uploads/news-98.jpeg');

assert(Slugger::slugify('Talkshow Siginjai Fest 2026!') === 'talkshow-siginjai-fest-2026');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_category (category_id INTEGER PRIMARY KEY, category_name TEXT)');
$db->exec('CREATE TABLE tbl_news (
    news_id INTEGER PRIMARY KEY,
    slug TEXT,
    news_title TEXT,
    news_content TEXT,
    news_content_short TEXT,
    category_id INTEGER,
    status TEXT,
    deleted_at TEXT NULL
)');
$db->exec("INSERT INTO tbl_category (category_id, category_name) VALUES (1, 'General')");
$db->exec("INSERT INTO tbl_news (news_id, slug, news_title, news_content, news_content_short, category_id, status, deleted_at) VALUES
    (1, 'published-news', 'Published', '<p>ok</p>', 'short', 1, 'published', NULL),
    (2, 'draft-news', 'Draft', '<p>draft</p>', 'draft', 1, 'draft', NULL),
    (3, 'deleted-news', 'Deleted', '<p>deleted</p>', 'deleted', 1, 'published', '2026-05-08 00:00:00')");

$model = new News($db);
assert(($model->findPublicBySlug('published-news')['id'] ?? 0) === 1);
assert($model->findPublicBySlug('draft-news') === null);
assert($model->findPublicById(3) === null);

echo "PHP news model tests passed\n";
