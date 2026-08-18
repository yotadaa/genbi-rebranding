<?php

return [
    'up' => function (\PDO $db) {
        $db->exec("ALTER TABLE tbl_transaksi_wilayah ADD COLUMN jenis_entri ENUM('kegiatan', 'invoice') NOT NULL DEFAULT 'kegiatan' AFTER kegiatan_id");
        $db->exec("ALTER TABLE tbl_transaksi_unja ADD COLUMN jenis_entri ENUM('kegiatan', 'invoice') NOT NULL DEFAULT 'kegiatan' AFTER kegiatan_id");
        $db->exec("ALTER TABLE tbl_transaksi_uin ADD COLUMN jenis_entri ENUM('kegiatan', 'invoice') NOT NULL DEFAULT 'kegiatan' AFTER kegiatan_id");
    },
    'down' => function (\PDO $db) {
        $db->exec("ALTER TABLE tbl_transaksi_wilayah DROP COLUMN jenis_entri");
        $db->exec("ALTER TABLE tbl_transaksi_unja DROP COLUMN jenis_entri");
        $db->exec("ALTER TABLE tbl_transaksi_uin DROP COLUMN jenis_entri");
    }
];
