<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_setting (
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT NULL,
            setting_type ENUM('string','int','bool','json') NOT NULL DEFAULT 'string',
            description VARCHAR(255) NULL,
            updated_by INT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
