<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('DESCRIBE tbl_team_member');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        $add = static function (string $sql) use ($db): void {
            try { $db->exec($sql); } catch (\Throwable) {}
        };

        if (!in_array('komisariat', $columns, true)) {
            $add("ALTER TABLE tbl_team_member ADD COLUMN komisariat ENUM('Universitas Jambi','UIN Sultan Thaha','Alumni') NULL AFTER designation");
        }
        if (!in_array('divisi', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN divisi VARCHAR(120) NULL AFTER komisariat');
        }
        if (!in_array('jabatan', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN jabatan VARCHAR(120) NULL AFTER divisi');
        }
        if (!in_array('divisi_lain', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN divisi_lain VARCHAR(120) NULL AFTER jabatan');
        }
        if (!in_array('tahun', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN tahun INT NULL AFTER divisi_lain');
        }
        if (!in_array('status', $columns, true)) {
            $add("ALTER TABLE tbl_team_member ADD COLUMN status ENUM('active','inactive','alumni') NOT NULL DEFAULT 'active' AFTER tahun");
        }
        if (!in_array('created_at', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN created_at DATETIME NULL');
        }
        if (!in_array('updated_at', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN updated_at DATETIME NULL');
        }
        if (!in_array('deleted_at', $columns, true)) {
            $add('ALTER TABLE tbl_team_member ADD COLUMN deleted_at DATETIME NULL');
        }
    },
];
