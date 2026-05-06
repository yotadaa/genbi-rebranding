<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    public function __construct(private PDO $db, private string $migrationPath)
    {
    }

    /** @return array<int, string> */
    public function pending(): array
    {
        $files = $this->migrationFiles();
        $executed = $this->executedMigrations();

        return array_values(array_filter($files, static fn (string $file): bool => !in_array(basename($file), $executed, true)));
    }

    /** @return array<int, string> */
    public function run(): array
    {
        $this->ensureMigrationsTable();
        $pending = $this->pending();
        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatch();
        $applied = [];

        foreach ($pending as $file) {
            $migration = require $file;
            if (!is_array($migration) || !is_callable($migration['up'] ?? null)) {
                throw new RuntimeException('Invalid migration file: ' . basename($file));
            }

            try {
                $migration['up']($this->db);
                $statement = $this->db->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
                $statement->execute([
                    'migration' => basename($file),
                    'batch' => $batch,
                ]);
            } catch (\Throwable $error) {
                throw $error;
            }

            $applied[] = basename($file);
        }

        return $applied;
    }

    public function ensureMigrationsTable(): void
    {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS migrations (' .
            'id INT UNSIGNED NOT NULL AUTO_INCREMENT, ' .
            'migration VARCHAR(255) NOT NULL, ' .
            'batch INT NOT NULL, ' .
            'executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ' .
            'PRIMARY KEY (id)' .
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /** @return array<int, string> */
    private function migrationFiles(): array
    {
        $files = glob(rtrim($this->migrationPath, '/') . '/*.php') ?: [];
        sort($files, SORT_STRING);

        return $files;
    }

    /** @return array<int, string> */
    private function executedMigrations(): array
    {
        $this->ensureMigrationsTable();
        $statement = $this->db->prepare('SELECT migration FROM migrations ORDER BY id ASC');
        $statement->execute();

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function nextBatch(): int
    {
        $statement = $this->db->prepare('SELECT MAX(batch) FROM migrations');
        $statement->execute();
        $current = (int) $statement->fetchColumn();

        return $current + 1;
    }
}
