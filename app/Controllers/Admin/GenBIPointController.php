<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\GenBIPoint;
use DateTimeImmutable;

final class GenBIPointController
{
    public function __construct(private ?GenBIPoint $points = null) {}

    public function members(Request $request, Response $response): void
    {
        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 25, 100);
        $filters = ['q' => $request->query('q')];
        $items = $this->points?->membersWithPoints($filters, $pg['per_page'], $pg['offset']) ?? [];
        $total = $this->points?->countMembers($filters) ?? count($items);

        $response->json([
            'data' => $items,
            'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
        ]);
    }

    public function activities(Request $request, Response $response): void
    {
        $pg = Paginator::resolve([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
        ], 25, 100);
        $filters = ['q' => $request->query('q')];
        $items = $this->points?->activities($filters, $pg['per_page'], $pg['offset']) ?? [];
        $total = $this->points?->countActivities($filters) ?? count($items);

        $response->json([
            'data' => $items,
            'meta' => Paginator::meta($pg['page'], $pg['per_page'], $total),
        ]);
    }

    public function showActivity(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->points?->findActivity($id);
        if (!$item) {
            $response->json(['error' => 'Aktivitas poin tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => $item]);
    }

    public function storeActivity(Request $request, Response $response): void
    {
        [$payload, $errors] = $this->validatedActivity($request->json());
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $payload['created_by'] = $this->userId();
        $id = $this->points?->createActivity($payload) ?? 0;
        if ($id <= 0) {
            $response->json(['error' => 'Gagal menyimpan aktivitas poin'], 500);
            return;
        }

        $response->json(['data' => ['id' => $id, 'activity' => $this->points?->findActivity($id)]], 201);
    }

    public function updateActivity(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        if (!$this->points?->findActivity($id)) {
            $response->json(['error' => 'Aktivitas poin tidak ditemukan'], 404);
            return;
        }

        [$payload, $errors] = $this->validatedActivity($request->json());
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $payload['updated_by'] = $this->userId();
        $success = $this->points?->updateActivity($id, $payload) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal memperbarui aktivitas poin'], 500);
            return;
        }

        $response->json(['data' => ['id' => $id, 'updated' => true, 'activity' => $this->points?->findActivity($id)]]);
    }

    /** @param array<string, mixed> $body @return array{0: array<string, mixed>, 1: string[]} */
    private function validatedActivity(array $body): array
    {
        $errors = [];
        $teamId = (int) ($body['team_id'] ?? 0);
        $activityName = strip_tags(mb_substr(trim((string) ($body['activity_name'] ?? '')), 0, 255));
        $points = filter_var($body['points'] ?? null, FILTER_VALIDATE_INT);
        $activityDate = trim((string) ($body['activity_date'] ?? ''));

        if ($teamId <= 0 || !($this->points?->teamExists($teamId) ?? false)) {
            $errors[] = 'Nama anggota wajib dipilih dari dropdown.';
        }
        if ($activityName === '') {
            $errors[] = 'Nama kegiatan wajib diisi.';
        }
        if ($points === false || $points === null || (int) $points < 0 || (int) $points > 100000) {
            $errors[] = 'Jumlah poin harus berupa angka 0 sampai 100000.';
        }
        if ($activityDate !== '' && !$this->validDate($activityDate)) {
            $errors[] = 'Tanggal kegiatan tidak valid.';
        }

        return [[
            'team_id' => $teamId,
            'activity_name' => $activityName,
            'points' => (int) ($points ?: 0),
            'activity_date' => $activityDate !== '' ? $activityDate : null,
        ], $errors];
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
    }

    private function userId(): int
    {
        $user = Session::get('_auth_user');
        return is_array($user) ? (int) ($user['id'] ?? $user['user_id'] ?? 0) : 0;
    }
}
