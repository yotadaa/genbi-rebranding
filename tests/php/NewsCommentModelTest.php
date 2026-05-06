<?php

declare(strict_types=1);

use App\Models\NewsComment;

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

echo "PHP news comment model tests passed\n";
