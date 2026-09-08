<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS tbl_login_attempt (
            login_attempt_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email_normalized VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_count INT NOT NULL DEFAULT 0,
            first_attempt_at DATETIME NOT NULL,
            last_attempt_at DATETIME NOT NULL,
            locked_until DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uniq_login_attempt_email_ip (email_normalized, ip_address),
            KEY idx_login_attempt_locked_until (locked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    },
];
