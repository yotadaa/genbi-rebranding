<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_feature (
            feature_id INT NOT NULL AUTO_INCREMENT,
            title VARCHAR(120) NULL,
            name VARCHAR(255) NULL,
            description TEXT NULL,
            focus VARCHAR(120) NULL,
            icon_key VARCHAR(80) NULL,
            show_on_home TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (feature_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $columns = [];
        try {
            $statement = $db->query('DESCRIBE tbl_feature');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            $columns = [];
        }

        $alterStatements = [];
        if (!in_array('title', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN title VARCHAR(120) NULL';
        }
        if (!in_array('name', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN name VARCHAR(255) NULL';
        }
        if (!in_array('description', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN description TEXT NULL';
        }
        if (!in_array('focus', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN focus VARCHAR(120) NULL';
        }
        if (!in_array('icon_key', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN icon_key VARCHAR(80) NULL';
        }
        if (!in_array('show_on_home', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 1';
        }
        if (!in_array('sort_order', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN sort_order INT NOT NULL DEFAULT 0';
        }
        if (!in_array('status', $columns, true)) {
            $alterStatements[] = "ADD COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'published'";
        }
        if (!in_array('created_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
        }
        if (!in_array('updated_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN updated_at DATETIME NULL';
        }
        if (!in_array('deleted_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN deleted_at DATETIME NULL';
        }

        if ($alterStatements !== []) {
            $db->exec('ALTER TABLE tbl_feature ' . implode(', ', $alterStatements));
        }

        $db->exec("CREATE TABLE IF NOT EXISTS tbl_feature_image (
            id INT NOT NULL AUTO_INCREMENT,
            feature_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            INDEX idx_tbl_feature_image_feature (feature_id, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $featureKey = in_array('feature_id', $columns, true) ? 'feature_id' : (in_array('id', $columns, true) ? 'id' : null);
        if ($featureKey !== null) {
            try {
                $statement = $db->query("
                    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                    WHERE CONSTRAINT_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'tbl_feature_image'
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                      AND CONSTRAINT_NAME = 'fk_tbl_feature_image_feature'
                ");
                $exists = (int) ($statement?->fetchColumn() ?? 0) > 0;
                if (!$exists) {
                    $db->exec('ALTER TABLE tbl_feature_image ADD CONSTRAINT fk_tbl_feature_image_feature FOREIGN KEY (feature_id) REFERENCES tbl_feature(' . $featureKey . ') ON DELETE CASCADE');
                }
            } catch (\Throwable) {
                // Legacy databases may not support the FK yet; keep migration non-fatal.
            }
        }

        try {
            $db->exec('CREATE INDEX idx_tbl_feature_home ON tbl_feature (show_on_home, sort_order)');
        } catch (\Throwable) {
        }
        try {
            $db->exec('CREATE INDEX idx_tbl_feature_status ON tbl_feature (status)');
        } catch (\Throwable) {
        }
    },
];
