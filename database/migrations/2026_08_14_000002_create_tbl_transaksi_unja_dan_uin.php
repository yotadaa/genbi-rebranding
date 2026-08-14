<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        // 1. Create tbl_transaksi_unja
        $sqlUnja = "
        CREATE TABLE IF NOT EXISTS tbl_transaksi_unja (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            kegiatan_id INT NULL,
            
            dicatat_oleh VARCHAR(150) NOT NULL,
            periode_kepengurusan VARCHAR(50) NOT NULL,
            
            tipe_transaksi ENUM('pemasukan', 'pengeluaran') NOT NULL,
            nominal DECIMAL(15, 2) NOT NULL,
            tanggal_transaksi DATE NOT NULL,
            keterangan_transaksi TEXT NOT NULL,
            
            sumber_dana VARCHAR(255) NULL,
            alokasi_dana VARCHAR(255) NULL,
            sumber_penerima_dana VARCHAR(255) NULL,
            bukti_transaksi VARCHAR(255) NULL,
            
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (kegiatan_id) REFERENCES tbl_kegiatan_keuangan(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $db->exec($sqlUnja);
        } catch (\Throwable $e) {
            // Abaikan jika sudah ada
        }

        // 2. Create tbl_transaksi_uin
        $sqlUin = "
        CREATE TABLE IF NOT EXISTS tbl_transaksi_uin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            kegiatan_id INT NULL,
            
            dicatat_oleh VARCHAR(150) NOT NULL,
            periode_kepengurusan VARCHAR(50) NOT NULL,
            
            tipe_transaksi ENUM('pemasukan', 'pengeluaran') NOT NULL,
            nominal DECIMAL(15, 2) NOT NULL,
            tanggal_transaksi DATE NOT NULL,
            keterangan_transaksi TEXT NOT NULL,
            
            sumber_dana VARCHAR(255) NULL,
            alokasi_dana VARCHAR(255) NULL,
            sumber_penerima_dana VARCHAR(255) NULL,
            bukti_transaksi VARCHAR(255) NULL,
            
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (kegiatan_id) REFERENCES tbl_kegiatan_keuangan(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $db->exec($sqlUin);
        } catch (\Throwable $e) {
            // Abaikan jika sudah ada
        }
    },
];
