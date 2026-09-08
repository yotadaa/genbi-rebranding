<?php

declare(strict_types=1);

use App\Controllers\Admin\SettingsController;
use App\Models\Setting;
use App\Services\SiteSettings;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_setting (setting_key TEXT PRIMARY KEY, setting_value TEXT NULL, setting_type TEXT NOT NULL DEFAULT "string", description TEXT NULL, updated_by INTEGER NULL, updated_at TEXT NULL)');

$setting = new Setting($db);
$controller = new SettingsController($setting, new SiteSettings($setting), null);

$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('normalizeHex');
$method->setAccessible(true);
$errors = [];
$result = $method->invokeArgs($controller, ['#114B9A', &$errors, 'site.color_primary']);
assert($result === '#114b9a');

$errors = [];
$result = $method->invokeArgs($controller, ['114B9A', &$errors, 'site.color_primary']);
assert($result === null);
assert(isset($errors['site.color_primary']));

echo "PHP settings controller tests passed\n";
