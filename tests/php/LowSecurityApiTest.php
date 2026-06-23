<?php

declare(strict_types=1);

use App\Controllers\Public\PrestasiController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\Prestasi;
use App\Models\PrestasiToken;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_low_api(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function low_api_request(array $json, string $method = 'POST'): Request
{
    $_POST = [];
    $_FILES = [];
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'LowSecurityApiTest/1.0';

    $request = new Request();
    $property = new ReflectionProperty(Request::class, 'jsonBody');
    $property->setAccessible(true);
    $property->setValue($request, $json);

    return $request;
}

/** @return array{status: int, payload: array<string, mixed>, raw: string} */
function low_api_capture(callable $callback): array
{
    http_response_code(200);
    ob_start();
    $callback();
    $raw = (string) ob_get_clean();
    $payload = json_decode($raw, true);

    return [
        'status' => http_response_code(),
        'payload' => is_array($payload) ? $payload : [],
        'raw' => $raw,
    ];
}

final class LowSecurityValidPrestasiTokenModel extends PrestasiToken
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function validateTokenForUpdate(string $plainToken): ?array
    {
        expect_low_api($plainToken === 'pst_low_api_token', 'Prestasi submit API should validate the plain route token.');

        return [
            'id' => 123,
            'label' => 'Low security token',
            'status' => 'active',
        ];
    }
}

final class LowSecurityThrowingPrestasiModel extends Prestasi
{
    /** @var array<string, mixed> */
    public array $received = [];

    public function findBySlug(string $slug): ?array
    {
        return null;
    }

    public function create(array $data): int
    {
        $this->received = $data;

        throw new RuntimeException('SQLSTATE[42S22]: Column not found: secret_path=C:\\server\\schema.sql');
    }
}

$input = [
    'title' => ' <b>Juara<script>alert(1)</script></b> ',
    'category' => ' <i>Akademik</i> ',
    'year' => '2026',
    'campus' => 'Universitas Jambi',
    'name' => '<img src=x onerror=alert(1)>Amalia',
    'institution' => '<strong>Bank Indonesia</strong>',
    'description' => 'Ringkas<script>alert(2)</script>',
    'content' => '<p>Konten aman</p><script>alert(3)</script><img src=x onerror=alert(4)>',
    'image_url' => 'javascript:alert(5)',
];

$prestasi = new LowSecurityThrowingPrestasiModel(null);
$controller = new PrestasiController(
    new StaticPageRenderer(dirname(__DIR__, 2) . '/fallbacks'),
    $prestasi,
    new LowSecurityValidPrestasiTokenModel(Database::connection()),
    null
);

$result = low_api_capture(static function () use ($controller, $input): void {
    $controller->submitWithToken(
        low_api_request($input),
        new Response(),
        ['token' => 'pst_low_api_token']
    );
});

expect_low_api($result['status'] === 500, 'Prestasi submit API should return HTTP 500 for unexpected persistence failure.');
expect_low_api(($result['payload']['error'] ?? '') === 'Gagal menyimpan data', 'Prestasi submit API should return a stable generic error.');
expect_low_api(($result['payload']['code'] ?? '') === 'submission_transaction_failed', 'Prestasi submit API should return a safe failure code.');
expect_low_api(isset($result['payload']['request_id']) && preg_match('/^prestasi_submit_[a-f0-9]{16}$/', (string) $result['payload']['request_id']) === 1, 'Prestasi submit API should return a safe request_id for log correlation.');
expect_low_api(!array_key_exists('exception', $result['payload']), 'Prestasi submit API must not expose exception class names.');
expect_low_api(!array_key_exists('message', $result['payload']), 'Prestasi submit API must not expose raw exception messages.');
expect_low_api(!str_contains($result['raw'], 'SQLSTATE'), 'Prestasi submit API response must not leak SQLSTATE details.');
expect_low_api(!str_contains($result['raw'], 'secret_path'), 'Prestasi submit API response must not leak server paths.');

expect_low_api($prestasi->received !== [], 'Prestasi submit API should pass sanitized payload into the model before persistence.');
foreach (['title', 'name', 'category', 'institution', 'description', 'content'] as $field) {
    $value = (string) ($prestasi->received[$field] ?? '');
    expect_low_api(!str_contains($value, '<'), "Prestasi submit field {$field} should not keep HTML tags.");
    expect_low_api(!str_contains(strtolower($value), 'onerror'), "Prestasi submit field {$field} should not keep event-handler text.");
}
expect_low_api(($prestasi->received['image'] ?? null) === '', 'Prestasi submit API should sanitize javascript: image_url to an empty image value.');

echo "PHP low security Prestasi API tests passed\n";
