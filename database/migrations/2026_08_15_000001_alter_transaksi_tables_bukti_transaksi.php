<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        // Memperbesar kolom bukti_transaksi menjadi VARCHAR(1000) agar muat untuk link Google Drive yang panjang
        // Untuk ketiga tabel transaksi
        
        $sqlAlterWilayah = "ALTER TABLE tbl_transaksi_wilayah MODIFY COLUMN bukti_transaksi VARCHAR(1000) NULL;";
        $sqlAlterUnja = "ALTER TABLE tbl_transaksi_unja MODIFY COLUMN bukti_transaksi VARCHAR(1000) NULL;";
        $sqlAlterUin = "ALTER TABLE tbl_transaksi_uin MODIFY COLUMN bukti_transaksi VARCHAR(1000) NULL;";

        try {
            $db->exec($sqlAlterWilayah);
            $db->exec($sqlAlterUnja);
            $db->exec($sqlAlterUin);
        } catch (\Throwable $e) {
            // Abaikan jika ada error
        }
    },
];
