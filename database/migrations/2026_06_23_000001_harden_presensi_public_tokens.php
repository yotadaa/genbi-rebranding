<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('SHOW COLUMNS FROM tbl_presensi_event');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        if (!in_array('public_token', $columns, true)) {
            return;
        }

        try {
            $db->exec('ALTER TABLE tbl_presensi_event DROP INDEX uq_tbl_presensi_event_token');
        } catch (\Throwable) {
            // Index may already be absent in updated databases.
        }

        try {
            $db->exec('ALTER TABLE tbl_presensi_event MODIFY public_token VARCHAR(96) NULL DEFAULT NULL');
        } catch (\Throwable) {
            // Some legacy hosts may not allow MODIFY; runtime stores a non-bearer marker for compatibility.
        }

        try {
            $db->exec("UPDATE tbl_presensi_event SET public_token = NULL WHERE public_token IS NOT NULL AND public_token <> '' AND public_token NOT LIKE 'sha256:%'");
        } catch (\Throwable) {
            // Keep migration best-effort; public lookup uses public_token_hash.
        }

        if (!in_array('public_token_expires_at', $columns, true)) {
            try {
                $db->exec('ALTER TABLE tbl_presensi_event ADD COLUMN public_token_expires_at DATETIME NULL AFTER public_token_hash');
            } catch (\Throwable) {
                // Optional hardening column; code checks for existence before use.
            }
        }

        try {
            $db->exec('ALTER TABLE tbl_presensi_event ADD INDEX idx_tbl_presensi_event_public_token_expires_at (public_token_expires_at)');
        } catch (\Throwable) {
            // Index already exists or column unavailable.
        }
    },
];
