<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $tables = ['tbl_transaksi_unja', 'tbl_transaksi_uin', 'tbl_transaksi_wilayah'];
        
        foreach ($tables as $table) {
            try {
                // Cek apakah kolom masih ada sebelum dihapus untuk menghindari error
                $checkAlokasi = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'alokasi_dana'")->fetch();
                $checkSumber = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'sumber_penerima_dana'")->fetch();
                
                if ($checkAlokasi || $checkSumber) {
                    $dropQuery = "ALTER TABLE `{$table}` ";
                    $drops = [];
                    
                    if ($checkAlokasi) $drops[] = "DROP COLUMN `alokasi_dana`";
                    if ($checkSumber) $drops[] = "DROP COLUMN `sumber_penerima_dana`";
                    
                    $dropQuery .= implode(", ", $drops) . ";";
                    
                    $db->exec($dropQuery);
                }
            } catch (\Throwable $e) {
                // Abaikan jika ada error (kolom sudah hilang atau tabel tidak ada)
            }
        }
    },

    'down' => static function (\PDO $db): void {
        $tables = ['tbl_transaksi_unja', 'tbl_transaksi_uin', 'tbl_transaksi_wilayah'];
        
        foreach ($tables as $table) {
            try {
                $db->exec("ALTER TABLE `{$table}` 
                    ADD COLUMN `alokasi_dana` VARCHAR(255) NULL AFTER `sumber_dana`,
                    ADD COLUMN `sumber_penerima_dana` VARCHAR(255) NULL AFTER `alokasi_dana`;");
            } catch (\Throwable $e) {
                // Abaikan jika error
            }
        }
    }
];
