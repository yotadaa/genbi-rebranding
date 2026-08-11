<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        $sql = "
        CREATE TABLE IF NOT EXISTS tbl_transaksi_genbi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bendahara_id INT NOT NULL,
            keterangan_transaksi TEXT NOT NULL,
            jumlah_transaksi DECIMAL(15, 2) NOT NULL,
            tanggal_transaksi DATE NOT NULL,
            tipe_transaksi ENUM('pemasukan', 'pengeluaran') NOT NULL,
            sumber_dana VARCHAR(255) NULL,
            alokasi_dana VARCHAR(255) NULL,
            sumber_penerima_dana VARCHAR(255) NULL,
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
