<?php

declare(strict_types=1);

use App\Models\NewsComment;
use App\Models\NewsCommentVote;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$row = NewsComment::mapRow([
    'comment_id' => 12,
    'news_id' => 94,
    'name' => 'Rina Aprilianti',
    'email' => 'rina@example.com',
    'website' => 'https://example.com',
    'content' => 'Komentar masuk antrean moderasi.',
    'status' => 'approved',
    'news_title' => 'Talkshow Siginjai Fest',
    'created_at' => '2026-05-06 12:00:00',
]);

assert($row['id'] === 12);
assert($row['comment_id'] === 12);
assert($row['news_id'] === 94);
assert($row['name'] === 'Rina Aprilianti');
assert($row['email'] === 'rina@example.com');
assert($row['text'] === 'Komentar masuk antrean moderasi.');
assert($row['comment'] === 'Komentar masuk antrean moderasi.');
assert($row['status'] === 'approved');
assert($row['article'] === 'Talkshow Siginjai Fest');
assert($row['date'] === '2026-05-06 12:00:00');
assert($row['parent_id'] === null);
assert($row['up_votes'] === 0);
assert($row['down_votes'] === 0);
assert($row['score'] === 0);

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_news (news_id INTEGER PRIMARY KEY, news_title TEXT)');
$db->exec('CREATE TABLE tbl_news_comment (comment_id INTEGER PRIMARY KEY AUTOINCREMENT, news_id INTEGER NOT NULL, parent_id INTEGER NULL, name TEXT NOT NULL, email TEXT NOT NULL, website TEXT NULL, content TEXT NOT NULL, status TEXT NOT NULL, ip_address TEXT NULL, user_agent TEXT NULL, moderated_by INTEGER NULL, moderated_at TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NULL, deleted_at TEXT NULL)');
$db->exec('CREATE TABLE tbl_news_comment_vote (vote_id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER NOT NULL, news_id INTEGER NOT NULL, voter_key TEXT NOT NULL, value INTEGER NOT NULL, ip_address TEXT NULL, user_agent TEXT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
$db->exec('CREATE UNIQUE INDEX uq_vote_per_commenter ON tbl_news_comment_vote (comment_id, voter_key)');
$db->exec("INSERT INTO tbl_news (news_id, news_title) VALUES (94, 'Talkshow Siginjai Fest')");
$db->exec("INSERT INTO tbl_news_comment (comment_id, news_id, parent_id, name, email, website, content, status, created_at) VALUES
    (1, 94, NULL, 'Rina', 'rina@example.com', NULL, 'Komentar induk', 'approved', '2026-05-06 10:00:00'),
    (2, 94, 1, 'Dimas', 'dimas@example.com', NULL, 'Balasan pertama', 'approved', '2026-05-06 11:00:00'),
    (3, 94, 1, 'Aulia', 'aulia@example.com', NULL, 'Balasan kedua', 'approved', '2026-05-06 12:00:00'),
    (4, 94, 2, 'Pending', 'pending@example.com', NULL, 'Tidak tampil', 'pending', '2026-05-06 13:00:00')");

$votes = new NewsCommentVote($db);
$votes->upsert(1, 94, 'v1', 1, null, null);
$votes->upsert(3, 94, 'v2', -1, null, null);

$model = new NewsComment($db, $votes);
$tree = $model->treeForNews(94);
assert(count($tree) === 1);
assert($tree[0]['id'] === 1);
assert($tree[0]['score'] === 1);
assert(count($tree[0]['children']) === 2);

$created = $model->create([
    'news_id' => 94,
    'parent_id' => 1,
    'name' => 'Balasan Baru',
    'email' => 'baru@example.com',
    'content' => 'Siap',
    'status' => 'pending',
]);
assert($created['parent_id'] === 1);

$failed = false;
try {
    $model->create([
        'news_id' => 94,
        'parent_id' => 999,
        'name' => 'Gagal',
        'email' => 'gagal@example.com',
        'content' => 'Tidak valid',
        'status' => 'pending',
    ]);
} catch (\RuntimeException) {
    $failed = true;
}

assert($failed === true);

echo "PHP news comment model tests passed\n";
