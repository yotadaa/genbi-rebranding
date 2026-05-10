<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

final class Setting
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        try {
            $statement = $this->db->query('SELECT setting_key, setting_value, setting_type FROM tbl_setting ORDER BY setting_key ASC');
            $settings = [];

            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $settings[(string) $row['setting_key']] = $this->castValue($row['setting_value'] ?? null, (string) ($row['setting_type'] ?? 'string'));
            }

            return $settings;
        } catch (Throwable) {
            return [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $statement = $this->db->prepare('SELECT setting_value, setting_type FROM tbl_setting WHERE setting_key = :key LIMIT 1');
            $statement->execute([':key' => $key]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return $default;
            }

            return $this->castValue($row['setting_value'] ?? null, (string) ($row['setting_type'] ?? 'string'));
        } catch (Throwable) {
            return $default;
        }
    }

    /** @param array<string, mixed> $values */
    public function putMany(array $values, ?int $userId = null): void
    {
        try {
            $this->db->beginTransaction();
            $select = $this->db->prepare('SELECT setting_key, setting_type, description FROM tbl_setting WHERE setting_key = :key LIMIT 1');
            $insert = $this->db->prepare('INSERT INTO tbl_setting (setting_key, setting_value, setting_type, description, updated_by, updated_at) VALUES (:key, :value, :type, :description, :updated_by, CURRENT_TIMESTAMP)');
            $update = $this->db->prepare('UPDATE tbl_setting SET setting_value = :value, setting_type = :type, description = :description, updated_by = :updated_by, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :key');

            foreach ($values as $key => $value) {
                $select->execute([':key' => $key]);
                $row = $select->fetch(PDO::FETCH_ASSOC);
                $type = is_array($row) ? (string) ($row['setting_type'] ?? $this->detectType($value)) : $this->detectType($value);
                $description = is_array($row) ? (string) ($row['description'] ?? '') : '';
                $params = [
                    ':key' => $key,
                    ':value' => $this->serializeValue($value, $type),
                    ':type' => $type,
                    ':description' => $description,
                    ':updated_by' => $userId,
                ];

                if (is_array($row)) {
                    $update->execute($params);
                    continue;
                }

                $insert->execute($params);
            }

            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    private function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) => 'int',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    private function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'bool' => $value ? '1' : '0',
            'int' => (string) ((int) $value),
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool' => in_array((string) $value, ['1', 'true', 'on', 'yes'], true),
            'int' => (int) $value,
            'json' => is_string($value) && $value !== '' ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : [],
            default => $value,
        };
    }
}
