<?php

declare(strict_types=1);

return [
    'up' => function (\PDO $db) {
        $tables = [
            'tbl_transaksi_wilayah',
            'tbl_transaksi_unja',
            'tbl_transaksi_uin'
        ];

        foreach ($tables as $table) {
            $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'alokasi_dana'");
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `alokasi_dana` VARCHAR(255) NULL AFTER `sumber_dana`");
            }
        }
    },
    'down' => function (\PDO $db) {
        $tables = [
            'tbl_transaksi_wilayah',
            'tbl_transaksi_unja',
            'tbl_transaksi_uin'
        ];

        foreach ($tables as $table) {
            $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'alokasi_dana'");
            if ($stmt->fetch()) {
                $db->exec("ALTER TABLE `$table` DROP COLUMN `alokasi_dana`");
            }
        }
    }
];
