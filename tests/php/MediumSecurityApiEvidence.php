<?php

declare(strict_types=1);

use App\Controllers\Admin\FeatureController;
use App\Controllers\Admin\PresensiController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Feature;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\TeamMember;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function evidence_medium_request(array $json = [], string $method = 'POST'): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['HTTP_ACCEPT'] = 'application/json';

    $request = new Request();
    $property = new ReflectionProperty(Request::class, 'jsonBody');
    $property->setAccessible(true);
    $property->setValue($request, $json);

    return $request;
}

/** @return array{status: int, json: array<string, mixed>, raw: string} */
function evidence_medium_capture(callable $callback): array
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

$db = Database::connection();
$createdPresensiId = null;
$createdFeatureId = null;
$createdImageId = null;
$outsideFile = dirname(__DIR__, 2) . '/public/security-evidence-outside.txt';

try {
    $memberRows = $db->query('SELECT id FROM teams WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 2')->fetchAll(PDO::FETCH_ASSOC);
    $memberIds = array_map(static fn(array $row): int => (int) $row['id'], $memberRows);
    if ($memberIds === []) {
        throw new RuntimeException('No active team members available for evidence run.');
    }

    $events = new PresensiEvent($db);
    $presensiController = new PresensiController($events, new PresensiSubmission($db), new TeamMember($db));
    $presensiInput = [
        'event_name' => 'Evidence Presensi ' . bin2hex(random_bytes(3)),
        'location' => 'KPw BI Jambi',
        'roles' => [['name' => 'Peserta', 'score' => 5]],
        'member_ids' => $memberIds,
        'status' => 'open',
        'public_token_expires_at' => '2099-12-31 23:59:59',
    ];

    $presensiStore = evidence_medium_capture(static function () use ($presensiController, $presensiInput): void {
        $presensiController->store(evidence_medium_request($presensiInput, 'POST'), new Response());
    });
    $createdPresensiId = (int) ($presensiStore['json']['data']['id'] ?? 0);

    $presensiShow = evidence_medium_capture(static function () use ($presensiController, $createdPresensiId): void {
        $presensiController->show(evidence_medium_request([], 'GET'), new Response(), ['id' => $createdPresensiId]);
    });

    $feature = new Feature($db);
    $insertFeature = $db->prepare("INSERT INTO tbl_feature (name, content, icon, title, description, focus, icon_key, status, show_on_home, sort_order, created_at) VALUES (:name, :content, :icon, :title, :description, :focus, :icon_key, 'draft', 0, 0, NOW())");
    $insertFeature->execute([
        ':name' => 'Evidence feature containment',
        ':content' => 'Temporary feature for evidence run',
        ':icon' => 'sparkles',
        ':title' => 'Evidence SEC-03',
        ':description' => 'Temporary feature for evidence run',
        ':focus' => 'security',
        ':icon_key' => 'sparkles',
    ]);
    $createdFeatureId = (int) $db->lastInsertId();

    file_put_contents($outsideFile, 'outside');
    $maliciousImagePath = '/uploads/features/../../security-evidence-outside.txt';
    $insertImage = $db->prepare('INSERT INTO tbl_feature_image (feature_id, image_path, sort_order, created_at, updated_at) VALUES (:feature_id, :image_path, 0, NOW(), NOW())');
    $insertImage->execute([
        ':feature_id' => $createdFeatureId,
        ':image_path' => $maliciousImagePath,
    ]);
    $createdImageId = (int) $db->lastInsertId();

    $featureController = new FeatureController($feature);
    $featureDelete = evidence_medium_capture(static function () use ($featureController, $createdFeatureId, $createdImageId): void {
        $featureController->deleteImage(evidence_medium_request([], 'POST'), new Response(), [
            'id' => $createdFeatureId,
            'imageId' => $createdImageId,
        ]);
    });

    echo json_encode([
        'presensi_store_returns_one_time_plain_token' => [
            'method' => 'POST',
            'path' => '/admin/presensi',
            'input_json' => $presensiInput,
            'status' => $presensiStore['status'],
            'output_json' => [
                'data' => [
                    'id' => $presensiStore['json']['data']['id'] ?? null,
                    'public_token' => $presensiStore['json']['data']['public_token'] ?? null,
                    'public_token_hash' => $presensiStore['json']['data']['public_token_hash'] ?? null,
                    'public_url' => $presensiStore['json']['data']['public_url'] ?? null,
                    'event_public_token_after_refetch' => $presensiStore['json']['data']['event']['public_token'] ?? null,
                    'event_public_url_after_refetch' => $presensiStore['json']['data']['event']['public_url'] ?? null,
                ],
            ],
        ],
        'presensi_show_does_not_reexpose_token' => [
            'method' => 'GET',
            'path' => '/admin/presensi/' . $createdPresensiId,
            'input_json' => ['route_id' => $createdPresensiId, 'accept' => 'application/json'],
            'status' => $presensiShow['status'],
            'output_json' => [
                'data' => [
                    'id' => $presensiShow['json']['data']['id'] ?? null,
                    'public_token' => $presensiShow['json']['data']['public_token'] ?? null,
                    'public_url' => $presensiShow['json']['data']['public_url'] ?? null,
                    'public_token_hash' => $presensiShow['json']['data']['public_token_hash'] ?? null,
                ],
            ],
        ],
        'feature_delete_rejects_path_traversal_unlink' => [
            'method' => 'POST',
            'path' => '/admin/features/' . $createdFeatureId . '/images/' . $createdImageId . '/delete',
            'input_json' => [
                'route_id' => $createdFeatureId,
                'route_imageId' => $createdImageId,
                'stored_image_path' => $maliciousImagePath,
            ],
            'status' => $featureDelete['status'],
            'output_json' => $featureDelete['json'],
            'filesystem_assertion' => ['outside_file_still_exists' => is_file($outsideFile)],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} finally {
    if ($createdPresensiId) {
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event_member WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
        $stmt = $db->prepare('DELETE FROM tbl_presensi_submission WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
    }
    if ($createdImageId) {
        $stmt = $db->prepare('DELETE FROM tbl_feature_image WHERE id = :id');
        $stmt->execute([':id' => $createdImageId]);
    }
    if ($createdFeatureId) {
        $stmt = $db->prepare('DELETE FROM tbl_feature WHERE id = :id');
        $stmt->execute([':id' => $createdFeatureId]);
    }
    if (is_file($outsideFile)) {
        @unlink($outsideFile);
    }
}
