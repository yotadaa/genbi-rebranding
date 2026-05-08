<?php

declare(strict_types=1);

use App\Services\LoginThrottleService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE tbl_login_attempt (
    login_attempt_id INTEGER PRIMARY KEY AUTOINCREMENT,
    email_normalized TEXT NOT NULL,
    ip_address TEXT NOT NULL,
    attempt_count INTEGER NOT NULL DEFAULT 0,
    first_attempt_at TEXT NOT NULL,
    last_attempt_at TEXT NOT NULL,
    locked_until TEXT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
)');

$service = new LoginThrottleService($db);

for ($i = 0; $i < 9; $i++) {
    $service->recordFailure('Admin@Example.com', '127.0.0.1');
    assert($service->isBlocked('admin@example.com', '127.0.0.1') === false);
}

$service->recordFailure('admin@example.com', '127.0.0.1');
assert($service->isBlocked('admin@example.com', '127.0.0.1') === true);

$service->clear('admin@example.com', '127.0.0.1');
assert($service->isBlocked('admin@example.com', '127.0.0.1') === false);

echo "PHP login throttle service tests passed\n";
