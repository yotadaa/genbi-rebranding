<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_user');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        $add = static function (string $sql) use ($db): void {
            try { $db->exec($sql); } catch (\Throwable) {}
        };

        // Widen password column (safe to re-run)
        $add('ALTER TABLE tbl_user MODIFY password VARCHAR(255) NOT NULL');

        if (!in_array('remember_token_hash', $columns, true)) {
            $add('ALTER TABLE tbl_user ADD COLUMN remember_token_hash CHAR(64) NULL AFTER token');
        }
        if (!in_array('last_login_at', $columns, true)) {
            $add('ALTER TABLE tbl_user ADD COLUMN last_login_at DATETIME NULL AFTER status');
        }
        if (!in_array('last_login_ip', $columns, true)) {
            $add('ALTER TABLE tbl_user ADD COLUMN last_login_ip VARCHAR(45) NULL AFTER last_login_at');
        }
        if (!in_array('failed_login_count', $columns, true)) {
            $add('ALTER TABLE tbl_user ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0 AFTER last_login_ip');
        }
        if (!in_array('locked_until', $columns, true)) {
            $add('ALTER TABLE tbl_user ADD COLUMN locked_until DATETIME NULL AFTER failed_login_count');
        }
    },
];
