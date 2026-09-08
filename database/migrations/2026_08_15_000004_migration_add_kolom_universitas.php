<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $sql = "
            ALTER TABLE tbl_profil_bendahara 
            ADD COLUMN universitas VARCHAR(150) NOT NULL AFTER jenis_kelamin
        ";

        try {
            $db->exec($sql);
        } catch (\Throwable $e) {
            // Log atau abaikan jika tabel sudah memiliki kolom universitas
        }
    },
];
