<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\AuthService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_auth_legacy_hash(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$db = Database::connection();
$email = 'md5-' . bin2hex(random_bytes(4)) . '@test.local';
$password = 'legacy-secret';
$legacyHash = md5($password);

$delete = $db->prepare('DELETE FROM tbl_user WHERE email = :email');
$delete->execute([':email' => $email]);

try {
    $insert = $db->prepare("INSERT INTO tbl_user (email, password, photo, token, role, status, failed_login_count) VALUES (:email, :password, '', '', 'admin', 'Active', 0)");
    $insert->execute([
        ':email' => $email,
        ':password' => $legacyHash,
    ]);

    $result = (new AuthService($db))->attempt($email, $password, '127.0.0.1');

    expect_auth_legacy_hash(($result['success'] ?? false) === false, 'AuthService must reject legacy MD5 password hashes.');
    expect_auth_legacy_hash(($result['error'] ?? '') === 'Email atau password salah', 'AuthService should keep a generic password failure response.');

    $stmt = $db->prepare('SELECT password, failed_login_count FROM tbl_user WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    expect_auth_legacy_hash(is_array($row), 'Temporary legacy-hash test user should still exist before cleanup.');
    expect_auth_legacy_hash(($row['password'] ?? '') === $legacyHash, 'Rejected legacy MD5 hash should not be silently rehashed into a valid password.');
    expect_auth_legacy_hash((int) ($row['failed_login_count'] ?? 0) === 1, 'Rejected legacy MD5 login should be counted as a failed attempt.');
} finally {
    $delete->execute([':email' => $email]);
}

echo "PHP auth legacy hash security tests passed\n";
