<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS tbl_buku (
                buku_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                judul VARCHAR(255) NOT NULL,
                file_path VARCHAR(1000) NOT NULL,
                penulis VARCHAR(255) NOT NULL,
                editor VARCHAR(255) NULL,
                deskripsi TEXT NULL,
                sinopsis TEXT NULL,
                cover_image VARCHAR(1000) NULL,
                tahun_terbit SMALLINT UNSIGNED NULL,
                kategori VARCHAR(100) NULL,
                status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
                download_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,

                PRIMARY KEY (buku_id),
                INDEX idx_buku_status (status),
                INDEX idx_buku_kategori (kategori),
                INDEX idx_buku_deleted (deleted_at)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");
    },
];
