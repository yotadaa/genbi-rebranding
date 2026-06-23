<?php

declare(strict_types=1);

use App\Controllers\Public\PrestasiController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Models\Prestasi;
use App\Models\PrestasiToken;
use App\Services\AuthService;
use App\Services\StructuredData;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function evidence_low_request(array $json = [], string $method = 'POST'): Request
{
    $_POST = [];
    $_FILES = [];
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'LowSecurityApiEvidence/1.0';

    $request = new Request();
    $property = new ReflectionProperty(Request::class, 'jsonBody');
    $property->setAccessible(true);
    $property->setValue($request, $json);

    return $request;
}

/** @return array{status: int, json: array<string, mixed>, raw: string} */
function evidence_low_capture(callable $callback): array
{
    http_response_code(200);
    ob_start();
    $callback();
    $raw = (string) ob_get_clean();
    $payload = json_decode($raw, true);

    return [
        'status' => http_response_code(),
        'json' => is_array($payload) ? $payload : [],
        'raw' => $raw,
    ];
}

final class LowEvidenceValidPrestasiTokenModel extends PrestasiToken
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function validateTokenForUpdate(string $plainToken): ?array
    {
        return $plainToken === 'pst_low_api_token'
            ? ['id' => 456, 'label' => 'Low evidence token', 'status' => 'active']
            : null;
    }
}

final class LowEvidenceThrowingPrestasiModel extends Prestasi
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

$db = Database::connection();
$prestasiInput = [
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

$prestasi = new LowEvidenceThrowingPrestasiModel(null);
$prestasiController = new PrestasiController(
    new StaticPageRenderer(dirname(__DIR__, 2) . '/fallbacks'),
    $prestasi,
    new LowEvidenceValidPrestasiTokenModel($db),
    null
);

$prestasiResult = evidence_low_capture(static function () use ($prestasiController, $prestasiInput): void {
    $prestasiController->submitWithToken(
        evidence_low_request($prestasiInput),
        new Response(),
        ['token' => 'pst_low_api_token']
    );
});

$scriptInput = [
    'title' => '</script><img src=x onerror=alert(1)>',
    'slug' => 'jsonld-xss-test',
    'excerpt' => 'Tom & Jerry "quote" \'apos\' <tag>',
    'published_at' => '2026-06-23',
    'author' => 'Eve </script>',
];
$script = StructuredData::newsArticle($scriptInput);
$json = substr($script, strlen('<script type="application/ld+json">'), -strlen('</script>'));

$email = 'md5-evidence-' . bin2hex(random_bytes(3)) . '@test.local';
$legacyPassword = 'legacy-secret';
$legacyHash = md5($legacyPassword);
$delete = $db->prepare('DELETE FROM tbl_user WHERE email = :email');
$delete->execute([':email' => $email]);

try {
    $insert = $db->prepare("INSERT INTO tbl_user (email, password, photo, token, role, status, failed_login_count) VALUES (:email, :password, '', '', 'admin', 'Active', 0)");
    $insert->execute([
        ':email' => $email,
        ':password' => $legacyHash,
    ]);

    $authResult = (new AuthService($db))->attempt($email, $legacyPassword, '127.0.0.1');
    $stmt = $db->prepare('SELECT password, failed_login_count FROM tbl_user WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $authRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} finally {
    $delete->execute([':email' => $email]);
}

echo json_encode([
    'prestasi_submit_internal_error_is_generic' => [
        'method' => 'POST',
        'path' => '/prestasi/submit/pst_low_api_token',
        'input_json' => $prestasiInput,
        'status' => $prestasiResult['status'],
        'output_json' => $prestasiResult['json'],
        'response_safety_assertions' => [
            'has_request_id' => isset($prestasiResult['json']['request_id']),
            'leaks_exception_class' => array_key_exists('exception', $prestasiResult['json']),
            'leaks_raw_message' => array_key_exists('message', $prestasiResult['json']),
            'raw_response_contains_sqlstate' => str_contains($prestasiResult['raw'], 'SQLSTATE'),
            'raw_response_contains_server_path' => str_contains($prestasiResult['raw'], 'secret_path'),
        ],
        'sanitized_model_payload' => $prestasi->received,
    ],
    'structured_data_script_context_uses_hex_encoding' => [
        'method' => 'SSR_RENDER',
        'path' => '/news/jsonld-xss-test',
        'input_json' => $scriptInput,
        'status' => 200,
        'output_json' => [
            'json_contains_raw_script_close' => str_contains($json, '</script>'),
            'json_contains_raw_img_tag' => str_contains($json, '<img'),
            'json_contains_hex_angle_bracket' => str_contains($json, '\u003C'),
            'json_contains_hex_ampersand' => str_contains($json, '\u0026'),
            'json_contains_hex_quote' => str_contains($json, '\u0022'),
            'json_contains_hex_apostrophe' => str_contains($json, '\u0027'),
        ],
    ],
    'auth_legacy_md5_hash_is_rejected' => [
        'method' => 'AUTH_SERVICE_ATTEMPT',
        'path' => '/admin/login',
        'input_json' => [
            'email' => $email,
            'password' => $legacyPassword,
            'stored_password_shape' => '32-character legacy md5 hash',
        ],
        'status' => 401,
        'output_json' => [
            'success' => $authResult['success'] ?? null,
            'error' => $authResult['error'] ?? null,
            'stored_password_unchanged' => ($authRow['password'] ?? '') === $legacyHash,
            'failed_login_count' => (int) ($authRow['failed_login_count'] ?? 0),
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
