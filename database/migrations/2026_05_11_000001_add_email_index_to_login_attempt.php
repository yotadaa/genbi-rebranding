<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('ALTER TABLE tbl_login_attempt ADD INDEX idx_login_attempt_email (email_normalized, first_attempt_at)');
    },
];
