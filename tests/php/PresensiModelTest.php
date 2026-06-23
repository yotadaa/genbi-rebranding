<?php

declare(strict_types=1);

use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\TeamMember;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect(bool $condition, string $message = 'Expectation failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$mapped = PresensiEvent::mapRow([
    'presensi_event_id' => 9,
    'slug' => 'siginjai-fest',
    'public_token' => 'abc123',
    'event_name' => 'Siginjai Fest',
    'location' => 'KPw BI Jambi',
    'roles_json' => '["Panitia","Peserta","Panitia",""]',
    'member_count' => 2,
    'submission_count' => 1,
    'pending_count' => 1,
    'approved_count' => 0,
    'status' => 'open',
]);

expect($mapped['id'] === 9);
expect($mapped['roles'] === ['Panitia', 'Peserta']);
expect(PresensiEvent::normalizeRoleOptions([
    ['name' => 'Panitia', 'score' => 10],
    ['label' => 'Peserta', 'points' => '5'],
    'Tamu',
    ['name' => 'Panitia', 'score' => 99],
]) === [
    ['name' => 'Panitia', 'score' => 10],
    ['name' => 'Peserta', 'score' => 5],
    ['name' => 'Tamu', 'score' => 0],
], 'Role options should keep unique names with manual scores');
$scored = PresensiEvent::mapRow([
    'presensi_event_id' => 10,
    'public_token' => 'scored123',
    'event_name' => 'Scored Event',
    'roles_json' => '[{"name":"Panitia","score":10},{"label":"Peserta","points":"5"},"Tamu"]',
]);
expect($scored['roles'] === ['Panitia', 'Peserta', 'Tamu']);
expect($scored['role_options'][0]['score'] === 10);
expect(PresensiEvent::roleScore($scored, 'Peserta') === 5);
expect(PresensiEvent::roleScore($scored, 'Tamu') === 0);
expect($mapped['public_token'] === '', 'Presensi mapRow must not expose stored public_token.');
expect($mapped['public_url'] === '', 'Presensi mapRow must not reconstruct public_url from stored public_token.');
expect($mapped['member_count'] === 2);
expect($mapped['pending_count'] === 1);

$db = App\Core\Database::connection();
$createdEventId = null;
$firstSubmission = null;
$testSuffix = bin2hex(random_bytes(4));

$memberRows = $db->query('SELECT id, name FROM teams WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 2')->fetchAll(PDO::FETCH_ASSOC);
expect(count($memberRows) >= 2, 'PresensiModelTest requires at least two active team members');
$firstMemberId = (int) $memberRows[0]['id'];
$secondMemberId = (int) $memberRows[1]['id'];
$firstMemberName = (string) $memberRows[0]['name'];
$secondMemberName = (string) $memberRows[1]['name'];

