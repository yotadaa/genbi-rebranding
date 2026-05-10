<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_event');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            try {
                $statement = $db->query('PRAGMA table_info(tbl_event)');
                $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
                $columns = array_column($rows, 'name');
            } catch (\Throwable) {
                $columns = [];
            }
        }

        if (!in_array('event_title', $columns, true)) {
            return;
        }

        $entries = [
            [
                'event_title' => 'GenBI PEKA',
                'event_content' => '<p>Gerakan kepedulian anggota GenBI Jambi untuk hadir lebih dekat dengan masyarakat dan membangun empati melalui aksi nyata.</p>',
                'event_content_short' => 'Gerakan kepedulian anggota GenBI Jambi untuk hadir lebih dekat dengan masyarakat dan membangun empati melalui aksi nyata.',
                'event_start_date' => '2025-01-23',
                'event_end_date' => '2025-01-23',
                'event_location' => 'Jambi',
                'event_map' => '',
                'photo' => 'https://genbijambi.com/public/uploads/slider-1.png',
                'banner' => 'https://genbijambi.com/public/uploads/slider-1.png',
                'meta_title' => 'GenBI PEKA | Agenda GenBI Jambi',
                'meta_keyword' => 'GenBI PEKA, agenda GenBI Jambi',
                'meta_description' => 'Agenda kepedulian sosial GenBI Jambi untuk mendekatkan aksi nyata dengan masyarakat.',
                'status' => 'published',
            ],
            [
                'event_title' => 'GenBI Ceria',
                'event_content' => '<p>Agenda kebersamaan yang merawat solidaritas anggota, membuka ruang interaksi, dan menjaga semangat organisasi tetap hidup.</p>',
                'event_content_short' => 'Agenda kebersamaan yang merawat solidaritas anggota, membuka ruang interaksi, dan menjaga semangat organisasi tetap hidup.',
                'event_start_date' => '2024-12-21',
                'event_end_date' => '2024-12-21',
                'event_location' => 'Jambi',
                'event_map' => '',
                'photo' => 'https://genbijambi.com/public/uploads/slider-4.png',
                'banner' => 'https://genbijambi.com/public/uploads/slider-4.png',
                'meta_title' => 'GenBI Ceria | Agenda GenBI Jambi',
                'meta_keyword' => 'GenBI Ceria, agenda GenBI Jambi',
                'meta_description' => 'Agenda kebersamaan GenBI Jambi untuk memperkuat solidaritas dan semangat organisasi.',
                'status' => 'published',
            ],
            [
                'event_title' => 'GenBI for UMKM',
                'event_content' => '<p>Pendampingan sederhana untuk membantu pelaku usaha memahami pencatatan, promosi digital, dan peluang pembayaran non-tunai.</p>',
                'event_content_short' => 'Pendampingan sederhana untuk membantu pelaku usaha memahami pencatatan, promosi digital, dan peluang pembayaran non-tunai.',
                'event_start_date' => '2024-12-20',
                'event_end_date' => '2024-12-20',
                'event_location' => 'Jambi',
                'event_map' => '',
                'photo' => 'https://genbijambi.com/public/uploads/slider-1.png',
                'banner' => 'https://genbijambi.com/public/uploads/slider-1.png',
                'meta_title' => 'GenBI for UMKM | Agenda GenBI Jambi',
                'meta_keyword' => 'GenBI for UMKM, agenda GenBI Jambi',
                'meta_description' => 'Agenda pendampingan UMKM GenBI Jambi untuk literasi usaha dan pembayaran digital.',
                'status' => 'published',
            ],
            [
                'event_title' => 'PTBI 2024',
                'event_content' => '<p>Kesempatan anggota GenBI Jambi memperluas wawasan tentang arah kebijakan Bank Indonesia dan dinamika ekonomi terkini.</p>',
                'event_content_short' => 'Kesempatan anggota GenBI Jambi memperluas wawasan tentang arah kebijakan Bank Indonesia dan dinamika ekonomi terkini.',
                'event_start_date' => '2024-11-29',
                'event_end_date' => '2024-11-29',
                'event_location' => 'Bank Indonesia Jambi',
                'event_map' => '',
                'photo' => 'https://genbijambi.com/public/uploads/banner_event.png',
                'banner' => 'https://genbijambi.com/public/uploads/banner_event.png',
                'meta_title' => 'PTBI 2024 | Agenda GenBI Jambi',
                'meta_keyword' => 'PTBI 2024, agenda GenBI Jambi',
                'meta_description' => 'Agenda kebanksentralan GenBI Jambi bersama Bank Indonesia untuk memperluas wawasan anggota.',
                'status' => 'published',
            ],
        ];

        $lookup = $db->prepare('SELECT COUNT(*) FROM tbl_event WHERE event_title = :title');

        foreach ($entries as $entry) {
            $lookup->execute([':title' => $entry['event_title']]);
            if ((int) $lookup->fetchColumn() > 0) {
                continue;
            }

            $fieldMap = [
                'event_title' => $entry['event_title'],
                'event_content' => $entry['event_content'],
                'event_content_short' => $entry['event_content_short'],
                'event_start_date' => $entry['event_start_date'],
                'event_end_date' => $entry['event_end_date'],
                'event_location' => $entry['event_location'],
                'event_map' => $entry['event_map'],
                'photo' => $entry['photo'],
                'banner' => $entry['banner'],
                'meta_title' => $entry['meta_title'],
                'meta_keyword' => $entry['meta_keyword'],
                'meta_description' => $entry['meta_description'],
                'status' => $entry['status'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $insertColumns = [];
            $placeholders = [];
            $params = [];
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

            $sql = 'INSERT INTO tbl_event (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $insert = $db->prepare($sql);
            $insert->execute($params);
        }
    },
];
