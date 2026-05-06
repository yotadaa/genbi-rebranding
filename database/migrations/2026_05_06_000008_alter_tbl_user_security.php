<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('ALTER TABLE tbl_user
            MODIFY password VARCHAR(255) NOT NULL,
            ADD COLUMN remember_token_hash CHAR(64) NULL AFTER token,
            ADD COLUMN last_login_at DATETIME NULL AFTER status,
            ADD COLUMN last_login_ip VARCHAR(45) NULL AFTER last_login_at,
            ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0 AFTER last_login_ip,
            ADD COLUMN locked_until DATETIME NULL AFTER failed_login_count');
    },
];
