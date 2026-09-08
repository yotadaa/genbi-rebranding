<?php

declare(strict_types=1);

use App\Models\NewsCommentVote;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_news_comment_vote (vote_id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER NOT NULL, news_id INTEGER NOT NULL, voter_key TEXT NOT NULL, value INTEGER NOT NULL, ip_address TEXT NULL, user_agent TEXT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
$db->exec('CREATE UNIQUE INDEX uq_vote_per_commenter ON tbl_news_comment_vote (comment_id, voter_key)');

$model = new NewsCommentVote($db);
$model->upsert(11, 4, 'abc', 1, '127.0.0.1', 'UA');
assert($model->currentValue(11, 'abc') === 1);
$model->upsert(11, 4, 'abc', -1, '127.0.0.1', 'UA');
assert($model->currentValue(11, 'abc') === -1);
$model->upsert(11, 4, 'abc', 0, '127.0.0.1', 'UA');
assert($model->currentValue(11, 'abc') === 0);
$model->upsert(11, 4, 'second', 1, '127.0.0.2', 'UA');
$counts = $model->countsForNews(4);
assert($counts[11]['up'] === 1);
assert($counts[11]['down'] === 0);
assert($counts[11]['score'] === 1);

echo "PHP news comment vote model tests passed\n";
