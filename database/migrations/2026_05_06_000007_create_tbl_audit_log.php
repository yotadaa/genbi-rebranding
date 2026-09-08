<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS tbl_audit_log (
            audit_id BIGINT NOT NULL AUTO_INCREMENT,
            user_id INT NULL,
            action VARCHAR(80) NOT NULL,
            entity_type VARCHAR(80) NOT NULL,
            entity_id VARCHAR(80) NULL,
            old_data LONGTEXT NULL,
            new_data LONGTEXT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (audit_id),
            INDEX idx_audit_user_id (user_id),
            INDEX idx_audit_entity (entity_type, entity_id),
            INDEX idx_audit_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    },
];
