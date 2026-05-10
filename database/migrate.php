<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\MigrationRunner;

require dirname(__DIR__) . '/bootstrap/app.php';

$runner = new MigrationRunner(Database::connection(), __DIR__ . '/migrations');
$applied = $runner->run();

// Seed migrations are stored in database/migrations as idempotent migration files,
// so running this script applies both schema and seed data in sorted order.

if ($applied === []) {
    echo "No pending migrations.\n";
    exit(0);
}

foreach ($applied as $migration) {
    echo "Migrated: {$migration}\n";
}
