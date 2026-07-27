<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class SiteSettings
{
    private const CACHE_TTL = 300;

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    public function __construct(private ?Setting $settings = null)
    {
    }

    private static function getCacheFile(): string
    {
        return storage_path('framework/cache/site_settings.php');
    }

    public static function clearCache(): void
    {
        $file = self::getCacheFile();
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'https://genbijambi.com/public/')) {
            return str_replace('https://genbijambi.com/public/', '/', $url);
        }
        if (str_starts_with($url, 'https://genbijambi.com/')) {
            return str_replace('https://genbijambi.com/', '/', $url);
        }
        return $url;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if (is_array($this->cache)) {
            return $this->cache;
        }

        $cached = $this->loadFileCache();
        if ($cached !== null) {
            $this->cache = $cached;
            return $this->cache;
        }

        $stored = Setting::all();
        $data = self::defaults();

        foreach ($stored as $item) {
            $key = (string)($item->setting_key ?? $item['setting_key'] ?? '');
            $val = (string)($item->setting_value ?? $item['setting_value'] ?? '');
            if ($key !== '') {
                $data[$key] = $this->normalizeUrl($val);
            }
        }

        $publicTheme = $this->normalizeThemeKey((string) ($data['theme.public_key'] ?? 'genbi'));
        $adminTheme = $this->normalizeThemeKey((string) ($data['theme.admin_key'] ?? 'genbi'));

        $logoUrl = $this->normalizeUrl((string) ($data['site.logo_url'] ?? '/uploads/logo.png'));
        $faviconUrl = $this->normalizeUrl((string) ($data['site.favicon_url'] ?? '/uploads/logo.png'));
        $defaultFaviconUrl = (string) (self::defaults()['site.favicon_url'] ?? '/uploads/logo.png');

        if ($faviconUrl === '' || $faviconUrl === $defaultFaviconUrl) {
            $faviconUrl = $logoUrl;
        }
        if ($faviconUrl === '') {
            $faviconUrl = '/uploads/logo.png';
        }
        if ($logoUrl === '') {
            $logoUrl = '/uploads/logo.png';
        }

        $bannerImage1 = $this->normalizeUrl((string) ($data['site.banner_image_1'] ?? '/uploads/slider-1.png'));
        $bannerImage2 = $this->normalizeUrl((string) ($data['site.banner_image_2'] ?? '/uploads/slider-4.png'));
        if ($bannerImage2 === '') {
            $bannerImage2 = $bannerImage1;
        }

        $site = [
            'name' => (string) ($data['site.name'] ?? 'GenBI Provinsi Jambi'),
            'tagline' => (string) ($data['site.tagline'] ?? 'Bersama GenBI, Energi untuk Negeri'),
            'logo' => $logoUrl,
            'favicon' => $faviconUrl,
            'email' => (string) ($data['site.topbar_email'] ?? 'genbijambibi@gmail.com'),
            'phone' => (string) ($data['site.topbar_phone'] ?? '089627896750'),
            'address' => (string) ($data['site.footer_address'] ?? ''),
            'footerEmail' => (string) ($data['site.footer_email'] ?? ''),
            'footerPhone' => (string) ($data['site.footer_phone'] ?? ''),
            'footerCopyright' => (string) ($data['site.footer_copyright'] ?? ''),
            'footerRecentNewsCount' => max(1, (int) ($data['site.footer_recent_news_count'] ?? 3)),
            'videoResourceUrl' => (string) ($data['site.video_resource_url'] ?? ''),
            'baseUrl' => (string) ($data['site.base_url'] ?? ''),
            'heroSlides' => [
                [
                    'image' => $bannerImage1,
                    'eyebrow' => (string) ($data['site.banner_badge'] ?? ''),
                    'title' => (string) ($data['site.banner_headline'] ?? ''),
                    'caption' => (string) ($data['site.banner_subtitle'] ?? ''),
                ],
                [
                    'image' => $bannerImage2,
                    'eyebrow' => (string) ($data['site.banner_badge'] ?? ''),
                    'title' => (string) ($data['site.banner_headline_alt'] ?? $data['site.banner_headline'] ?? ''),
                    'caption' => (string) ($data['site.banner_subtitle_alt'] ?? $data['site.banner_subtitle'] ?? ''),
                ],
            ],
            'home' => [
                'announcementEyebrow' => (string) ($data['home.announcement_eyebrow'] ?? ''),
                'announcementTitle' => (string) ($data['home.announcement_title'] ?? ''),
                'announcementDescription' => (string) ($data['home.announcement_description'] ?? ''),
                'programEyebrow' => (string) ($data['home.program_eyebrow'] ?? ''),
                'programTitle' => (string) ($data['home.program_title'] ?? ''),
                'programDescription' => (string) ($data['home.program_description'] ?? ''),
                'teamEyebrow' => (string) ($data['home.team_eyebrow'] ?? ''),
                'teamTitle' => (string) ($data['home.team_title'] ?? ''),
                'teamDescription' => (string) ($data['home.team_description'] ?? ''),
                'eventEyebrow' => (string) ($data['home.event_eyebrow'] ?? ''),
                'eventTitle' => (string) ($data['home.event_title'] ?? ''),
                'eventDescription' => (string) ($data['home.event_description'] ?? ''),
                'newsEyebrow' => (string) ($data['home.news_eyebrow'] ?? ''),
                'newsTitle' => (string) ($data['home.news_title'] ?? ''),
            ],
            'sidebar' => [
                'news' => (string) ($data['site.sidebar_heading_news'] ?? ''),
                'recent' => (string) ($data['site.sidebar_heading_recent'] ?? ''),
                'upcoming' => (string) ($data['site.sidebar_heading_upcoming'] ?? ''),
                'past' => (string) ($data['site.sidebar_heading_past'] ?? ''),
                'contact' => (string) ($data['site.sidebar_heading_contact'] ?? ''),
            ],
            'colors' => [
                'primary' => (string) ($data['site.color_primary'] ?? '#114b9a'),
                'primaryHover' => (string) ($data['site.color_primary_hover'] ?? '#0c3572'),
                'primarySoft' => (string) ($data['site.color_primary_soft'] ?? '#eef6ff'),
            ],
            'themes' => [
                'public' => $publicTheme,
                'admin' => $adminTheme,
            ],
        ];

        $data['site'] = $site;
        $data['themes'] = [
            'public' => ThemeRegistry::get($publicTheme),
            'admin' => ThemeRegistry::get($adminTheme),
        ];

        $this->cache = $data;
        $this->writeFileCache($data);
        return $this->cache;
    }

    /** @return array<string, mixed> */
    public function site(): array
    {
        return $this->all()['site'];
    }

    public function themeKey(string $scope): string
    {
        return (string) ($this->all()['themes'][$scope] ?? 'genbi');
    }

    /** @return array<string, mixed> */
    public function theme(string $scope): array
    {
        return ThemeRegistry::get($this->themeKey($scope));
    }

    public function themeInlineCss(string $scope): string
    {
        return ThemeRegistry::inlineCss($this->themeKey($scope));
    }

    /** @return array<string, mixed> */
    public function clientPayload(): array
    {
        return $this->site();
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'site.name' => 'GenBI Provinsi Jambi',
            'site.tagline' => 'Bersama GenBI, Energi untuk Negeri',
            'site.logo_url' => '/uploads/logo.png',
            'site.favicon_url' => '/uploads/logo.png',
            'site.topbar_email' => 'genbijambibi@gmail.com',
            'site.topbar_phone' => '089627896750',
            'site.email_from' => 'genbijambibi@gmail.com',
            'site.email_to' => 'genbijambibi@gmail.com',
            'site.footer_copyright' => 'Copyright © 2026, GenBI Provinsi Jambi',
            'site.footer_address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
            'site.footer_phone' => 'Support: 089627896750',
            'site.footer_email' => 'genbijambibi@gmail.com',
            'site.footer_recent_news_count' => 3,
            'site.banner_headline' => 'Bersama GenBI, tumbuh dan berdampak untuk Jambi.',
            'site.banner_headline_alt' => 'Ruang belajar, berkarya, dan mengabdi bersama.',
            'site.banner_subtitle' => 'Kami adalah komunitas penerima beasiswa Bank Indonesia di Jambi yang bergerak lewat edukasi, pengabdian, kepemimpinan, dan kolaborasi anak muda.',
            'site.banner_subtitle_alt' => 'Dari kampus ke masyarakat, GenBI Jambi hadir membawa semangat literasi kebanksentralan, kepedulian sosial, dan kontribusi nyata untuk daerah.',
            'site.banner_badge' => 'Energi untuk Negeri',
            'site.banner_image_1' => '/uploads/slider-1.png',
            'site.banner_image_2' => '/uploads/slider-4.png',
            'site.sidebar_heading_news' => 'Categories',
            'site.sidebar_heading_recent' => 'Recent Posts',
            'site.sidebar_heading_upcoming' => 'Upcoming Events',
            'site.sidebar_heading_past' => 'Past Events',
            'site.sidebar_heading_contact' => 'Quick Contact',
            'theme.public_key' => 'genbi',
            'theme.admin_key' => 'genbi',
            'theme.custom_override_enabled' => false,
        ];
    }

    private function normalizeThemeKey(string $key): string
    {
        $key = trim(strtolower($key));
        return $key === '' ? 'genbi' : $key;
    }

    /** @return array<string, mixed>|null */
    private function loadFileCache(): ?array
    {
        $file = self::getCacheFile();
        if (!is_file($file)) {
            return null;
        }

        $mtime = filemtime($file);
        if ($mtime === false || (time() - $mtime) > self::CACHE_TTL) {
            return null;
        }

        $data = @include $file;
        return is_array($data) ? $data : null;
    }

    /** @param array<string, mixed> $data */
    private function writeFileCache(array $data): void
    {
        $file = self::getCacheFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $export = var_export($data, true);
        @file_put_contents($file, "<?php\n\nreturn " . $export . ";\n");
    }
}
