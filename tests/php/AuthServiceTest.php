<?php

declare(strict_types=1);

use App\Services\AuthService;
use App\Services\CsrfService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

// --- AuthService::isValidRole ---
assert(AuthService::isValidRole('superadmin') === true);
assert(AuthService::isValidRole('admin') === true);
assert(AuthService::isValidRole('editor') === true);
assert(AuthService::isValidRole('moderator') === true);
assert(AuthService::isValidRole('user') === false);
assert(AuthService::isValidRole('') === false);
assert(AuthService::isValidRole('ADMIN') === false); // case-sensitive

// --- AuthService::allowedRoles ---
$roles = AuthService::allowedRoles();
assert(count($roles) === 4);
assert(in_array('superadmin', $roles, true));
assert(in_array('admin', $roles, true));
assert(in_array('editor', $roles, true));
assert(in_array('moderator', $roles, true));

// --- AuthService without DB ---
$auth = new AuthService(null);
assert($auth->check() === false);
assert($auth->user() === null);
$result = $auth->attempt('test@example.com', 'password');
assert($result['success'] === false);
assert(str_contains($result['error'], 'Database'));

// --- CsrfService (requires session) ---
// Start a test session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$token = CsrfService::token();
assert(!empty($token));
assert(strlen($token) === 64); // bin2hex(32 bytes) = 64 chars

// Same token on repeated calls
assert(CsrfService::token() === $token);

// Validation
assert(CsrfService::validate($token) === true);
assert(CsrfService::validate('wrong-token') === false);
assert(CsrfService::validate(null) === false);
assert(CsrfService::validate('') === false);

// Regenerate
CsrfService::regenerate();
$newToken = CsrfService::token();
assert($newToken !== $token);
assert(CsrfService::validate($newToken) === true);
assert(CsrfService::validate($token) === false); // old token invalid

// Hidden input
$input = CsrfService::hiddenInput();
assert(str_contains($input, '<input type="hidden"'));
assert(str_contains($input, 'name="_csrf_token"'));
assert(str_contains($input, $newToken));

echo "PHP auth service tests passed\n";
