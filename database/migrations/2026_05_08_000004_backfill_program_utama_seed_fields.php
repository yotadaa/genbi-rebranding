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

        $idColumn = in_array('feature_id', $columns, true) ? 'feature_id' : (in_array('id', $columns, true) ? 'id' : null);
        if ($idColumn === null || !in_array('name', $columns, true)) {
            return;
        }

        $rows = [
            [
                'lookup' => ['KKG (KEGIATAN KENAL GENBI)', 'Kegiatan Kenal GenBI'],
                'title' => 'KKG',
                'name' => 'Kegiatan Kenal GenBI',
                'description' => 'Kegiatan kenal GenBI memberi manfaat lewat jaringan yang luas, ruang pertukaran ide dan pengalaman, serta penguatan keterampilan sosial dan kepemimpinan anggota.',
                'focus' => 'Internal komunitas',
                'icon_key' => 'users',
                'legacy_icon' => 'fa-users',
                'sort_order' => 1,
            ],
            [
                'lookup' => ['SIGINJAI (SEMARAK EKONOMI DAN KEUANGAN SYARIAH NEGERI JAMBI)', 'Semarak Ekonomi Syariah Negeri Jambi'],
                'title' => 'SIGINJAI',
                'name' => 'Semarak Ekonomi Syariah Negeri Jambi',
                'description' => 'Program kolaboratif untuk mendorong pertumbuhan ekonomi syariah, memperkuat stabilitas keuangan daerah, dan memperluas sinergi lintas sektor dengan dampak transaksi nyata.',
                'focus' => 'Ekonomi syariah',
                'icon_key' => 'bank',
                'legacy_icon' => 'fa-university',
                'sort_order' => 2,
            ],
            [
                'lookup' => ['GENTALA ARASY (GEBYAR EKONOMI DIGITAL &  LITERASI JAMBI)', 'Gebyar Ekonomi Digital dan Literasi Jambi'],
                'title' => 'GENTALA ARASY',
                'name' => 'Gebyar Ekonomi Digital dan Literasi Jambi',
                'description' => 'Program ini meningkatkan pemahaman bisnis digital, literasi teknologi, dan keamanan online agar anggota lebih siap bertumbuh di ekosistem ekonomi digital.',
                'focus' => 'Literasi digital',
                'icon_key' => 'chart',
                'legacy_icon' => 'fa-money',
                'sort_order' => 3,
            ],
            [
                'lookup' => ['GENBI LEADERSHIP CAMP', 'GenBI Leadership Camp'],
                'title' => 'GENBI LEADERSHIP CAMP',
                'name' => 'GenBI Leadership Camp',
                'description' => 'Rangkaian aktivitas penguatan karakter kepemimpinan, kerja tim, dan tanggung jawab sosial untuk menyiapkan pemimpin muda yang berdampak bagi komunitas.',
                'focus' => 'Kepemimpinan',
                'icon_key' => 'users',
                'legacy_icon' => 'fa-users',
                'sort_order' => 4,
            ],
            [
                'lookup' => ['GGTC (GENBI GOES TO CAMPUS)', 'GenBI Goes to Campus'],
                'title' => 'GGTC',
                'name' => 'GenBI Goes to Campus',
                'description' => 'Kegiatan tahunan edukatif untuk mengenalkan beasiswa Bank Indonesia dan pemahaman Cinta Bangga Paham Rupiah, QRIS, serta literasi kebanksentralan di kampus.',
                'focus' => 'Edukasi kampus',
                'icon_key' => 'academic',
                'legacy_icon' => 'fa-university',
                'sort_order' => 5,
            ],
            [
                'lookup' => ['GENBI SERTIFIKASI', 'GenBI Sertifikasi'],
                'title' => 'GENBI SERTIFIKASI',
                'name' => 'GenBI Sertifikasi',
                'description' => 'Program peningkatan kompetensi anggota dengan pendampingan pengajar bersertifikat agar lebih siap menghadapi dunia kerja dan berkontribusi untuk pembangunan daerah.',
                'focus' => 'Pengembangan kompetensi',
                'icon_key' => 'sparkles',
                'legacy_icon' => 'fa-mobile',
                'sort_order' => 6,
            ],
        ];

        $select = $db->prepare('SELECT ' . $idColumn . ' FROM tbl_feature WHERE name = :name LIMIT 1');
        foreach ($rows as $row) {
            $targetId = null;
            foreach ($row['lookup'] as $name) {
                $select->execute([':name' => $name]);
                $found = (int) ($select->fetchColumn() ?: 0);
                if ($found > 0) {
                    $targetId = $found;
                    break;
                }
            }
            if ($targetId === null) {
                continue;
            }

            $pairs = [];
            $params = [':id' => $targetId];
            $fieldMap = [
                'title' => $row['title'],
                'name' => $row['name'],
                'description' => $row['description'],
                'content' => $row['description'],
                'focus' => $row['focus'],
                'icon_key' => $row['icon_key'],
                'icon' => $row['legacy_icon'],
                'show_on_home' => 1,
                'sort_order' => $row['sort_order'],
                'status' => 'published',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            foreach ($fieldMap as $column => $value) {
                if (!in_array($column, $columns, true)) {
                    continue;
                }
                $pairs[] = $column . ' = :' . $column;
                $params[':' . $column] = $value;
            }

            if ($pairs === []) {
                continue;
            }

            $sql = 'UPDATE tbl_feature SET ' . implode(', ', $pairs) . ' WHERE ' . $idColumn . ' = :id';
            $update = $db->prepare($sql);
            $update->execute($params);
        }
    },
];

