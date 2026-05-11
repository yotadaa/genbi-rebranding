<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS tbl_prestasi_submission_token (
            token_id INT NOT NULL AUTO_INCREMENT,
            token_hash CHAR(64) NOT NULL,
            label VARCHAR(120) NULL,
            intended_for VARCHAR(120) NULL,
            max_uses TINYINT UNSIGNED NOT NULL DEFAULT 1,
            used_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
            expires_at DATETIME NULL,
            used_at DATETIME NULL,
            created_by INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            revoked_at DATETIME NULL,
            PRIMARY KEY (token_id),
            UNIQUE KEY uq_prestasi_token_hash (token_hash),
            INDEX idx_prestasi_token_expires_at (expires_at),
            INDEX idx_prestasi_token_used_at (used_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    },
];
