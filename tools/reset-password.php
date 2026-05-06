<?php

/**
 * CLI tool to reset admin user password.
 * Usage: php tools/reset-password.php <email> <new-password>
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

if ($argc < 3) {
    echo "Usage: php tools/reset-password.php <email> <new-password>\n";
    echo "Example: php tools/reset-password.php genbi@gmail.com MyNewPass123\n";
    exit(1);
}

$email = trim($argv[1]);
$password = $argv[2];

if (strlen($password) < 6) {
    echo "Error: Password must be at least 6 characters.\n";
    exit(1);
}

require __DIR__ . '/../app/Core/Env.php';
\App\Core\Env::load(dirname(__DIR__) . '/.env');
require __DIR__ . '/../app/Config/Database.php';
require __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::connection();
} catch (\Throwable $e) {
    echo "Error: Cannot connect to database - " . $e->getMessage() . "\n";
    exit(1);
}

$stmt = $db->prepare('SELECT id, email, role FROM tbl_user WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$user) {
    echo "Error: No user found with email '{$email}'.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('UPDATE tbl_user SET password = :hash WHERE id = :id');
$stmt->execute([':hash' => $hash, ':id' => $user['id']]);

echo "Password updated successfully.\n";
echo "  User: {$user['email']} (id={$user['id']}, role={$user['role']})\n";
echo "  Hash: {$hash}\n";
