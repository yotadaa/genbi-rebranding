<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        $sql = "
        CREATE TABLE IF NOT EXISTS tbl_kegiatan_genbi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bendahara_id INT NOT NULL,
            nama_kegiatan VARCHAR(255) NOT NULL,
            tipe ENUM('wilayah', 'komsat unja', 'komsat uin') NOT NULL,
            divisi VARCHAR(100) NOT NULL,
            periode_kegiatan VARCHAR(100) NOT NULL,
            keterangan TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (bendahara_id) REFERENCES tbl_profil_bendahara(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $db->exec($sql);
        } catch (\Throwable $e) {
            // Log atau abaikan jika tabel sudah ada
        }
    },
];
