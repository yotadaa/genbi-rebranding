<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $columns = $db
            ->query('SHOW COLUMNS FROM tbl_buku')
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('file_original_name', $columns, true)) {
            $db->exec(
                'ALTER TABLE tbl_buku
                 ADD COLUMN file_original_name VARCHAR(255) NULL
                 AFTER file_path'
            );
        }

        if (!in_array('file_size_bytes', $columns, true)) {
            $db->exec(
                'ALTER TABLE tbl_buku
                 ADD COLUMN file_size_bytes BIGINT UNSIGNED NULL
                 AFTER file_original_name'
            );
        }

        if (!in_array('file_mime_type', $columns, true)) {
            $db->exec(
                'ALTER TABLE tbl_buku
                 ADD COLUMN file_mime_type VARCHAR(100) NULL
                 AFTER file_size_bytes'
            );
        }
    },
];
