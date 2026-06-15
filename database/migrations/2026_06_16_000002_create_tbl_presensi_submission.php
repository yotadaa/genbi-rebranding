<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_presensi_submission (
            submission_id INT NOT NULL AUTO_INCREMENT,
            presensi_event_id INT NOT NULL,
            team_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(120) NOT NULL,
            photo_path VARCHAR(255) NOT NULL,
            status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            approved_by INT NULL,
            approved_at DATETIME NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (submission_id),
            UNIQUE KEY uq_tbl_presensi_submission_member (presensi_event_id, team_id),
            INDEX idx_tbl_presensi_submission_status (status),
            INDEX idx_tbl_presensi_submission_created_at (created_at),
            INDEX idx_tbl_presensi_submission_event_status (presensi_event_id, status),
            INDEX idx_tbl_presensi_submission_team (team_id),
            CONSTRAINT fk_tbl_presensi_submission_event
                FOREIGN KEY (presensi_event_id)
                REFERENCES tbl_presensi_event (presensi_event_id)
                ON DELETE CASCADE,
            CONSTRAINT fk_tbl_presensi_submission_team
                FOREIGN KEY (team_id)
                REFERENCES teams (id)
                ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
