<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_genbi_point_activity (
            activity_id INT NOT NULL AUTO_INCREMENT,
            team_id BIGINT UNSIGNED NOT NULL,
            activity_name VARCHAR(255) NOT NULL,
            points INT NOT NULL DEFAULT 0,
            activity_date DATE NULL,
            created_by INT NULL,
            updated_by INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (activity_id),
            INDEX idx_tbl_genbi_point_team (team_id),
            INDEX idx_tbl_genbi_point_activity_date (activity_date),
            INDEX idx_tbl_genbi_point_deleted_at (deleted_at),
            CONSTRAINT fk_tbl_genbi_point_team
                FOREIGN KEY (team_id)
                REFERENCES teams (id)
                ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
