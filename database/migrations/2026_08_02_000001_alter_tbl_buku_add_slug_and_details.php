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

        // 1. Tambahkan Slug setelah judul (Untuk URL SEO-friendly di web)
        if (!in_array('slug', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN slug VARCHAR(255) NULL AFTER judul');
        }

        // 2. Tambahkan foto_cover_buku setelah sinopsis
        if (!in_array('foto_cover_buku', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN foto_cover_buku VARCHAR(1000) NULL AFTER sinopsis');
        }

        // 3. Tambahkan penerbit setelah editor
        if (!in_array('penerbit', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN penerbit VARCHAR(255) NULL AFTER editor');
        }

        // 4. Tambahkan isbn & page_count (jumlah halaman) setelah tahun_terbit
        if (!in_array('isbn', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN isbn VARCHAR(50) NULL AFTER tahun_terbit');
        }
        if (!in_array('page_count', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN page_count SMALLINT UNSIGNED NULL AFTER isbn');
        }

        // 5. Tambahkan view_count (jumlah dibaca/dilihat) setelah status
        if (!in_array('view_count', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN view_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER status');
        }

        // 6. Tambahkan published_at (waktu tayang ke publik) setelah view_count
        if (!in_array('published_at', $columns, true)) {
            $add('ALTER TABLE tbl_buku ADD COLUMN published_at DATETIME NULL AFTER view_count');
        }

        // 7. Tambahkan Index untuk mempercepat query pencarian & filter
        $add('ALTER TABLE tbl_buku ADD UNIQUE KEY uq_tbl_buku_slug (slug)');
        $add('ALTER TABLE tbl_buku ADD INDEX idx_tbl_buku_published_at (published_at)');
        $add('ALTER TABLE tbl_buku ADD INDEX idx_tbl_buku_isbn (isbn)');
    },
];
