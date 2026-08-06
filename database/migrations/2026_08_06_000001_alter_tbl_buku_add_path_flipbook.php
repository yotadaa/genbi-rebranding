<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_buku');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        $add = static function (string $sql) use ($db): void {
            try {
                $db->exec($sql);
            } catch (\Throwable) {
            }
        };

        // Tambahkan kolom path_flipbook setelah file_path
        if (!in_array('path_flipbook', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN path_flipbook VARCHAR(1000) NULL AFTER file_path');
        }
    },
];