try {
$team = new TeamMember($db);
$options = $team->searchOptions($firstMemberName, 5);
$optionIds = array_map(static fn(array $item): int => (int) $item['id'], $options);
expect(in_array($firstMemberId, $optionIds, true));
expect($options[0]['name'] !== '');
$divisionRows = $db->query('SELECT DISTINCT divisi_id FROM teams WHERE deleted_at IS NULL AND divisi_id IS NOT NULL AND divisi_id > 0 ORDER BY divisi_id ASC LIMIT 2')->fetchAll(PDO::FETCH_COLUMN);
if (count($divisionRows) >= 2) {
    $filteredOptions = $team->searchOptions('', 200, ['division_id' => (int) $divisionRows[0]]);
    expect($filteredOptions !== [], 'Division-filtered member picker should return active members');
    expect(
        array_reduce($filteredOptions, static fn(bool $carry, array $item): bool => $carry && (int) ($item['division_id'] ?? 0) === (int) $divisionRows[0], true),
        'Division-filtered member picker should only include selected division'
    );
}

$events = new PresensiEvent($db);
$created = $events->create([
    'event_name' => 'Pelatihan QRIS Test ' . $testSuffix,
    'location' => 'Aula GenBI',
    'roles' => [['name' => 'Panitia', 'score' => 10], ['name' => 'Peserta', 'score' => 5], ['name' => 'Panitia', 'score' => 99]],
    'member_ids' => [$firstMemberId, 999999999, $secondMemberId],
    'created_by' => 7,
]);
$createdEventId = (int) $created['id'];

expect($created['id'] > 0);
expect(str_starts_with($created['public_token'], 'prs_'));
expect(strlen($created['public_token']) >= 40);
expect($created['public_token_hash'] === hash('sha256', $created['public_token']));
$storedToken = $db->query('SELECT public_token FROM tbl_presensi_event WHERE presensi_event_id = ' . (int) $created['id'])->fetchColumn();
expect($storedToken !== $created['public_token'], 'Presensi create must not store the plain bearer token in public_token.');
expect($events->findByPublicToken((string) $storedToken) === null, 'Stored public_token marker must not be usable as a public bearer token.');

$event = $events->findByPublicToken($created['public_token']);
expect($event !== null);
expect($event['public_token'] === '', 'Fetched Presensi events must not expose public_token after creation response.');
expect($event['public_url'] === '', 'Fetched Presensi events must not expose reusable public_url after creation response.');
expect($event['roles'] === ['Panitia', 'Peserta']);
expect($event['role_options'] === [['name' => 'Panitia', 'score' => 10], ['name' => 'Peserta', 'score' => 5]]);
expect(PresensiEvent::roleScore($event, 'Panitia') === 10);
$initialMemberIds = array_map('intval', array_column($event['members'], 'id'));
sort($initialMemberIds);
$expectedInitialIds = [$firstMemberId, $secondMemberId];
sort($expectedInitialIds);
expect($initialMemberIds === $expectedInitialIds);

$synced = $events->syncMembers($created['id'], [$secondMemberId, 999999999]);
expect($synced === 1);
$eventAfterSync = $events->findById($created['id']);
expect(array_map('intval', array_column($eventAfterSync['members'], 'id')) === [$secondMemberId]);

$submissions = new PresensiSubmission($db);
$firstSubmission = $submissions->create([
    'presensi_event_id' => $created['id'],
    'team_id' => $secondMemberId,
    'role' => 'Peserta',
    'photo_path' => '/uploads/presensi/proof.webp',
    'ip_address' => '127.0.0.1',
    'user_agent' => 'PresensiTest',
]);
expect($firstSubmission > 0);

$duplicate = $submissions->create([
    'presensi_event_id' => $created['id'],
    'team_id' => $secondMemberId,
    'role' => 'Panitia',
    'photo_path' => '/uploads/presensi/another.webp',
]);
expect($duplicate === null);

$items = $submissions->forEvent($created['id']);
expect(count($items) === 1);
expect($items[0]['member_name'] === $secondMemberName);
expect($items[0]['status'] === 'pending');

$approved = $submissions->approve($firstSubmission, 7);
expect($approved === true);
$approvedSubmission = $submissions->find($firstSubmission);
expect($approvedSubmission['status'] === 'approved');
expect((int) $approvedSubmission['approved_by'] === 7);

$cancelled = $submissions->cancel($firstSubmission);
expect($cancelled === true);
expect($submissions->find($firstSubmission) === null);
$firstSubmission = null;
} finally {
    if ($firstSubmission) {
        $stmt = $db->prepare('DELETE FROM tbl_presensi_submission WHERE submission_id = :id');
        $stmt->execute(['id' => $firstSubmission]);
    }
    if ($createdEventId) {
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event_member WHERE presensi_event_id = :id');
        $stmt->execute(['id' => $createdEventId]);
        $stmt = $db->prepare('DELETE FROM tbl_presensi_event WHERE presensi_event_id = :id');
        $stmt->execute(['id' => $createdEventId]);
    }
}

echo "PHP presensi model tests passed\n";
