<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Models\TeamMember;

final class PresensiController
{
    private const STATUSES = ['draft', 'open', 'closed', 'archived'];

    public function __construct(
        private ?PresensiEvent $events = null,
        private ?PresensiSubmission $submissions = null,
        private ?TeamMember $team = null,
    ) {}

    public function index(Request $request, Response $response): void
    {
        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 25, 100);
        $filters = [
            'q' => $request->query('q'),
            'status' => $request->query('status'),
        ];
        $items = $this->events?->allForAdmin($filters, $pg['per_page'], $pg['offset']) ?? [];
        $total = $this->events?->countForAdmin($filters) ?? count($items);

        $response->json([
            'data' => $items,
            'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $event = $this->events?->findById($id);
        if (!$event) {
            $response->json(['error' => 'Event presensi tidak ditemukan'], 404);
            return;
        }

        $response->json([
            'data' => $event,
            'submissions' => $this->submissions?->forEvent($id) ?? [],
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $body = $request->json();
        [$payload, $errors] = $this->validatedPayload($body);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $userId = $this->userId();
        $payload['created_by'] = $userId;
        $created = $this->events?->create($payload) ?? ['id' => 0];
        if ((int) ($created['id'] ?? 0) < 1) {
            $response->json(['error' => 'Gagal menyimpan event presensi'], 500);
            return;
        }

        $event = $this->events?->findById((int) $created['id']);
        $this->events?->logAudit('create', 'presensi_event', (string) $created['id'], $userId, null, $event, $request->ip(), $request->userAgent());
        $response->json(['data' => array_merge($created, ['event' => $event])], 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $before = $this->events?->findById($id);
        if (!$before) {
            $response->json(['error' => 'Event presensi tidak ditemukan'], 404);
            return;
        }

        [$payload, $errors] = $this->validatedPayload($request->json());
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $userId = $this->userId();
        $payload['updated_by'] = $userId;
        $success = $this->events?->update($id, $payload) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal memperbarui event presensi'], 500);
            return;
        }

        $after = $this->events?->findById($id);
        $this->events?->logAudit('update', 'presensi_event', (string) $id, $userId, $before, $after, $request->ip(), $request->userAgent());
        $response->json(['data' => ['id' => $id, 'updated' => true, 'event' => $after]]);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $before = $this->events?->findById($id);
        if (!$before) {
            $response->json(['error' => 'Event presensi tidak ditemukan'], 404);
            return;
        }

        $userId = $this->userId();
        $success = $this->events?->softDelete($id, $userId) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal menghapus event presensi'], 500);
            return;
        }

        $this->events?->logAudit('delete', 'presensi_event', (string) $id, $userId, $before, null, $request->ip(), $request->userAgent());
        $response->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    public function submissions(Request $request, Response $response, array $params): void
    {
        $eventId = (int) ($params['id'] ?? 0);
        if (!$this->events?->findById($eventId)) {
            $response->json(['error' => 'Event presensi tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => $this->submissions?->forEvent($eventId) ?? []]);
    }

    public function approve(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $before = $this->submissions?->find($id);
        if (!$before) {
            $response->json(['error' => 'Data kehadiran tidak ditemukan'], 404);
            return;
        }

        $userId = $this->userId();
        $success = $this->submissions?->approve($id, $userId) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal menyetujui presensi'], 500);
            return;
        }

        $after = $this->submissions?->find($id);
        $this->events?->logAudit('approve', 'presensi_submission', (string) $id, $userId, $before, $after, $request->ip(), $request->userAgent());
        $response->json(['data' => $after]);
    }

    public function cancel(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $before = $this->submissions?->find($id);
        if (!$before) {
            $response->json(['error' => 'Data kehadiran tidak ditemukan'], 404);
            return;
        }

        $userId = $this->userId();
        $success = $this->submissions?->cancel($id) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal membatalkan presensi'], 500);
            return;
        }

        $this->events?->logAudit('cancel', 'presensi_submission', (string) $id, $userId, $before, null, $request->ip(), $request->userAgent());
        $response->json(['data' => ['id' => $id, 'cancelled' => true]]);
    }

    public function approveMember(Request $request, Response $response, array $params): void
    {
        $eventId = (int) ($params['eventId'] ?? 0);
        $teamId = (int) ($params['teamId'] ?? 0);
        $event = $this->events?->findById($eventId);
        if (!$event) {
            $response->json(['error' => 'Event presensi tidak ditemukan'], 404);
            return;
        }

        if ($teamId <= 0 || !($this->events?->memberBelongsToEvent($eventId, $teamId) ?? false)) {
            $response->json(['error' => 'Anggota tidak terdaftar pada event ini'], 422);
            return;
        }

        if ($this->submissions?->existsForEventMember($eventId, $teamId)) {
            $response->json(['error' => 'Anggota ini sudah memiliki data presensi'], 409);
            return;
        }

        $body = $request->json();
        $role = strip_tags(mb_substr(trim((string) ($body['role'] ?? '')), 0, 120));
        if ($role === '' || !($this->events?->roleIsAllowed($event, $role) ?? false)) {
            $response->json(['error' => 'Role presensi tidak valid untuk event ini'], 422);
            return;
        }

        $userId = $this->userId();
        $submissionId = $this->submissions?->createManualApproved([
            'presensi_event_id' => $eventId,
            'team_id' => $teamId,
            'role' => $role,
            'approved_by' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!$submissionId) {
            $response->json(['error' => 'Gagal membuat presensi manual. Data mungkin sudah ada.'], 409);
            return;
        }

        $after = $this->submissions?->find($submissionId);
        $this->events?->logAudit('manual_approve', 'presensi_submission', (string) $submissionId, $userId, null, $after, $request->ip(), $request->userAgent());
        $response->json(['data' => $after], 201);
    }

    /** @param array<string, mixed> $body @return array{0: array<string, mixed>, 1: string[]} */
    private function validatedPayload(array $body): array
    {
        $errors = [];
        $eventName = strip_tags(mb_substr(trim((string) ($body['event_name'] ?? $body['name'] ?? '')), 0, 255));
        $location = strip_tags(mb_substr(trim((string) ($body['location'] ?? '')), 0, 255));
        $roles = PresensiEvent::normalizeRoleOptions($body['roles'] ?? []);
        $memberIds = $this->extractIds($body['member_ids'] ?? []);
        $validMemberIds = $this->events?->validTeamIds($memberIds) ?? [];
        $status = strtolower(trim((string) ($body['status'] ?? 'open')));

        if ($eventName === '') {
            $errors[] = 'Nama event wajib diisi';
        }
        if ($location === '') {
            $errors[] = 'Lokasi wajib diisi';
        }
        if ($roles === []) {
            $errors[] = 'Minimal satu role wajib ditambahkan';
        }
        if (count($roles) > 20) {
            $errors[] = 'Role maksimal 20 pilihan';
        }
        if ($memberIds === []) {
            $errors[] = 'Minimal satu anggota wajib dipilih dari dropdown';
        }
        if (count($validMemberIds) !== count($memberIds)) {
            $errors[] = 'Semua anggota harus dipilih dari data teams yang valid';
        }
        if (!in_array($status, self::STATUSES, true)) {
            $errors[] = 'Status event tidak valid';
        }

        return [[
            'event_name' => $eventName,
            'location' => $location,
            'roles' => $roles,
            'member_ids' => $validMemberIds,
            'status' => in_array($status, self::STATUSES, true) ? $status : 'open',
        ], $errors];
    }

    /** @return int[] */
    private function extractIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : preg_split('/[\s,]+/', $value);
        }
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value), static fn(int $id): bool => $id > 0)));
    }

    private function userId(): int
    {
        $user = Session::get('_auth_user');
        return is_array($user) ? (int) ($user['id'] ?? $user['user_id'] ?? 0) : 0;
    }
}
