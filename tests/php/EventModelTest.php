<?php

declare(strict_types=1);

use App\Models\Event;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_event (
    event_id INTEGER PRIMARY KEY,
    event_title TEXT,
    event_content TEXT,
    event_content_short TEXT,
    event_start_date TEXT,
    event_end_date TEXT,
    event_location TEXT,
    event_map TEXT,
    photo TEXT,
    banner TEXT,
    status TEXT,
    deleted_at TEXT NULL
)');
$db->exec("INSERT INTO tbl_event (event_id, event_title, event_content, event_content_short, event_start_date, event_end_date, event_location, event_map, photo, banner, status, deleted_at) VALUES
    (1, 'Visible event', '<p>visible</p>', 'visible', '2026-05-08', '2026-05-09', 'Jambi', '', '', '', 'published', NULL),
    (2, 'Draft event', '<p>draft</p>', 'draft', '2026-05-08', '2026-05-09', 'Jambi', '', '', '', 'draft', NULL),
    (3, 'Deleted event', '<p>deleted</p>', 'deleted', '2026-05-08', '2026-05-09', 'Jambi', '', '', '', 'published', '2026-05-08 00:00:00')");

$model = new Event($db);
$rows = $model->paginate([], 10, 0);

assert(count($rows) === 1);
assert($rows[0]['id'] === 1);
assert(($model->findPublicById(1)['id'] ?? 0) === 1);
assert($model->findPublicById(2) === null);
assert($model->findPublicById(3) === null);

echo "PHP event model tests passed\n";
