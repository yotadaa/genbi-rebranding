<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

final class AuthService
{
    private const SESSION_USER_KEY = '_auth_user';
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 10;
    private const ALLOWED_ROLES = ['superadmin', 'admin', 'editor', 'moderator'];

    public function __construct(private ?\PDO $db = null) {}

    public function attempt(string $email, string $password, string $ip = ''): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database tidak tersedia. Periksa koneksi database dan ekstensi pdo_mysql di cPanel.'];
        }

        $email = trim(strtolower($email));
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Email dan password wajib diisi'];
        }

        $user = $this->findUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'Email atau password salah'];
        }

        // Check account lock (only if column exists from migration 008)
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'error' => "Akun terkunci. Coba lagi dalam {$remaining} menit."];
        }

        // Check status (case-insensitive: 'Active', 'active', '1' all valid)
        $status = strtolower($user['status'] ?? '');
        if ($status !== 'active' && $status !== '1' && $status !== '') {
            return ['success' => false, 'error' => 'Akun tidak aktif'];
        }

        // Verify password
        if (!$this->verifyPassword($password, $user['password'] ?? '')) {
            $this->recordFailedAttempt($user);
            return ['success' => false, 'error' => 'Email atau password salah'];
        }

        // Check role (case-insensitive comparison)
        $role = strtolower($user['role'] ?? '');
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            return ['success' => false, 'error' => 'Akun tidak memiliki akses admin'];
        }

        // Success - reset failed attempts, update login info
        $this->recordSuccessfulLogin($user, $ip);

        // Rehash if needed (legacy password migration)
        $storedPassword = $user['password'] ?? '';
        if ($storedPassword !== '' && password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $this->rehashPassword($this->getUserId($user), $password);
        }

        // Set session
        Session::regenerate();
        Session::set(self::SESSION_USER_KEY, [
            'id' => $this->getUserId($user),
            'email' => $user['email'] ?? '',
            'name' => $user['name'] ?? $user['fullname'] ?? $user['username'] ?? '',
            'role' => $role,
            'login_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true];
    }

    public function check(): bool
    {
        return Session::has(self::SESSION_USER_KEY);
    }

    public function user(): ?array
    {
        return Session::get(self::SESSION_USER_KEY);
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function requireRole(string ...$roles): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        return in_array($user['role'] ?? '', $roles, true);
    }

    // --- Static helpers for testing ---

    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::ALLOWED_ROLES, true);
    }

    public static function allowedRoles(): array
    {
        return self::ALLOWED_ROLES;
    }

    // --- Private methods ---

    private function getUserId(array $user): int
    {
        return (int) ($user['user_id'] ?? $user['id'] ?? 0);
    }

    /**
     * Check if a column exists in tbl_user (for graceful handling before migration 008).
     */
    private function hasColumn(string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $stmt = $this->db->query('DESCRIBE tbl_user');
                $columns = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Field');
            } catch (\Throwable) {
                $columns = [];
            }
        }
        return in_array($column, $columns, true);
    }

    private function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tbl_user WHERE email = :email LIMIT 1'
        );
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function verifyPassword(string $input, string $stored): bool
    {
        return password_verify($input, $stored);
    }

    private function rehashPassword(int $userId, string $plainPassword): void
    {
        if ($userId <= 0) {
            return;
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE tbl_user SET password = :hash WHERE id = :id');
        $stmt->execute([':hash' => $hash, ':id' => $userId]);
    }

    private function recordFailedAttempt(array $user): void
    {
        $userId = $this->getUserId($user);
        if ($userId <= 0 || !$this->hasColumn('failed_login_count')) {
            return;
        }

        $failedCount = ((int) ($user['failed_login_count'] ?? 0)) + 1;

        if ($failedCount >= self::MAX_FAILED_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + (self::LOCKOUT_MINUTES * 60));
            $stmt = $this->db->prepare(
                'UPDATE tbl_user SET failed_login_count = :count, locked_until = :lock WHERE id = :id'
            );
            $stmt->execute([':count' => $failedCount, ':lock' => $lockUntil, ':id' => $userId]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE tbl_user SET failed_login_count = :count WHERE id = :id'
            );
            $stmt->execute([':count' => $failedCount, ':id' => $userId]);
        }
    }

    private function recordSuccessfulLogin(array $user, string $ip): void
    {
        $userId = $this->getUserId($user);
        if ($userId <= 0) {
            return;
        }

        // Only update columns that exist (graceful before migration 008)
        if ($this->hasColumn('failed_login_count')) {
            $stmt = $this->db->prepare(
                'UPDATE tbl_user SET failed_login_count = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = :ip WHERE id = :id'
            );
            $stmt->execute([':ip' => $ip, ':id' => $userId]);
        }
    }
}
