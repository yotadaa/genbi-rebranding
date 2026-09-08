<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_presensi_event (
            presensi_event_id INT NOT NULL AUTO_INCREMENT,
            slug VARCHAR(255) NOT NULL,
            public_token VARCHAR(96) NOT NULL,
            public_token_hash CHAR(64) NOT NULL,
            event_name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            roles_json LONGTEXT NOT NULL,
            status ENUM('draft','open','closed','archived') NOT NULL DEFAULT 'open',
            created_by INT NULL,
            updated_by INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (presensi_event_id),
            UNIQUE KEY uq_tbl_presensi_event_slug (slug),
            UNIQUE KEY uq_tbl_presensi_event_token (public_token),
            UNIQUE KEY uq_tbl_presensi_event_token_hash (public_token_hash),
            INDEX idx_tbl_presensi_event_status (status),
            INDEX idx_tbl_presensi_event_deleted_at (deleted_at),
            INDEX idx_tbl_presensi_event_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS tbl_presensi_event_member (
            event_member_id INT NOT NULL AUTO_INCREMENT,
            presensi_event_id INT NOT NULL,
            team_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (event_member_id),
            UNIQUE KEY uq_tbl_presensi_event_member (presensi_event_id, team_id),
            INDEX idx_tbl_presensi_event_member_team (team_id),
            CONSTRAINT fk_tbl_presensi_event_member_event
                FOREIGN KEY (presensi_event_id)
                REFERENCES tbl_presensi_event (presensi_event_id)
                ON DELETE CASCADE,
            CONSTRAINT fk_tbl_presensi_event_member_team
                FOREIGN KEY (team_id)
                REFERENCES teams (id)
                ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
