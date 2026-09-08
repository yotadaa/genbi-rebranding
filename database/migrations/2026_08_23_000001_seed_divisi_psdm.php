<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $namaDivisi = 'Divisi Pengembangan Sumber Daya Manusia';

        $stmt = $db->prepare('SELECT COUNT(*) FROM divisis WHERE nama = :nama');
        $stmt->execute(['nama' => $namaDivisi]);
        $exists = (int) $stmt->fetchColumn() > 0;

        if (!$exists) {
            $insert = $db->prepare('INSERT INTO divisis (id, nama, komsat_id, deskripsi, created_at, updated_at) VALUES (:id, :nama, :komsat_id, :deskripsi, NOW(), NOW())');

            // Generate a random ID or rely on auto-increment? The ID column is bigint unsigned, but not auto_increment in DESCRIBE? 
            // Wait, DESCRIBE didn't say auto_increment in 'Extra'. So we might need to find max id + 1.
            $maxStmt = $db->query('SELECT MAX(id) FROM divisis');
            $maxId = (int) $maxStmt->fetchColumn();
            $newId = $maxId > 0 ? $maxId + 1 : 1;

            $insert->execute([
                'id' => $newId,
                'nama' => $namaDivisi,
                'komsat_id' => null,
                'deskripsi' => 'Divisi Pengembangan Sumber Daya Manusia (PSDM)'
            ]);
        }
    },
    'down' => static function (\PDO $db): void {
        $db->prepare('DELETE FROM divisis WHERE nama = :nama')->execute(['nama' => 'Divisi Pengembangan Sumber Daya Manusia (PSDM)']);
    }
];
