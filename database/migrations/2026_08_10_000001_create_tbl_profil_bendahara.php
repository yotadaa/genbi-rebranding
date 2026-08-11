<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        $sql = "
        CREATE TABLE IF NOT EXISTS tbl_profil_bendahara (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            nama_bendahara VARCHAR(150) NOT NULL,
            tahun_periode_awal YEAR NOT NULL,
            tahun_periode_akhir YEAR NOT NULL,
            tempat ENUM('wilayah', 'komsat unja', 'komsat uin') NOT NULL,
            jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
            program_studi VARCHAR(100) NOT NULL,
            semester_studi INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $db->exec($sql);
        } catch (\Throwable $e) {
            // Log atau abaikan jika tabel sudah ada
        }
    },
];
