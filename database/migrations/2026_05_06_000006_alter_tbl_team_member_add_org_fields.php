<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        $db->exec("ALTER TABLE tbl_team_member
            ADD COLUMN komisariat ENUM('Universitas Jambi','UIN Sultan Thaha','Alumni') NULL AFTER designation,
            ADD COLUMN divisi VARCHAR(120) NULL AFTER komisariat,
            ADD COLUMN jabatan VARCHAR(120) NULL AFTER divisi,
            ADD COLUMN divisi_lain VARCHAR(120) NULL AFTER jabatan,
            ADD COLUMN tahun INT NULL AFTER divisi_lain,
            ADD COLUMN status ENUM('active','inactive','alumni') NOT NULL DEFAULT 'active' AFTER tahun,
            ADD COLUMN created_at DATETIME NULL,
            ADD COLUMN updated_at DATETIME NULL,
            ADD COLUMN deleted_at DATETIME NULL");
    },
];
