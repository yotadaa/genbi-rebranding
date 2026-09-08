<?php

declare(strict_types=1);

use App\Controllers\Admin\PrestasiTokenController;
use App\Controllers\Public\PrestasiController;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\PrestasiToken;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_api(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function request_with_json(array $body, string $method = 'POST'): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['HTTP_ACCEPT'] = 'application/json';

    $request = new Request();
    $jsonBody = new ReflectionProperty(Request::class, 'jsonBody');
    $jsonBody->setAccessible(true);
    $jsonBody->setValue($request, $body);

    return $request;
}

/** @return array{status: int, payload: array<string, mixed>} */
function capture_json_response(callable $callback): array
{
    http_response_code(200);
    ob_start();
    $callback();
    $raw = ob_get_clean();

    $payload = json_decode((string) $raw, true);

    return [
        'status' => http_response_code(),
        'payload' => is_array($payload) ? $payload : [],
    ];
}

final class PrestasiTokenApiGenerateFake extends PrestasiToken
{
    public function generate(string $label, int $createdBy, ?string $expiresAt = null): ?array
    {
        expect_api($label === 'Token API Prestasi', 'Admin API should pass sanitized label into PrestasiToken::generate.');
        expect_api($createdBy > 0, 'Admin API should resolve a positive creator id.');

        return [
            'id' => 77,
            'token' => 'pst_api-token_123',
        ];
    }
}

final class PrestasiTokenApiValidateFake extends PrestasiToken
{
    public function __construct(private string $validToken)
    {
        parent::__construct(null);
    }

    public function validateToken(string $plainToken): ?array
    {
        $storedHash = hash('sha256', $this->validToken);
        $lookupHash = hash('sha256', trim($plainToken));

        if ($lookupHash !== $storedHash) {
            return null;
        }

        return [
            'id' => 88,
            'label' => 'Reusable sampai expired',
            'status' => 'active',
        ];
    }
}

$adminController = new PrestasiTokenController(new PrestasiTokenApiGenerateFake());
$adminGenerate = capture_json_response(static function () use ($adminController): void {
    $adminController->generate(
        request_with_json(['label' => 'Token API Prestasi']),
        new Response()
    );
});

expect_api($adminGenerate['status'] === 201, 'Admin token generate API should return HTTP 201.');
expect_api(($adminGenerate['payload']['data']['token'] ?? '') === 'pst_api-token_123', 'Admin token generate API should return the plain generated token once.');
expect_api(($adminGenerate['payload']['data']['submit_url'] ?? '') === '/prestasi/submit/pst_api-token_123', 'Admin token generate API must return a plain-token submit URL, not a token_hash URL.');
expect_api(!preg_match('/\/prestasi\/submit\/[a-f0-9]{64}$/i', (string) ($adminGenerate['payload']['data']['submit_url'] ?? '')), 'Admin token generate API must not expose a SHA-256-looking submit URL.');

$validToken = 'pst_public-token_456';
$storedHash = hash('sha256', $validToken);
$publicController = new PrestasiController(
    new StaticPageRenderer(dirname(__DIR__, 2) . '/fallbacks'),
    null,
    new PrestasiTokenApiValidateFake($validToken),
    null
);

$validCheck = capture_json_response(static function () use ($publicController, $validToken): void {
    $publicController->submissionForm(
        request_with_json([], 'GET'),
        new Response(),
        ['token' => $validToken]
    );
});

expect_api($validCheck['status'] === 200, 'Public Prestasi token-check API should accept the plain token.');
expect_api(($validCheck['payload']['data']['valid'] ?? false) === true, 'Public Prestasi token-check API should report plain token as valid.');

$hashCheck = capture_json_response(static function () use ($publicController, $storedHash): void {
    $publicController->submissionForm(
        request_with_json([], 'GET'),
        new Response(),
        ['token' => $storedHash]
    );
});

expect_api($hashCheck['status'] === 403, 'Public Prestasi token-check API should reject token_hash as a bearer token.');
expect_api(
    ($hashCheck['payload']['error'] ?? '') === 'Token tidak valid, kedaluwarsa, atau sudah dicabut',
    'Public Prestasi token-check API should return the reusable-token-safe invalid message.'
);

echo "PHP prestasi token API tests passed\n";
