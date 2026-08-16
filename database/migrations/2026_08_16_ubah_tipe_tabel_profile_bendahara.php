<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $sql = "
            ALTER TABLE tbl_profil_bendahara 
            MODIFY COLUMN tempat VARCHAR(50) NOT NULL DEFAULT 'wilayah'
        ";

        try {
            $db->exec($sql);
        } catch (\Throwable $e) {
            // Log atau abaikan jika sudah diubah
        }
    },
    'down' => static function (\PDO $db): void {
        $sql = "
            ALTER TABLE tbl_profil_bendahara 
            MODIFY COLUMN tempat ENUM('wilayah','komsat unja','komsat uin') NOT NULL DEFAULT 'wilayah'
        ";

        try {
            $db->exec($sql);
        } catch (\Throwable $e) {
            // Abaikan jika rollback gagal
        }
    }
];
