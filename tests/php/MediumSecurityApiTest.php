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

function expect_medium_api(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function medium_request(array $json = [], string $method = 'POST'): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['HTTP_ACCEPT'] = 'application/json';

    $request = new Request();
    $property = new ReflectionProperty(Request::class, 'jsonBody');
    $property->setAccessible(true);
    $property->setValue($request, $json);

    return $request;
}

/** @return array{status: int, payload: array<string, mixed>, raw: string} */
function medium_capture_json(callable $callback): array
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

$db = Database::connection();
$createdPresensiId = null;
$createdFeatureId = null;
$createdFeatureImageId = null;
$outsideFile = dirname(__DIR__, 2) . '/public/security-delete-api-outside.txt';

$memberRows = $db->query('SELECT id FROM teams WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 2')->fetchAll(PDO::FETCH_ASSOC);
expect_medium_api(count($memberRows) >= 1, 'MediumSecurityApiTest requires at least one active team member.');
$memberIds = array_map(static fn(array $row): int => (int) $row['id'], $memberRows);

try {
    $events = new PresensiEvent($db);
    $presensiController = new PresensiController($events, new PresensiSubmission($db), new TeamMember($db));
    $presensiInput = [
        'event_name' => 'Security API Presensi ' . bin2hex(random_bytes(4)),
        'location' => 'KPw BI Jambi',
        'roles' => [['name' => 'Peserta', 'score' => 5]],
        'member_ids' => $memberIds,
        'status' => 'open',
        'public_token_expires_at' => '2099-12-31 23:59:59',
    ];

    $presensiStore = medium_capture_json(static function () use ($presensiController, $presensiInput): void {
        $presensiController->store(medium_request($presensiInput, 'POST'), new Response());
    });
    $createdPresensiId = (int) ($presensiStore['payload']['data']['id'] ?? 0);
    $createdPublicToken = (string) ($presensiStore['payload']['data']['public_token'] ?? '');
    $createdPublicUrl = (string) ($presensiStore['payload']['data']['public_url'] ?? '');

    expect_medium_api($presensiStore['status'] === 201, 'Presensi store API should return HTTP 201.');
    expect_medium_api($createdPresensiId > 0, 'Presensi store API should create an event id.');
    expect_medium_api(str_starts_with($createdPublicToken, 'prs_'), 'Presensi store API should return the plain token once.');
    expect_medium_api($createdPublicUrl === '/presensi/' . rawurlencode($createdPublicToken), 'Presensi store API should return the one-time public URL.');

    $presensiShow = medium_capture_json(static function () use ($presensiController, $createdPresensiId): void {
        $presensiController->show(medium_request([], 'GET'), new Response(), ['id' => $createdPresensiId]);
    });

    expect_medium_api($presensiShow['status'] === 200, 'Presensi show API should return HTTP 200 for created event.');
    expect_medium_api(($presensiShow['payload']['data']['public_token'] ?? null) === '', 'Presensi show API must not expose public_token after creation.');
    expect_medium_api(($presensiShow['payload']['data']['public_url'] ?? null) === '', 'Presensi show API must not expose public_url after creation.');

    $feature = new Feature($db);
    $insertFeature = $db->prepare(
        "INSERT INTO tbl_feature (name, content, icon, title, description, focus, icon_key, status, show_on_home, sort_order, created_at) VALUES (:name, :content, :icon, :title, :description, :focus, :icon_key, 'draft', 0, 0, NOW())"
    );
    $insertFeature->execute([
        ':name' => 'Security containment test',
        ':content' => 'Temporary feature for security API test',
        ':icon' => 'sparkles',
        ':title' => 'SEC-03',
        ':description' => 'Temporary feature for security API test',
        ':focus' => 'security',
        ':icon_key' => 'sparkles',
    ]);
    $createdFeatureId = (int) $db->lastInsertId();
    expect_medium_api($createdFeatureId > 0, 'Feature API test should create a temporary feature.');

    file_put_contents($outsideFile, 'outside');
    $insertImage = $db->prepare('INSERT INTO tbl_feature_image (feature_id, image_path, sort_order, created_at, updated_at) VALUES (:feature_id, :image_path, 0, NOW(), NOW())');
    $insertImage->execute([
        ':feature_id' => $createdFeatureId,
        ':image_path' => '/uploads/features/../../security-delete-api-outside.txt',
    ]);
    $createdFeatureImageId = (int) $db->lastInsertId();

    $featureController = new FeatureController($feature);
    $featureDelete = medium_capture_json(static function () use ($featureController, $createdFeatureId, $createdFeatureImageId): void {
        $featureController->deleteImage(medium_request([], 'POST'), new Response(), [
            'id' => $createdFeatureId,
            'imageId' => $createdFeatureImageId,
        ]);
    });

    expect_medium_api($featureDelete['status'] === 200, 'Feature image delete API should return HTTP 200 for an existing image row.');
    expect_medium_api(($featureDelete['payload']['data']['deleted'] ?? false) === true, 'Feature image delete API should delete the image row.');
    expect_medium_api(is_file($outsideFile), 'Feature image delete API must not delete traversal target outside uploads/features.');
} finally {
    if ($createdPresensiId) {
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event_member WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
        $stmt = $db->prepare('DELETE FROM tbl_presensi_submission WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event WHERE presensi_event_id = :id');
        $stmt->execute([':id' => $createdPresensiId]);
    }
    if ($createdFeatureImageId) {
        $stmt = $db->prepare('DELETE FROM tbl_feature_image WHERE id = :id');
        $stmt->execute([':id' => $createdFeatureImageId]);
    }
    if ($createdFeatureId) {
        $featureIdColumn = 'feature_id';
        try {
            $columns = $db->query('DESCRIBE tbl_feature')->fetchAll(PDO::FETCH_ASSOC);
            $names = array_column($columns, 'Field');
            $featureIdColumn = in_array('feature_id', $names, true) ? 'feature_id' : 'id';
        } catch (Throwable) {
        }
        $stmt = $db->prepare('DELETE FROM tbl_feature WHERE ' . $featureIdColumn . ' = :id');
        $stmt->execute([':id' => $createdFeatureId]);
    }
    if (is_file($outsideFile)) {
        @unlink($outsideFile);
    }
}

echo "PHP medium security API tests passed\n";
