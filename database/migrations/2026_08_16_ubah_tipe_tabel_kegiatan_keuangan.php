<?php

return [
    'up' => function (\PDO $db) {
        $db->exec("ALTER TABLE tbl_kegiatan_keuangan MODIFY COLUMN tingkat VARCHAR(50) NOT NULL");
    },
    'down' => function (\PDO $db) {
        // Revert back to ENUM
        $db->exec("ALTER TABLE tbl_kegiatan_keuangan MODIFY COLUMN tingkat ENUM('wilayah','komsat unja','komsat uin') NOT NULL");
    }
];
