<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        
        // 1. Create tbl_kegiatan_keuangan
        $sqlKegiatan = "
        CREATE TABLE IF NOT EXISTS tbl_kegiatan_keuangan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            
            nama_kegiatan VARCHAR(255) NOT NULL,
            tingkat ENUM('wilayah', 'komsat unja', 'komsat uin') NOT NULL,
            divisi VARCHAR(100) NULL,
            tanggal_mulai DATE NULL,
            tanggal_selesai DATE NULL,
            keterangan_kegiatan TEXT NULL,
            
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $db->exec($sqlKegiatan);
        } catch (\Throwable $e) {
            // Log atau abaikan jika tabel sudah ada
        }

        // 2. Create tbl_transaksi_wilayah
        $sqlTransaksi = "
        CREATE TABLE IF NOT EXISTS tbl_transaksi_wilayah (
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
            $db->exec($sqlTransaksi);
        } catch (\Throwable $e) {
            // Log atau abaikan jika tabel sudah ada
        }
    },
];
