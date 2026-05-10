<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\SiteSettings;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_setting (setting_key TEXT PRIMARY KEY, setting_value TEXT NULL, setting_type TEXT NOT NULL DEFAULT "string", description TEXT NULL, updated_by INTEGER NULL, updated_at TEXT NULL)');
$db->exec("INSERT INTO tbl_setting (setting_key, setting_value, setting_type) VALUES ('site.name', 'GenBI Test', 'string')");
$db->exec("INSERT INTO tbl_setting (setting_key, setting_value, setting_type) VALUES ('theme.admin_key', 'dark-01', 'string')");

$service = new SiteSettings(new Setting($db));
$site = $service->site();
assert($site['name'] === 'GenBI Test');
assert($service->themeKey('admin') === 'dark-01');
assert($service->themeKey('public') === 'genbi');

echo "PHP site settings service tests passed\n";
