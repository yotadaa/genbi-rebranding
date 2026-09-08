<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_event');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            try {
                $statement = $db->query('PRAGMA table_info(tbl_event)');
                $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
                $columns = array_column($rows, 'name');
            } catch (\Throwable) {
                $columns = [];
            }
        }

        if ($columns === []) {
            $db->exec("CREATE TABLE IF NOT EXISTS tbl_event (
                event_id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_title VARCHAR(255) NOT NULL,
                event_content LONGTEXT NULL,
                event_content_short TEXT NULL,
                event_start_date DATE NULL,
                event_end_date DATE NULL,
                event_location VARCHAR(255) NULL,
                event_map TEXT NULL,
                photo VARCHAR(255) NULL,
                banner VARCHAR(255) NULL,
                meta_title VARCHAR(255) NULL,
                meta_keyword VARCHAR(255) NULL,
                meta_description TEXT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'published',
                deleted_at DATETIME NULL,
                created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP
            )");
            return;
        }

        $addColumn = static function (string $sql) use ($db): void {
            try {
                $db->exec($sql);
            } catch (\Throwable) {
                // Keep idempotent/non-fatal on legacy schemas.
            }
        };

        if (!in_array('meta_title', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN meta_title VARCHAR(255) NULL');
        }
        if (!in_array('meta_keyword', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN meta_keyword VARCHAR(255) NULL');
        }
        if (!in_array('meta_description', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN meta_description TEXT NULL');
        }
        if (!in_array('status', $columns, true)) {
            $addColumn("ALTER TABLE tbl_event ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT 'published'");
        }
        if (!in_array('deleted_at', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN deleted_at DATETIME NULL');
        }
        if (!in_array('created_at', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
        }
        if (!in_array('updated_at', $columns, true)) {
            $addColumn('ALTER TABLE tbl_event ADD COLUMN updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
        }

        try {
            $db->exec("UPDATE tbl_event SET status = 'published' WHERE status IS NULL OR status = ''");
        } catch (\Throwable) {
            // Ignore legacy/SQLite differences.
        }
    },
];
