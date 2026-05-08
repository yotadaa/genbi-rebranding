<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeImmutable;
use PDO;
use Throwable;

final class LoginThrottleService
{
    private const MAX_ATTEMPTS = 10;
    private const WINDOW_MINUTES = 10;

    public function __construct(private ?PDO $db = null)
    {
    }

    public function isBlocked(string $email, string $ip): bool
    {
        $key = $this->normalizeKey($email, $ip);
        if ($key === null) {
            return false;
        }

        $row = $this->findRow($key['email'], $key['ip']);
        if (!is_array($row)) {
            return false;
        }

        $lockedUntil = $row['locked_until'] ?? null;
        if (!is_string($lockedUntil) || $lockedUntil === '') {
            return false;
        }

        $expiresAt = strtotime($lockedUntil);
        return $expiresAt !== false && $expiresAt > time();
    }

    public function recordFailure(string $email, string $ip): void
    {
        $key = $this->normalizeKey($email, $ip);
        if ($key === null || !$this->db instanceof PDO) {
            return;
        }

        try {
            $row = $this->findRow($key['email'], $key['ip']);
            $now = new DateTimeImmutable('now');
            $windowStart = $now->sub(new DateInterval('PT' . self::WINDOW_MINUTES . 'M'));
            $firstAttempt = null;
            $attempts = 0;

            if (is_array($row)) {
                $firstAttempt = isset($row['first_attempt_at']) ? strtotime((string) $row['first_attempt_at']) : false;
                $attempts = (int) ($row['attempt_count'] ?? 0);
            }

            if ($firstAttempt === false || $firstAttempt < $windowStart->getTimestamp()) {
                $attempts = 0;
                $firstAttemptAt = $now->format('Y-m-d H:i:s');
            } else {
                $firstAttemptAt = (string) ($row['first_attempt_at'] ?? $now->format('Y-m-d H:i:s'));
            }

            $attempts++;
            $lockedUntil = $attempts >= self::MAX_ATTEMPTS
                ? $now->add(new DateInterval('PT' . self::WINDOW_MINUTES . 'M'))->format('Y-m-d H:i:s')
                : null;

            if (is_array($row)) {
                $statement = $this->db->prepare(
                    'UPDATE tbl_login_attempt SET attempt_count = :attempt_count, first_attempt_at = :first_attempt_at, last_attempt_at = :last_attempt_at, locked_until = :locked_until, updated_at = :updated_at WHERE email_normalized = :email AND ip_address = :ip'
                );
            } else {
                $statement = $this->db->prepare(
                    'INSERT INTO tbl_login_attempt (email_normalized, ip_address, attempt_count, first_attempt_at, last_attempt_at, locked_until, created_at, updated_at) VALUES (:email, :ip, :attempt_count, :first_attempt_at, :last_attempt_at, :locked_until, :created_at, :updated_at)'
                );
                $statement->bindValue(':created_at', $now->format('Y-m-d H:i:s'));
            }

            $statement->bindValue(':email', $key['email']);
            $statement->bindValue(':ip', $key['ip']);
            $statement->bindValue(':attempt_count', $attempts, PDO::PARAM_INT);
            $statement->bindValue(':first_attempt_at', $firstAttemptAt);
            $statement->bindValue(':last_attempt_at', $now->format('Y-m-d H:i:s'));
            $statement->bindValue(':locked_until', $lockedUntil);
            $statement->bindValue(':updated_at', $now->format('Y-m-d H:i:s'));
            $statement->execute();
        } catch (Throwable) {
            // Throttling should not break login flows when the table is unavailable.
        }
    }

    public function clear(string $email, string $ip): void
    {
        $key = $this->normalizeKey($email, $ip);
        if ($key === null || !$this->db instanceof PDO) {
            return;
        }

        try {
            $statement = $this->db->prepare(
                'DELETE FROM tbl_login_attempt WHERE email_normalized = :email AND ip_address = :ip'
            );
            $statement->execute([
                ':email' => $key['email'],
                ':ip' => $key['ip'],
            ]);
        } catch (Throwable) {
            // Keep successful logins working even when cleanup fails.
        }
    }

    /** @return array{email: string, ip: string}|null */
    private function normalizeKey(string $email, string $ip): ?array
    {
        $email = trim(mb_strtolower($email));
        $ip = trim($ip);

        if ($email === '' || $ip === '') {
            return null;
        }

        return ['email' => $email, 'ip' => $ip];
    }

    /** @return array<string, mixed>|null */
    private function findRow(string $email, string $ip): ?array
    {
        if (!$this->db instanceof PDO) {
            return null;
        }

        try {
            $statement = $this->db->prepare(
                'SELECT * FROM tbl_login_attempt WHERE email_normalized = :email AND ip_address = :ip LIMIT 1'
            );
            $statement->execute([
                ':email' => $email,
                ':ip' => $ip,
            ]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : null;
        } catch (Throwable) {
            return null;
        }
    }
}
