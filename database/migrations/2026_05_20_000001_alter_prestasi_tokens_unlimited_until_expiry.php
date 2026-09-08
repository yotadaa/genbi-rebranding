<?php

declare(strict_types=1);

return [
    'up' => static function (\PDO $db): void {
        try {
            $statement = $db->query('SHOW COLUMNS FROM tbl_prestasi_submission_token');
            $columns = $statement ? array_column($statement->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
        } catch (\Throwable) {
            return;
        }

        if (in_array('max_uses', $columns, true)) {
            $db->exec('ALTER TABLE tbl_prestasi_submission_token MODIFY max_uses INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (in_array('used_count', $columns, true)) {
            $db->exec('ALTER TABLE tbl_prestasi_submission_token MODIFY used_count INT UNSIGNED NOT NULL DEFAULT 0');
        }

        if (in_array('used_count', $columns, true) || in_array('used_at', $columns, true)) {
            $sets = [];
            if (in_array('used_count', $columns, true)) {
                $sets[] = 'used_count = 0';
            }
            if (in_array('used_at', $columns, true)) {
                $sets[] = 'used_at = NULL';
            }

            if ($sets !== []) {
                $db->exec('UPDATE tbl_prestasi_submission_token SET ' . implode(', ', $sets) . ' WHERE revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW())');
            }
        }
    },
];
