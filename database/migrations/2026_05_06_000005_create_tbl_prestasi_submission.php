<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS tbl_prestasi_submission (
            submission_id INT NOT NULL AUTO_INCREMENT,
            token_id INT NOT NULL,
            prestasi_id INT NULL,
            submitter_name VARCHAR(120) NOT NULL,
            submitter_email VARCHAR(120) NOT NULL,
            payload_json LONGTEXT NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (submission_id),
            INDEX idx_prestasi_submission_token_id (token_id),
            INDEX idx_prestasi_submission_prestasi_id (prestasi_id),
            CONSTRAINT fk_prestasi_submission_token FOREIGN KEY (token_id) REFERENCES tbl_prestasi_submission_token(token_id) ON DELETE CASCADE,
            CONSTRAINT fk_prestasi_submission_prestasi FOREIGN KEY (prestasi_id) REFERENCES tbl_prestasi(prestasi_id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    },
];
