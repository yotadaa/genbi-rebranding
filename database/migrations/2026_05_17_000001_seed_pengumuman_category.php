<?php

declare(strict_types=1);

return [
    'up' => static function (PDO $db): void {
        $exists = $db->prepare('SELECT category_id FROM tbl_category WHERE LOWER(category_name) = LOWER(:name) LIMIT 1');
        $exists->execute([':name' => 'Pengumuman']);
        $categoryId = $exists->fetchColumn();

        if ($categoryId !== false) {
            $update = $db->prepare(
                'UPDATE tbl_category
                 SET category_name = :name,
                     meta_title = CASE WHEN meta_title = \'\' THEN :meta_title ELSE meta_title END,
                     meta_keyword = CASE WHEN meta_keyword = \'\' THEN :meta_keyword ELSE meta_keyword END,
                     meta_description = CASE WHEN meta_description = \'\' THEN :meta_description ELSE meta_description END
                 WHERE category_id = :id'
            );
            $update->execute([
                ':id' => (int) $categoryId,
                ':name' => 'Pengumuman',
                ':meta_title' => 'Pengumuman',
                ':meta_keyword' => 'Pengumuman GenBI Jambi, informasi GenBI Jambi, pengumuman resmi',
                ':meta_description' => 'Pengumuman resmi GenBI Provinsi Jambi untuk anggota dan publik.',
            ]);
            return;
        }

        $insert = $db->prepare(
            'INSERT INTO tbl_category (category_name, category_banner, meta_title, meta_keyword, meta_description)
             VALUES (:name, :banner, :meta_title, :meta_keyword, :meta_description)'
        );
        $insert->execute([
            ':name' => 'Pengumuman',
            ':banner' => '',
            ':meta_title' => 'Pengumuman',
            ':meta_keyword' => 'Pengumuman GenBI Jambi, informasi GenBI Jambi, pengumuman resmi',
            ':meta_description' => 'Pengumuman resmi GenBI Provinsi Jambi untuk anggota dan publik.',
        ]);
    },

    'down' => static function (PDO $db): void {
        // Keep production content safe. This seed is harmless if retained.
        // If a rollback is ever required, delete the category manually only after
        // confirming no tbl_news rows reference it.
    },
];
