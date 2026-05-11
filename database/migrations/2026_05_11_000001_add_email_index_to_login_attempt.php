<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('SHOW INDEX FROM tbl_login_attempt');
            $indexes = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Key_name') : [];
        } catch (\Throwable) {
            return;
        }

        if (!in_array('idx_login_attempt_email', $indexes, true)) {
            $db->exec('ALTER TABLE tbl_login_attempt ADD INDEX idx_login_attempt_email (email_normalized, first_attempt_at)');
        }
    },
];
