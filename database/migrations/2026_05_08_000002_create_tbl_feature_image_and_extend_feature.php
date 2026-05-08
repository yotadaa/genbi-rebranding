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
            PRIMARY KEY (feature_id),
            INDEX idx_tbl_feature_home (show_on_home, sort_order),
            INDEX idx_tbl_feature_status (status)
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
            $alterStatements[] = 'ADD COLUMN title VARCHAR(120) NULL AFTER feature_id';
        }
        if (!in_array('name', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN name VARCHAR(255) NULL AFTER title';
        }
        if (!in_array('description', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN description TEXT NULL AFTER name';
        }
        if (!in_array('focus', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN focus VARCHAR(120) NULL AFTER description';
        }
        if (!in_array('icon_key', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN icon_key VARCHAR(80) NULL AFTER focus';
        }
        if (!in_array('show_on_home', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 1 AFTER icon_key';
        }
        if (!in_array('sort_order', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER show_on_home';
        }
        if (!in_array('status', $columns, true)) {
            $alterStatements[] = "ADD COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'published' AFTER sort_order";
        }
        if (!in_array('created_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER status';
        }
        if (!in_array('updated_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN updated_at DATETIME NULL AFTER created_at';
        }
        if (!in_array('deleted_at', $columns, true)) {
            $alterStatements[] = 'ADD COLUMN deleted_at DATETIME NULL AFTER updated_at';
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
            INDEX idx_tbl_feature_image_feature (feature_id, sort_order),
            CONSTRAINT fk_tbl_feature_image_feature FOREIGN KEY (feature_id) REFERENCES tbl_feature(feature_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
