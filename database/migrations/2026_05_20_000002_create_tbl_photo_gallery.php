<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS tbl_photo_gallery (
            photo_id INT NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            image_url VARCHAR(1000) NOT NULL,
            caption TEXT NULL,
            status ENUM('show','hide') NOT NULL DEFAULT 'show',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (photo_id),
            INDEX idx_photo_gallery_status (status),
            INDEX idx_photo_gallery_sort (sort_order),
            INDEX idx_photo_gallery_deleted (deleted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    },
];
