<?php

declare(strict_types=1);

use App\Models\Setting;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_setting (setting_key TEXT PRIMARY KEY, setting_value TEXT NULL, setting_type TEXT NOT NULL DEFAULT "string", description TEXT NULL, updated_by INTEGER NULL, updated_at TEXT NULL)');
$db->exec("INSERT INTO tbl_setting (setting_key, setting_value, setting_type, description) VALUES ('comments.enabled', '1', 'bool', 'Allow comments globally')");

$model = new Setting($db);
assert($model->get('comments.enabled') === true);
assert($model->get('comments.missing', 99) === 99);
$model->putMany([
    'comments.enabled' => false,
    'comments.max_reply_depth' => 4,
    'comments.root_sort' => 'top_voted',
]);
assert($model->get('comments.enabled') === false);
assert($model->get('comments.max_reply_depth') === 4);
assert($model->get('comments.root_sort') === 'top_voted');
$all = $model->all();
assert($all['comments.enabled'] === false);
assert($all['comments.max_reply_depth'] === 4);

echo "PHP setting model tests passed\n";
