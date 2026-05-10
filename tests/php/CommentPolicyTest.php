<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\CommentPolicy;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_setting (setting_key TEXT PRIMARY KEY, setting_value TEXT NULL, setting_type TEXT NOT NULL DEFAULT "string", description TEXT NULL, updated_by INTEGER NULL, updated_at TEXT NULL)');
$db->exec("INSERT INTO tbl_setting (setting_key, setting_value, setting_type) VALUES
    ('comments.enabled', '1', 'bool'),
    ('comments.voting_enabled', '1', 'bool'),
    ('comments.replies_enabled', '1', 'bool'),
    ('comments.max_reply_depth', '3', 'int'),
    ('comments.replies_require_moderation', '1', 'bool'),
    ('comments.root_sort', 'newest_first', 'string'),
    ('comments.reply_sort', 'oldest_first', 'string'),
    ('comments.rate_limit_per_ip_per_15min', '20', 'int'),
    ('comments.vote_rate_limit_per_ip_per_15min', '60', 'int')");

$policy = new CommentPolicy(new Setting($db));
$default = $policy->forNews([]);
assert($default['comments_enabled'] === true);
assert($default['max_reply_depth'] === 3);
$override = $policy->forNews([
    'comments_enabled' => 0,
    'voting_enabled' => 0,
    'replies_enabled' => 1,
    'max_reply_depth' => 5,
]);
assert($override['comments_enabled'] === false);
assert($override['voting_enabled'] === false);
assert($override['replies_enabled'] === true);
assert($override['max_reply_depth'] === 5);
assert(strlen(CommentPolicy::hashVoter('127.0.0.1', 'UA', 'salt')) === 64);

echo "PHP comment policy tests passed\n";
