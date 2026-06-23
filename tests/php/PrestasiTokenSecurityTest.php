<?php

declare(strict_types=1);

use App\Models\PrestasiToken;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$hashMethod = new ReflectionMethod(PrestasiToken::class, 'tokenHash');
$hashMethod->setAccessible(true);

$plainToken = 'pst_' . str_repeat('A', 43);
$expectedPlainHash = hash('sha256', $plainToken);

expect(
    $hashMethod->invoke(null, $plainToken) === $expectedPlainHash,
    'Plain Prestasi submission tokens must be looked up by SHA-256 hash.'
);

$storedHash = $expectedPlainHash;
expect(
    $hashMethod->invoke(null, $storedHash) === hash('sha256', $storedHash),
    'A stored token_hash value must not be accepted directly as a public bearer token.'
);
expect(
    $hashMethod->invoke(null, $storedHash) !== $storedHash,
    'Submitting a database token_hash must not produce the same lookup hash.'
);

$hexLookingPlainToken = str_repeat('a', 64);
expect(
    $hashMethod->invoke(null, $hexLookingPlainToken) === hash('sha256', $hexLookingPlainToken),
    'Even 64-character hex-looking route tokens must be treated as plain input and hashed again.'
);

expect(
    method_exists(PrestasiToken::class, 'newPlainToken'),
    'PrestasiToken should generate public tokens through a dedicated helper for regression coverage.'
);

$newPlainToken = new ReflectionMethod(PrestasiToken::class, 'newPlainToken');
$newPlainToken->setAccessible(true);

for ($i = 0; $i < 10; $i++) {
    $generated = $newPlainToken->invoke(null);

    expect(is_string($generated), 'Generated Prestasi token must be a string.');
    expect(str_starts_with($generated, 'pst_'), 'Generated Prestasi token must use a non-hex public prefix.');
    expect(strlen($generated) >= 40, 'Generated Prestasi token must keep enough entropy for bearer-token use.');
    expect(!preg_match('/^[a-f0-9]{64}$/i', $generated), 'Generated Prestasi token must not look like a stored SHA-256 hash.');
    expect(
        $hashMethod->invoke(null, $generated) === hash('sha256', $generated),
        'Generated Prestasi token must be stored and looked up only by SHA-256 hash.'
    );
}

$mapped = PrestasiToken::mapRow([
    'token_id' => 12,
    'token_hash' => $storedHash,
    'label' => 'Reusable sampai expired',
    'status' => 'active',
]);

expect($mapped['submit_url'] === '', 'Token list mapping must not reconstruct public submit_url from token_hash.');

$model = new PrestasiToken(null);
expect($model->markUsed(12) === true, 'Reusable Prestasi tokens should remain valid after submit until expired/revoked.');
expect($model->markUsed(12) === true, 'markUsed remains an idempotent no-op for reusable Prestasi tokens.');

echo "PHP prestasi token security tests passed\n";
