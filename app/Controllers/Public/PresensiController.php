<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Models\PresensiEvent;
use App\Models\PresensiSubmission;
use App\Services\CsrfService;

final class PresensiController
{
    private const UPLOAD_DIR = '/uploads/presensi/';
    private const MAX_UPLOAD_SIZE = 5_242_880;
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private ?PresensiEvent $events = null,
        private ?PresensiSubmission $submissions = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {}

    public function show(Request $request, Response $response, array $params): void
    {
        $token = $this->safeToken((string) ($params['token'] ?? ''));
        $event = $this->events?->findByPublicToken($token, true);

        if ($request->acceptsJson()) {
            if (!$event) {
                $response->json(['error' => 'Link presensi tidak valid atau sudah ditutup'], 404);
                return;
            }
            $response->json(['data' => $this->publicEventPayload($event)]);
            return;
        }

        if (!$event || !$this->viewRenderer instanceof ViewRenderer) {
            ErrorHandler::render($response, 404, 'Presensi tidak tersedia', 'Link presensi tidak valid, sudah ditutup, atau event sudah diarsipkan.');
            return;
        }

        $title = 'Presensi ' . ($event['event_name'] ?? 'GenBI Jambi');
        $meta = '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>' . PHP_EOL
            . '<meta name="robots" content="noindex, nofollow">';

        $html = $this->viewRenderer->renderWithLayout('public/presensi/show.php', 'layouts/public.php', [
            'event' => $event,
            'token' => $token,
            'csrfToken' => CsrfService::token(),
            'meta' => $meta,
            'bodyClass' => 'page-presensi page-ready',
            'scripts' => '<script defer src="/assets/js/dist/pages/presensi.js?v=20260617a"></script>',
        ]);
        $response->html($html, 200, ['X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function members(Request $request, Response $response, array $params): void
    {
        $token = $this->safeToken((string) ($params['token'] ?? ''));
        $event = $this->events?->findByPublicToken($token, true);
        if (!$event) {
            $response->json(['error' => 'Link presensi tidak valid atau sudah ditutup'], 404);
            return;
        }

        $query = trim((string) ($request->query('q') ?? ''));
        $items = $this->events?->memberOptionsForEvent((int) $event['id'], $query, 12) ?? [];
        $response->json(['data' => $items]);
    }

    public function submit(Request $request, Response $response, array $params): void
    {
        $token = $this->safeToken((string) ($params['token'] ?? ''));
        $event = $this->events?->findByPublicToken($token, true);
        if (!$event) {
            $response->json(['error' => 'Link presensi tidak valid atau sudah ditutup'], 404);
            return;
        }

        $body = !empty($_POST) ? $_POST : $request->json();
        $teamId = (int) ($body['team_id'] ?? 0);
        $role = strip_tags(mb_substr(trim((string) ($body['role'] ?? '')), 0, 120));
        $errors = $this->validateSubmission((int) $event['id'], $teamId, $role, $event);

        $photoPath = '';
        if ($errors === []) {
            $upload = $this->storePhoto($_FILES['photo'] ?? null);
            if (isset($upload['error'])) {
                $errors[] = $upload['error'];
            } else {
                $photoPath = $upload['url'];
            }
        }

        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $id = $this->submissions?->create([
            'presensi_event_id' => (int) $event['id'],
            'team_id' => $teamId,
            'role' => $role,
            'photo_path' => $photoPath,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!$id) {
            $this->deleteUpload($photoPath);
            $response->json(['error' => 'Presensi untuk anggota ini sudah pernah dikirim'], 409);
            return;
        }

        $response->json(['data' => ['id' => $id, 'status' => 'pending']], 201);
    }

    /** @return array<int, string> */
    private function validateSubmission(int $eventId, int $teamId, string $role, array $event): array
    {
        $errors = [];
        if ($teamId <= 0 || !$this->events?->memberBelongsToEvent($eventId, $teamId)) {
            $errors[] = 'Nama wajib dipilih dari dropdown anggota event';
        }
        if ($role === '' || !$this->events?->roleIsAllowed($event, $role)) {
            $errors[] = 'Role wajib dipilih dari opsi event';
        }
        if ($teamId > 0 && $this->submissions?->existsForEventMember($eventId, $teamId)) {
            $errors[] = 'Presensi untuk anggota ini sudah pernah dikirim';
        }

        return $errors;
    }

    /** @return array{url: string, filename: string, mime: string, size: int}|array{error: string} */
    private function storePhoto(mixed $file): array
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'Bukti foto wajib diunggah'];
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload bukti foto gagal'];
        }
        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > self::MAX_UPLOAD_SIZE) {
            return ['error' => 'Ukuran bukti foto maksimal 5MB'];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $imageInfo = is_file($tmp) ? @getimagesize($tmp) : false;
        $mimeType = $this->detectUploadedMime($tmp, is_array($imageInfo) ? $imageInfo : []);
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true) || $imageInfo === false) {
            return ['error' => 'Bukti foto harus berupa JPEG, PNG, atau WebP yang valid'];
        }

        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = 'presensi-' . bin2hex(random_bytes(12)) . '.' . $extension;
        $uploadDir = dirname(__DIR__, 3) . '/public' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $htaccess = $uploadDir . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\nOptions -ExecCGI\nAddType text/plain .php .phtml .php3 .php4 .php5\n");
        }

        if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
            return ['error' => 'Gagal menyimpan bukti foto'];
        }

        return [
            'url' => self::UPLOAD_DIR . $filename,
            'filename' => $filename,
            'mime' => $mimeType,
            'size' => (int) $file['size'],
        ];
    }

    /** @param array<string, mixed> $imageInfo */
    private function detectUploadedMime(string $tmp, array $imageInfo): string
    {
        if ($tmp === '' || !is_file($tmp)) {
            return '';
        }
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = (string) $finfo->file($tmp);
            if ($mime !== '') {
                return $mime;
            }
        }
        if (function_exists('mime_content_type')) {
            $mime = (string) @mime_content_type($tmp);
            if ($mime !== '') {
                return $mime;
            }
        }

        return (string) ($imageInfo['mime'] ?? '');
    }

    private function deleteUpload(string $url): void
    {
        if ($url === '' || !str_starts_with($url, self::UPLOAD_DIR)) {
            return;
        }
        $path = dirname(__DIR__, 3) . '/public' . $url;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** @return array<string, mixed> */
    private function publicEventPayload(array $event): array
    {
        return [
            'id' => (int) ($event['id'] ?? 0),
            'event_name' => (string) ($event['event_name'] ?? ''),
            'location' => (string) ($event['location'] ?? ''),
            'roles' => is_array($event['roles'] ?? null) ? $event['roles'] : [],
            'role_options' => is_array($event['role_options'] ?? null) ? $event['role_options'] : [],
        ];
    }

    private function safeToken(string $token): string
    {
        return mb_substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '', 0, 120);
    }
}
