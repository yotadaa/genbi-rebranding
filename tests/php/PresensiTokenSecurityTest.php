<?php

declare(strict_types=1);

use App\Models\PresensiEvent;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_presensi_token(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$storedPlainToken = 'legacy-plain-token';
$mapped = PresensiEvent::mapRow([
    'presensi_event_id' => 42,
    'slug' => 'event-aman',
    'public_token' => $storedPlainToken,
    'public_token_hash' => hash('sha256', $storedPlainToken),
    'event_name' => 'Event Aman',
    'location' => 'KPw BI Jambi',
    'roles_json' => '["Peserta"]',
    'status' => 'open',
]);

expect_presensi_token($mapped['public_token'] === '', 'Presensi admin/public mapping must not expose stored plaintext public_token.');
expect_presensi_token($mapped['public_url'] === '', 'Presensi admin/public mapping must not reconstruct public_url from stored public_token.');
expect_presensi_token($mapped['public_token_hash'] === hash('sha256', $storedPlainToken), 'Presensi mapping may expose only the non-bearer hash for admin diagnostics.');

expect_presensi_token(
    method_exists(PresensiEvent::class, 'storageTokenMarker'),
    'PresensiEvent should store a non-bearer marker in legacy public_token column for compatibility.'
);

$markerMethod = new ReflectionMethod(PresensiEvent::class, 'storageTokenMarker');
$markerMethod->setAccessible(true);

$hash = hash('sha256', 'prs_plain-public-token');
$marker = $markerMethod->invoke(null, $hash);

expect_presensi_token(is_string($marker), 'Presensi public_token storage marker must be a string.');
expect_presensi_token(str_starts_with($marker, 'sha256:'), 'Presensi public_token storage marker should identify non-plaintext hash material.');
expect_presensi_token($marker !== 'prs_plain-public-token', 'Presensi public_token storage marker must not equal the plain bearer token.');
expect_presensi_token(hash('sha256', $marker) !== $hash, 'Presensi public_token storage marker must not be accepted as the public bearer token.');

expect_presensi_token(
    method_exists(PresensiEvent::class, 'newPublicToken'),
    'PresensiEvent should generate public tokens through a dedicated helper for regression coverage.'
);

$tokenMethod = new ReflectionMethod(PresensiEvent::class, 'newPublicToken');
$tokenMethod->setAccessible(true);

for ($i = 0; $i < 10; $i++) {
    $token = $tokenMethod->invoke(null);
    expect_presensi_token(is_string($token), 'Generated Presensi token must be a string.');
    expect_presensi_token(str_starts_with($token, 'prs_'), 'Generated Presensi token must use a non-hex public prefix.');
    expect_presensi_token(strlen($token) >= 40, 'Generated Presensi token must retain enough entropy.');
    expect_presensi_token(!preg_match('/^[a-f0-9]{64}$/i', $token), 'Generated Presensi token must not look like a stored SHA-256 hash.');
}

echo "PHP presensi token security tests passed\n";
