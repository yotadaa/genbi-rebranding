<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\ThemeRegistry;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ViewRenderer;
use App\Models\Setting;
use App\Services\CsrfService;
use App\Services\SiteSettings;

final class SettingsController
{
    private const BRANDING_UPLOAD_DIR = '/uploads/branding/';
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'];
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;

    public function __construct(
        private ?Setting $settings = null,
        private ?SiteSettings $siteSettings = null,
        private ?ViewRenderer $viewRenderer = null,
    ) {
    }

    public function edit(Request $request, Response $response): void
    {
        if (!$this->viewRenderer instanceof ViewRenderer || !$this->siteSettings instanceof SiteSettings) {
            $response->json(['error' => 'Settings view tidak tersedia'], 500);
            return;
        }

        $response->html($this->viewRenderer->renderWithLayout('admin/settings/index.php', 'layouts/admin.php', [
            'title' => 'Settings | Admin GenBI',
            'csrfToken' => CsrfService::token(),
            'cmsPage' => 'settings',
            'cmsMode' => 'settings',
            'settingsData' => $this->bootstrapData(),
            'scripts' => '<script defer src="/assets/js/dist/theme-registry.js?v=20260510a"></script><script src="/assets/js/dist/admin/settings.js?v=20260510a"></script>',
        ]), 200, ['X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function data(Request $request, Response $response): void
    {
        $response->json(['data' => $this->bootstrapData()]);
    }

    public function showTheme(Request $request, Response $response): void
    {
        $response->json(['data' => [
            'publicKey' => $this->siteSettings?->themeKey('public') ?? 'genbi',
            'adminKey' => $this->siteSettings?->themeKey('admin') ?? 'genbi',
            'themes' => ThemeRegistry::summaries(),
        ]]);
    }

    public function updateTheme(Request $request, Response $response): void
    {
        if (!$this->settings instanceof Setting) {
            $response->json(['error' => 'Settings tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $publicKey = (string) ($body['theme.public_key'] ?? $body['public_key'] ?? 'genbi');
        $adminKey = (string) ($body['theme.admin_key'] ?? $body['admin_key'] ?? 'genbi');
        $allowed = ThemeRegistry::keys();
        $errors = [];

        if (!in_array($publicKey, $allowed, true)) {
            $errors['theme.public_key'] = 'Theme public tidak valid.';
        }
        if (!in_array($adminKey, $allowed, true)) {
            $errors['theme.admin_key'] = 'Theme admin tidak valid.';
        }

        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $this->settings->putMany([
            'theme.public_key' => $publicKey,
            'theme.admin_key' => $adminKey,
        ], $this->currentUserId());

        SiteSettings::clearCache();
        $response->json(['ok' => true, 'data' => ['theme.public_key' => $publicKey, 'theme.admin_key' => $adminKey]]);
    }

    public function upload(Request $request, Response $response): void
    {
        if (empty($_FILES['image'])) {
            $response->json(['error' => 'Tidak ada file yang diunggah'], 422);
            return;
        }

        $file = $_FILES['image'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $response->json(['error' => 'Upload gagal'], 422);
            return;
        }

        if (($file['size'] ?? 0) > self::MAX_UPLOAD_SIZE) {
            $response->json(['error' => 'Ukuran file melebihi batas 5MB'], 422);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES, true)) {
            $response->json(['error' => 'Tipe file tidak diizinkan'], 422);
            return;
        }

        if ($mimeType !== 'image/svg+xml' && @getimagesize($file['tmp_name']) === false) {
            $response->json(['error' => 'File gambar tidak valid'], 422);
            return;
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            'image/svg+xml' => 'svg',
            default => 'png',
        };

        $filename = 'branding-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 3) . '/public' . self::BRANDING_UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $htaccess = $uploadDir . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
        }

        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $response->json(['error' => 'Gagal menyimpan file'], 500);
            return;
        }

        $response->json(['data' => ['url' => self::BRANDING_UPLOAD_DIR . $filename]], 201);
    }

    public function updateLogo(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, ['site.logo_url' => ['type' => 'url', 'required' => true, 'max' => 500]]);
    }

    public function updateFavicon(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, ['site.favicon_url' => ['type' => 'url', 'required' => true, 'max' => 500]]);
    }

    public function updateTopbar(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.topbar_email' => ['type' => 'email', 'required' => true, 'max' => 255],
            'site.topbar_phone' => ['type' => 'string', 'required' => true, 'max' => 80],
        ]);
    }

    public function updateFooter(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.name' => ['type' => 'string', 'required' => true, 'max' => 255],
            'site.tagline' => ['type' => 'string', 'required' => true, 'max' => 255],
            'site.footer_copyright' => ['type' => 'string', 'required' => true, 'max' => 255],
            'site.footer_address' => ['type' => 'string', 'required' => true, 'max' => 500],
            'site.footer_email' => ['type' => 'email', 'required' => true, 'max' => 255],
            'site.footer_phone' => ['type' => 'string', 'required' => true, 'max' => 120],
            'site.footer_recent_news_count' => ['type' => 'int', 'required' => true, 'min' => 1, 'max' => 20],
        ]);
    }

    public function updateEmail(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.email_from' => ['type' => 'email', 'required' => true, 'max' => 255],
            'site.email_to' => ['type' => 'email', 'required' => true, 'max' => 255],
        ]);
    }

    public function updateBanner(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.banner_badge' => ['type' => 'string', 'required' => true, 'max' => 120],
            'site.banner_headline' => ['type' => 'string', 'required' => true, 'max' => 255],
            'site.banner_headline_alt' => ['type' => 'string', 'required' => false, 'max' => 255],
            'site.banner_subtitle' => ['type' => 'string', 'required' => true, 'max' => 500],
            'site.banner_subtitle_alt' => ['type' => 'string', 'required' => false, 'max' => 500],
            'site.banner_image_1' => ['type' => 'url', 'required' => true, 'max' => 500],
            'site.banner_image_2' => ['type' => 'url', 'required' => true, 'max' => 500],
        ]);
    }

    public function updateSidebar(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.sidebar_heading_news' => ['type' => 'string', 'required' => true, 'max' => 80],
            'site.sidebar_heading_recent' => ['type' => 'string', 'required' => true, 'max' => 80],
            'site.sidebar_heading_upcoming' => ['type' => 'string', 'required' => true, 'max' => 80],
            'site.sidebar_heading_past' => ['type' => 'string', 'required' => true, 'max' => 80],
            'site.sidebar_heading_contact' => ['type' => 'string', 'required' => true, 'max' => 80],
        ]);
    }

    public function updateColor(Request $request, Response $response): void
    {
        $this->updateSettings($request, $response, [
            'site.color_primary' => ['type' => 'hex', 'required' => true],
            'site.color_primary_hover' => ['type' => 'hex', 'required' => true],
            'site.color_primary_soft' => ['type' => 'hex', 'required' => true],
        ]);
    }

    /** @param array<string, array<string, mixed>> $rules */
    private function updateSettings(Request $request, Response $response, array $rules): void
    {
        if (!$this->settings instanceof Setting) {
            $response->json(['error' => 'Settings tidak tersedia'], 500);
            return;
        }

        $body = $request->json();
        $clean = [];
        $errors = [];

        foreach ($rules as $key => $rule) {
            $value = $body[$key] ?? null;
            if (($rule['required'] ?? false) && ($value === null || trim((string) $value) === '')) {
                $errors[$key] = 'Field wajib diisi.';
                continue;
            }
            if ($value === null) {
                continue;
            }

            $normalized = $this->normalizeValue((string) $value, $rule, $errors, $key);
            if ($normalized !== null) {
                $clean[$key] = $normalized;
            }
        }

        if ($errors !== []) {
            $response->json(['error' => 'Validasi gagal', 'details' => $errors], 422);
            return;
        }

        $this->settings->putMany($clean, $this->currentUserId());
        SiteSettings::clearCache();
        $response->json(['ok' => true, 'data' => $clean]);
    }

    /** @param array<string, mixed> $rule @param array<string, string> $errors */
    private function normalizeValue(string $value, array $rule, array &$errors, string $key): string|int|null
    {
        $trimmed = trim(strip_tags($value));
        return match ($rule['type']) {
            'email' => $this->normalizeEmail($trimmed, $errors, $key, (int) ($rule['max'] ?? 255)),
            'url' => $this->normalizeUrl($trimmed, $errors, $key, (int) ($rule['max'] ?? 500)),
            'int' => $this->normalizeInt($trimmed, $errors, $key, (int) ($rule['min'] ?? 0), (int) ($rule['max'] ?? PHP_INT_MAX)),
            'hex' => $this->normalizeHex($trimmed, $errors, $key),
            default => $this->normalizeString($trimmed, $errors, $key, (int) ($rule['max'] ?? 255)),
        };
    }

    /** @param array<string, string> $errors */
    private function normalizeString(string $value, array &$errors, string $key, int $max): ?string
    {
        if (mb_strlen($value) > $max) {
            $errors[$key] = 'Melebihi panjang maksimum.';
            return null;
        }

        return $value;
    }

    /** @param array<string, string> $errors */
    private function normalizeEmail(string $value, array &$errors, string $key, int $max): ?string
    {
        if (mb_strlen($value) > $max || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $errors[$key] = 'Email tidak valid.';
            return null;
        }

        return $value;
    }

    /** @param array<string, string> $errors */
    private function normalizeUrl(string $value, array &$errors, string $key, int $max): ?string
    {
        if (mb_strlen($value) > $max || !preg_match('#^(https?://|/)#', $value)) {
            $errors[$key] = 'URL tidak valid.';
            return null;
        }

        return $value;
    }

    /** @param array<string, string> $errors */
    private function normalizeInt(string $value, array &$errors, string $key, int $min, int $max): ?int
    {
        if (!preg_match('/^-?\d+$/', $value)) {
            $errors[$key] = 'Angka tidak valid.';
            return null;
        }
        $int = (int) $value;
        if ($int < $min || $int > $max) {
            $errors[$key] = 'Angka di luar batas.';
            return null;
        }

        return $int;
    }

    /** @param array<string, string> $errors */
    private function normalizeHex(string $value, array &$errors, string $key): ?string
    {
        $hex = strtoupper($value);
        if (!preg_match('/^#[0-9A-F]{6}$/', $hex)) {
            $errors[$key] = 'Warna harus format #RRGGBB.';
            return null;
        }

        return strtolower($hex);
    }

    /** @return array<string, mixed> */
    private function bootstrapData(): array
    {
        $site = $this->siteSettings?->site() ?? [];
        return [
            'site' => $site,
            'theme' => [
                'publicKey' => $this->siteSettings?->themeKey('public') ?? 'genbi',
                'adminKey' => $this->siteSettings?->themeKey('admin') ?? 'genbi',
                'themes' => ThemeRegistry::summaries(),
            ],
        ];
    }

    private function currentUserId(): ?int
    {
        $user = Session::get('_auth_user');
        if (!is_array($user)) {
            return null;
        }

        $id = (int) ($user['user_id'] ?? $user['id'] ?? 0);
        return $id > 0 ? $id : null;
    }
}
