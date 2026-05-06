<?php

declare(strict_types=1);

namespace App\Models;

class PrestasiToken
{
    public function __construct(private ?\PDO $db = null) {}

    public static function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['token_id'] ?? $row['id'] ?? 0),
            'token_id' => (int) ($row['token_id'] ?? $row['id'] ?? 0),
            'token_hash' => $row['token_hash'] ?? '',
            'label' => $row['label'] ?? $row['keterangan'] ?? '',
            'status' => $row['status'] ?? 'active',
            'created_by' => (int) ($row['created_by'] ?? 0),
            'used_at' => $row['used_at'] ?? null,
            'expires_at' => $row['expires_at'] ?? null,
            'created_at' => $row['created_at'] ?? '',
        ];
    }

    public function generate(string $label, int $createdBy, ?string $expiresAt = null): ?string
    {
        if (!$this->db) {
            return null;
        }

        $plainToken = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plainToken);

        $stmt = $this->db->prepare(
            'INSERT INTO tbl_prestasi_submission_token (token_hash, label, status, created_by, expires_at, created_at) VALUES (:hash, :label, :status, :created_by, :expires_at, NOW())'
        );
        $stmt->execute([
            ':hash' => $hash,
            ':label' => $label,
            ':status' => 'active',
            ':created_by' => $createdBy,
            ':expires_at' => $expiresAt,
        ]);

        return $plainToken;
    }

    public function validateToken(string $plainToken): ?array
    {
        if (!$this->db) {
            return null;
        }

        $hash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi_submission_token WHERE token_hash = :hash AND status = :status AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1'
        );
        $stmt->bindValue(':hash', $hash, \PDO::PARAM_STR);
        $stmt->bindValue(':status', 'active', \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    public function markUsed(int $tokenId): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE tbl_prestasi_submission_token SET status = :status, used_at = NOW() WHERE token_id = :id AND status = :active'
        );
        return $stmt->execute([
            ':status' => 'used',
            ':id' => $tokenId,
            ':active' => 'active',
        ]);
    }

    public function revoke(int $tokenId): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE tbl_prestasi_submission_token SET status = :status WHERE token_id = :id AND status = :active'
        );
        return $stmt->execute([
            ':status' => 'revoked',
            ':id' => $tokenId,
            ':active' => 'active',
        ]);
    }

    public function all(int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_prestasi_submission_token ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}
