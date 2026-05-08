<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_feature');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        if (!in_array('name', $columns, true) || !in_array('description', $columns, true)) {
            return;
        }

        $entries = [
            [
                'title' => 'KKG',
                'name' => 'Kegiatan Kenal GenBI',
                'description' => 'Kegiatan kenal GenBI memberi manfaat lewat jaringan yang luas, ruang pertukaran ide dan pengalaman, serta penguatan keterampilan sosial dan kepemimpinan anggota.',
                'focus' => 'Internal komunitas',
                'icon_key' => 'users',
                'legacy_icon' => 'fa-users',
                'sort_order' => 1,
            ],
            [
                'title' => 'SIGINJAI',
                'name' => 'Semarak Ekonomi Syariah Negeri Jambi',
                'description' => 'Program kolaboratif untuk mendorong pertumbuhan ekonomi syariah, memperkuat stabilitas keuangan daerah, dan memperluas sinergi lintas sektor dengan dampak transaksi nyata.',
                'focus' => 'Ekonomi syariah',
                'icon_key' => 'bank',
                'legacy_icon' => 'fa-university',
                'sort_order' => 2,
            ],
            [
                'title' => 'GENTALA ARASY',
                'name' => 'Gebyar Ekonomi Digital dan Literasi Jambi',
                'description' => 'Program ini meningkatkan pemahaman bisnis digital, literasi teknologi, dan keamanan online agar anggota lebih siap bertumbuh di ekosistem ekonomi digital.',
                'focus' => 'Literasi digital',
                'icon_key' => 'chart',
                'legacy_icon' => 'fa-money',
                'sort_order' => 3,
            ],
            [
                'title' => 'GENBI LEADERSHIP CAMP',
                'name' => 'GenBI Leadership Camp',
                'description' => 'Rangkaian aktivitas penguatan karakter kepemimpinan, kerja tim, dan tanggung jawab sosial untuk menyiapkan pemimpin muda yang berdampak bagi komunitas.',
                'focus' => 'Kepemimpinan',
                'icon_key' => 'users',
                'legacy_icon' => 'fa-users',
                'sort_order' => 4,
            ],
            [
                'title' => 'GGTC',
                'name' => 'GenBI Goes to Campus',
                'description' => 'Kegiatan tahunan edukatif untuk mengenalkan beasiswa Bank Indonesia dan pemahaman Cinta Bangga Paham Rupiah, QRIS, serta literasi kebanksentralan di kampus.',
                'focus' => 'Edukasi kampus',
                'icon_key' => 'academic',
                'legacy_icon' => 'fa-university',
                'sort_order' => 5,
            ],
            [
                'title' => 'GENBI SERTIFIKASI',
                'name' => 'GenBI Sertifikasi',
                'description' => 'Program peningkatan kompetensi anggota dengan pendampingan pengajar bersertifikat agar lebih siap menghadapi dunia kerja dan berkontribusi untuk pembangunan daerah.',
                'focus' => 'Pengembangan kompetensi',
                'icon_key' => 'sparkles',
                'legacy_icon' => 'fa-mobile',
                'sort_order' => 6,
            ],
        ];

        $existingLookup = $db->prepare('SELECT COUNT(*) FROM tbl_feature WHERE name = :name');
        foreach ($entries as $entry) {
            $existingLookup->execute([':name' => $entry['name']]);
            if ((int) $existingLookup->fetchColumn() > 0) {
                continue;
            }

            $insertColumns = [];
            $placeholders = [];
            $params = [];

            $fieldMap = [
                'title' => $entry['title'],
                'name' => $entry['name'],
                'description' => $entry['description'],
                'content' => $entry['description'],
                'icon' => $entry['legacy_icon'],
                'focus' => $entry['focus'],
                'icon_key' => $entry['icon_key'],
                'show_on_home' => 1,
                'sort_order' => $entry['sort_order'],
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            foreach ($fieldMap as $column => $value) {
                if (!in_array($column, $columns, true)) {
                    continue;
                }
                $insertColumns[] = $column;
                $placeholders[] = ':' . $column;
                $params[':' . $column] = $value;
            }

            if ($insertColumns === []) {
                continue;
            }

            $sql = 'INSERT INTO tbl_feature (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $insert = $db->prepare($sql);
            $insert->execute($params);
        }
    },
];
