<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Event;
use App\Services\HtmlSanitizer;

final class EventController
{
    private const MAX_TITLE_LENGTH = 255;
    private const MAX_TEXT_LENGTH = 5000;

    public function __construct(private ?Event $events = null)
    {
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->events) {
            $response->json(['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 1]]);
            return;
        }

        $page = max(1, (int) ($request->query('page') ?? 1));
        $perPage = max(1, min(100, (int) ($request->query('per_page') ?? 25)));
        $offset = ($page - 1) * $perPage;
        $query = trim((string) ($request->query('q') ?? ''));

        $items = $this->events->allForAdmin($perPage, $offset);
        if ($query !== '') {
            $items = array_values(array_filter($items, static function (array $item) use ($query): bool {
                $needle = mb_strtolower($query);
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($item['title'] ?? ''),
                    (string) ($item['excerpt'] ?? ''),
                    (string) ($item['location'] ?? ''),
                ]));
                return str_contains($haystack, $needle);
            }));
        }

        $total = count($items);
        $response->json([
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->events?->findById($id);

        if (!$item) {
            $response->json(['error' => 'Agenda tidak ditemukan'], 404);
            return;
        }

        $response->json(['data' => $item]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->events) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $errors = $this->validate($body, true);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $id = $this->events->create($this->sanitize($body));
        if (!$id) {
            $response->json(['error' => 'Gagal menyimpan agenda'], 500);
            return;
        }

        $response->json(['data' => ['id' => $id]], 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->events) {
            $response->json(['error' => 'Database tidak tersedia'], 500);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $body = $request->json();
        $errors = $this->validate($body, false);
        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $success = $this->events->update($id, $this->sanitize($body, false));
        if (!$success) {
            $response->json(['error' => 'Gagal memperbarui agenda'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'updated' => true]]);
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $success = $this->events?->delete($id) ?? false;
        if (!$success) {
            $response->json(['error' => 'Gagal menghapus agenda'], 404);
            return;
        }

        $response->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    /** @return array<int, string> */
    private function validate(array $body, bool $isCreate): array
    {
        $errors = [];
        $title = trim((string) ($body['title'] ?? $body['event_title'] ?? ''));
        if ($isCreate || array_key_exists('title', $body) || array_key_exists('event_title', $body)) {
            if ($title === '') {
                $errors[] = 'Judul agenda wajib diisi.';
            }
            if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
                $errors[] = 'Judul agenda terlalu panjang.';
            }
        }

        return $errors;
    }

    /** @return array<string, mixed> */
    private function sanitize(array $body, bool $includeDefaults = true): array
    {
        $payload = [];
        $textFields = [
            'event_title' => ['title', self::MAX_TITLE_LENGTH],
            'event_content_short' => ['excerpt', self::MAX_TEXT_LENGTH],
            'event_location' => ['location', 255],
            'event_map' => ['map', 2000],
            'photo' => ['photo', 2000],
            'banner' => ['banner', 2000],
            'meta_title' => ['meta_title', 255],
            'meta_keyword' => ['meta_keyword', 1000],
            'meta_description' => ['meta_description', 1000],
        ];

        foreach ($textFields as $target => [$source, $limit]) {
            if (!$includeDefaults && !array_key_exists($source, $body) && !array_key_exists($target, $body)) {
                continue;
            }
            $value = (string) ($body[$source] ?? $body[$target] ?? '');
            $payload[$target] = strip_tags(mb_substr(trim($value), 0, $limit));
        }

        if ($includeDefaults || array_key_exists('content', $body) || array_key_exists('event_content', $body)) {
            $payload['event_content'] = HtmlSanitizer::sanitize((string) ($body['content'] ?? $body['event_content'] ?? ''));
        }
        if ($includeDefaults || array_key_exists('start_date', $body) || array_key_exists('event_start_date', $body)) {
            $payload['event_start_date'] = substr((string) ($body['start_date'] ?? $body['event_start_date'] ?? date('Y-m-d')), 0, 10);
        }
        if ($includeDefaults || array_key_exists('end_date', $body) || array_key_exists('event_end_date', $body)) {
            $payload['event_end_date'] = substr((string) ($body['end_date'] ?? $body['event_end_date'] ?? date('Y-m-d')), 0, 10);
        }

        return $payload;
    }
}
