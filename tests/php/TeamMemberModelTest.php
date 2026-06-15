<?php

declare(strict_types=1);

use App\Models\TeamMember;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    echo "PHP team member model tests skipped: pdo_sqlite driver is not available\n";
    return;
}

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec('CREATE TABLE divisis (id INTEGER PRIMARY KEY, nama TEXT, komsat_id INTEGER)');
$db->exec('CREATE TABLE komsats (id INTEGER PRIMARY KEY, nama TEXT)');
$db->exec('CREATE TABLE teams (
    id INTEGER PRIMARY KEY,
    name TEXT,
    designation TEXT,
    photo TEXT,
    detail TEXT,
    komsat_id INTEGER,
    divisi_id INTEGER,
    komsat TEXT,
    tahun TEXT,
    show_on_home INTEGER DEFAULT 0,
    home_sort_order INTEGER DEFAULT 0,
    deleted_at TEXT NULL
)');

$db->exec("INSERT INTO komsats (id, nama) VALUES (1, 'Universitas Jambi'), (2, 'GenBI Wilayah Jambi'), (3, 'Alumni')");
$db->exec("INSERT INTO divisis (id, nama, komsat_id) VALUES (1, 'Badan Pengurus Inti', 1), (2, 'Divisi PSDM', 1)");
$db->exec("INSERT INTO teams (id, name, designation, komsat_id, divisi_id, komsat, tahun, show_on_home, home_sort_order, deleted_at) VALUES
    (1, 'Ketua 2024', 'Ketua', 1, 1, 'Universitas Jambi', '2024', 0, 0, NULL),
    (2, 'Ketua 2025', 'Ketua', 1, 1, 'Universitas Jambi', '2025', 0, 0, NULL),
    (3, 'Override 2025', 'Koordinator', 1, 2, 'Universitas Jambi', '2025', 1, 1, NULL),
    (4, 'BPI Wilayah 2025', 'Ketua Wilayah', 2, 1, 'GenBI Wilayah Jambi', '2025', 1, 99, NULL),
    (5, 'BPI Komsat 2025', 'Ketua Komsat', 1, 1, 'Universitas Jambi', '2025', 1, 0, NULL),
    (6, 'Alumni Member', 'Alumni', 3, 2, 'Alumni', '2025', 0, 0, NULL)
");

$model = new TeamMember($db);

$manualSelection = $model->bpiCore();
assert(count($manualSelection) === 3);
assert($manualSelection[0]['name'] === 'BPI Wilayah 2025');
assert($manualSelection[0]['show_on_home'] === true);
assert($manualSelection[1]['name'] === 'BPI Komsat 2025');
assert($manualSelection[2]['name'] === 'Override 2025');

$allActive = $model->allActive([], 10, 0);
assert($allActive[0]['name'] === 'BPI Wilayah 2025');

$activeOptions = $model->searchOptions('', 10, ['active_only' => true]);
$activeOptionIds = array_map(static fn(array $item): int => (int) $item['id'], $activeOptions);
assert(!in_array(6, $activeOptionIds, true));

$db->exec('UPDATE teams SET show_on_home = 0 WHERE id IN (3, 4, 5)');

$fallbackSelection = $model->bpiCore();
assert(count($fallbackSelection) === 1);
assert($fallbackSelection[0]['name'] === 'Ketua 2025');
assert($fallbackSelection[0]['division'] === 'Badan Pengurus Inti');
assert($fallbackSelection[0]['year'] === '2025');

echo "PHP team member model tests passed\n";
