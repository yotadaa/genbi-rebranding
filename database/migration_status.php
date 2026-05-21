<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\MigrationRunner;

require dirname(__DIR__) . '/bootstrap/app.php';

$db = Database::connection();
$runner = new MigrationRunner($db, __DIR__ . '/migrations');
$runner->ensureMigrationsTable();

$migrationFiles = glob(__DIR__ . '/migrations/*.php') ?: [];
sort($migrationFiles, SORT_STRING);
$fileNames = array_map('basename', $migrationFiles);

$appliedRows = fetchAppliedMigrations($db);
$appliedNames = array_column($appliedRows, 'migration');
$pending = array_values(array_diff($fileNames, $appliedNames));
$missingFiles = array_values(array_diff($appliedNames, $fileNames));

printLine('Migration status');
printLine('================');
printf("Total files: %d\n", count($fileNames));
printf("Applied:     %d\n", count(array_intersect($fileNames, $appliedNames)));
printf("Pending:     %d\n", count($pending));
printf("Orphaned:    %d\n\n", count($missingFiles));

printLine('Applied migrations');
printLine('------------------');
if ($appliedRows === []) {
    printLine('No migrations have been recorded yet.');
} else {
    foreach ($appliedRows as $row) {
        printf(
            '[batch %s] %s%s%s',
            $row['batch'],
            $row['migration'],
            $row['executed_at'] !== null ? ' @ ' . $row['executed_at'] : '',
            PHP_EOL
        );
    }
}

printLine('');
printLine('Pending migrations');
printLine('------------------');
if ($pending === []) {
    printLine('No pending migrations.');
} else {
    foreach ($pending as $migration) {
        printLine($migration);
    }
}

if ($missingFiles !== []) {
    printLine('');
    printLine('Recorded but missing from disk');
    printLine('------------------------------');
    foreach ($missingFiles as $migration) {
        printLine($migration);
    }
}

printLine('');
printLine('Tracked database shape');
printLine('----------------------');
foreach (trackedTables() as $table => $expectedColumns) {
    describeTable($db, $table, $expectedColumns);
}

/** @return array<int, array{migration: string, batch: int|string, executed_at: string|null}> */
function fetchAppliedMigrations(PDO $db): array
{
    $columns = columnsFor($db, 'migrations');
    $executedAtSelect = in_array('executed_at', $columns, true) ? 'executed_at' : 'NULL AS executed_at';
    $orderColumn = in_array('id', $columns, true) ? 'id' : 'migration';
    $statement = $db->query('SELECT migration, batch, ' . $executedAtSelect . ' FROM migrations ORDER BY batch ASC, ' . $orderColumn . ' ASC');
    if (!$statement) {
        return [];
    }

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/** @return array<string, array<int, string>> */
function trackedTables(): array
{
    return [
        'migrations' => ['migration', 'batch'],
        'tbl_news' => ['slug', 'contributor_redaksi', 'contributor_pewarta', 'contributor_editor', 'content_json', 'status', 'published_at', 'created_at', 'updated_at', 'deleted_at', 'comments_enabled', 'voting_enabled', 'replies_enabled', 'max_reply_depth'],
        'tbl_news_comment' => ['comment_id', 'news_id', 'parent_id', 'name', 'email', 'content', 'status', 'moderated_by', 'moderated_at', 'deleted_at'],
        'tbl_news_comment_vote' => ['vote_id', 'comment_id', 'news_id', 'voter_key', 'value'],
        'tbl_prestasi' => ['prestasi_id', 'slug', 'title', 'category', 'year', 'member_name', 'institution', 'status', 'deleted_at'],
        'tbl_prestasi_submission_token' => ['token_id', 'token_hash', 'max_uses', 'used_count', 'used_at', 'expires_at'],
        'tbl_prestasi_submission' => ['submission_id', 'token_id', 'prestasi_id', 'submitter_name', 'submitter_email', 'payload_json', 'created_at'],
        'tbl_team_member' => ['komisariat', 'divisi', 'jabatan', 'divisi_lain', 'tahun', 'status', 'deleted_at'],
        'tbl_user' => ['password', 'remember_token_hash', 'last_login_at', 'last_login_ip', 'failed_login_count', 'locked_until'],
        'tbl_audit_log' => ['audit_id', 'user_id', 'action', 'entity_type', 'entity_id', 'created_at'],
        'teams' => ['show_on_home', 'home_sort_order', 'deleted_at'],
        'tbl_feature' => ['title', 'name', 'description', 'focus', 'icon_key', 'show_on_home', 'sort_order', 'status', 'created_at', 'updated_at', 'deleted_at'],
        'tbl_feature_image' => ['id', 'feature_id', 'image_path', 'sort_order', 'created_at', 'updated_at'],
        'tbl_contact_setting' => ['id', 'place_name', 'address', 'email', 'phone', 'coordinates_label', 'maps_url', 'updated_at'],
        'tbl_event' => ['event_id', 'slug', 'event_title', 'event_content', 'event_start_date', 'event_end_date', 'status', 'deleted_at', 'created_at', 'updated_at'],
        'tbl_setting' => ['setting_key', 'setting_value', 'setting_type', 'description', 'updated_by', 'updated_at'],
        'tbl_photo_gallery' => ['photo_id', 'title', 'image_url', 'caption', 'status', 'sort_order', 'deleted_at'],
    ];
}

/** @param array<int, string> $expectedColumns */
function describeTable(PDO $db, string $table, array $expectedColumns): void
{
    if (!tableExists($db, $table)) {
        printf("%s: missing table\n", $table);
        return;
    }

    $columns = columnsFor($db, $table);
    $indexes = indexesFor($db, $table);
    $missingColumns = array_values(array_diff($expectedColumns, $columns));

    printf(
        '%s: present, %d columns, %d indexes%s%s',
        $table,
        count($columns),
        count($indexes),
        $missingColumns !== [] ? ', missing tracked columns: ' . implode(', ', $missingColumns) : '',
        PHP_EOL
    );

    if ($indexes !== []) {
        printf("  indexes: %s\n", implode(', ', $indexes));
    }
}

function tableExists(PDO $db, string $table): bool
{
    $statement = $db->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table LIMIT 1');
    $statement->execute(['table' => $table]);

    return (bool) $statement->fetchColumn();
}

/** @return array<int, string> */
function columnsFor(PDO $db, string $table): array
{
    $statement = $db->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table ORDER BY ordinal_position ASC');
    $statement->execute(['table' => $table]);

    return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

/** @return array<int, string> */
function indexesFor(PDO $db, string $table): array
{
    $statement = $db->prepare('SELECT DISTINCT index_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = :table ORDER BY index_name ASC');
    $statement->execute(['table' => $table]);

    return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

function printLine(string $line): void
{
    echo $line . PHP_EOL;
}
