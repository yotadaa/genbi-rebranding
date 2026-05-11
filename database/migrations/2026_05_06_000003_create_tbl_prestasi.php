<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_prestasi (
            prestasi_id INT NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            year INT NOT NULL,
            member_name VARCHAR(120) NOT NULL,
            institution VARCHAR(120) NULL,
            description TEXT NOT NULL,
            detail LONGTEXT NULL,
            photo VARCHAR(120) NULL,
            certificate_photo VARCHAR(120) NULL,
            status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            meta_title VARCHAR(255) NULL,
            meta_keyword TEXT NULL,
            meta_description TEXT NULL,
            created_by INT NULL,
            updated_by INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (prestasi_id),
            UNIQUE KEY uq_tbl_prestasi_slug (slug),
            INDEX idx_tbl_prestasi_category (category),
            INDEX idx_tbl_prestasi_year (year),
            INDEX idx_tbl_prestasi_status (status),
            INDEX idx_tbl_prestasi_featured (is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
