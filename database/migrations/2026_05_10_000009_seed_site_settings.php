<?php

declare(strict_types=1);

use App\Services\SiteSettings;

return [
    'up' => static function (PDO $db): void {
        $select = $db->prepare('SELECT setting_key, description FROM tbl_setting WHERE setting_key = :key LIMIT 1');
        $insert = $db->prepare('INSERT INTO tbl_setting (setting_key, setting_value, setting_type, description, updated_at) VALUES (:key, :value, :type, :description, CURRENT_TIMESTAMP)');
        $update = $db->prepare('UPDATE tbl_setting SET setting_value = :value, setting_type = :type, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :key');

        foreach (SiteSettings::defaults() as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'bool',
                is_int($value) => 'int',
                default => 'string',
            };

            $params = [
                ':key' => $key,
                ':value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                ':type' => $type,
                ':description' => 'Seeded site setting for admin settings integration.',
            ];

            $select->execute([':key' => $key]);
            $existing = $select->fetch(PDO::FETCH_ASSOC);
            if (is_array($existing)) {
                $update->execute([
                    ':key' => $params[':key'],
                    ':value' => $params[':value'],
                    ':type' => $params[':type'],
                ]);
                continue;
            }

            $insert->execute($params);
        }
    },

    'down' => static function (PDO $db): void {
        $keys = array_keys(SiteSettings::defaults());
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $statement = $db->prepare('DELETE FROM tbl_setting WHERE setting_key IN (' . $placeholders . ')');
        $statement->execute($keys);
    },
];
