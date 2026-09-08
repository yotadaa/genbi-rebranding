<?php

declare(strict_types=1);

namespace App\Models;

class PrestasiToken
{
    public function __construct(private ?\PDO $db = null) {}

    public static function mapRow(array $row): array
    {
        $status = self::resolveStatus($row);

        return [
            'id' => (int) ($row['token_id'] ?? $row['id'] ?? 0),
            'token_id' => (int) ($row['token_id'] ?? $row['id'] ?? 0),
            'token_hash' => $row['token_hash'] ?? '',
            'submit_url' => '',
            'label' => $row['label'] ?? $row['keterangan'] ?? '',
            'status' => $status,
            'created_by' => (int) ($row['created_by'] ?? 0),
            'used_at' => $row['used_at'] ?? null,
            'expires_at' => $row['expires_at'] ?? null,
            'created_at' => $row['created_at'] ?? '',
        ];
    }

    /** @return array{id: int, token: string}|null */
    public function generate(string $label, int $createdBy, ?string $expiresAt = null): ?array
    {
        if (!$this->db) {
            return null;
        }

        $plainToken = self::newPlainToken();
        $hash = self::tokenHash($plainToken);

        $stmt = $this->db->prepare(
            'INSERT INTO tbl_prestasi_submission_token (token_hash, label, created_by, expires_at, created_at) VALUES (:hash, :label, :created_by, :expires_at, NOW())'
        );
        $stmt->execute([
            ':hash' => $hash,
            ':label' => $label,
            ':created_by' => $createdBy,
            ':expires_at' => $expiresAt,
        ]);

        return [
            'id' => (int) $this->db->lastInsertId(),
            'token' => $plainToken,
        ];
    }

    public function validateToken(string $plainToken): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $hash = self::tokenHash($plainToken);
            $stmt = $this->db->prepare(
                'SELECT * FROM tbl_prestasi_submission_token WHERE token_hash = :hash AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1'
            );
            $stmt->bindValue(':hash', $hash, \PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? self::mapRow($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Validate token with row-level lock (FOR UPDATE) to prevent TOCTOU race conditions.
     * Must be called within a transaction.
     */
    public function validateTokenForUpdate(string $plainToken): ?array
    {
        if (!$this->db) {
            return null;
        }

        $hash = self::tokenHash($plainToken);
        $stmt = $this->db->prepare(
                'SELECT * FROM tbl_prestasi_submission_token WHERE token_hash = :hash AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1 FOR UPDATE'
        );
        $stmt->bindValue(':hash', $hash, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? self::mapRow($row) : null;
    }

    public function getDb(): ?\PDO
    {
        return $this->db;
    }

    public function markUsed(int $tokenId): bool
    {
        // Tokens are reusable until expires_at/revoked_at. Keep this method as a
        // compatibility no-op for older call sites that still invoke markUsed().
        return $tokenId > 0;
    }

    public function revoke(int $tokenId): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE tbl_prestasi_submission_token SET revoked_at = NOW() WHERE token_id = :id AND revoked_at IS NULL'
        );
        return $stmt->execute([
            ':id' => $tokenId,
        ]);
    }

    public function all(int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM tbl_prestasi_submission_token ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
            );
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return array_map([self::class, 'mapRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable) {
            return [];
        }
    }

    private static function resolveStatus(array $row): string
    {
        if (!empty($row['status']) && is_string($row['status'])) {
            return $row['status'];
        }

        if (!empty($row['revoked_at'])) {
            return 'revoked';
        }

        if (!empty($row['expires_at'])) {
            $expiresAt = strtotime((string) $row['expires_at']);
            if ($expiresAt !== false && $expiresAt <= time()) {
                return 'expired';
            }
        }

        return 'active';
    }

    private static function tokenHash(string $token): string
    {
        return hash('sha256', trim($token));
    }

    private static function newPlainToken(): string
    {
        return 'pst_' . rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
