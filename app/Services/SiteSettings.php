<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\ThemeRegistry;
use App\Models\Setting;

final class SiteSettings
{
    private const CACHE_FILE = __DIR__ . '/../../storage/cache/site_settings.php';
    private const CACHE_TTL = 300;

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    public function __construct(private ?Setting $settings = null)
    {
    }

    public static function clearCache(): void
    {
        $file = self::CACHE_FILE;
        if (is_file($file)) {
            @unlink($file);
        }
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

        $stored = $this->settings?->all() ?? [];
        $data = self::defaults();

        foreach ($stored as $key => $value) {
            $data[$key] = $value;
        }

        $publicTheme = $this->normalizeThemeKey((string) ($data['theme.public_key'] ?? 'genbi'));
        $adminTheme = $this->normalizeThemeKey((string) ($data['theme.admin_key'] ?? 'genbi'));

        $logoUrl = (string) ($data['site.logo_url'] ?? '');
        $faviconUrl = (string) ($data['site.favicon_url'] ?? '');
        $defaultFaviconUrl = (string) (self::defaults()['site.favicon_url'] ?? '');

        if ($faviconUrl === '' || $faviconUrl === $defaultFaviconUrl) {
            $faviconUrl = $logoUrl;
        }

        $bannerImage1 = (string) ($data['site.banner_image_1'] ?? '');
        $bannerImage2 = (string) ($data['site.banner_image_2'] ?? '');
        if ($bannerImage2 === '') {
            $bannerImage2 = $bannerImage1;
        }

        $site = [
            'name' => (string) ($data['site.name'] ?? ''),
            'tagline' => (string) ($data['site.tagline'] ?? ''),
            'logo' => $logoUrl,
            'favicon' => $faviconUrl,
            'email' => (string) ($data['site.topbar_email'] ?? ''),
            'phone' => (string) ($data['site.topbar_phone'] ?? ''),
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
        $key = (string) (($this->all()['theme.' . $scope . '_key'] ?? 'genbi'));
        return $this->normalizeThemeKey($key);
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
            'site.logo_url' => 'https://genbijambi.com/public/uploads/logo.png',
            'site.favicon_url' => 'https://genbijambi.com/public/uploads/logo.png',
            'site.topbar_email' => 'genbijambibi@gmail.com',
            'site.topbar_phone' => '085669152702',
            'site.email_from' => 'genbijambibi@gmail.com',
            'site.email_to' => 'genbijambibi@gmail.com',
            'site.footer_copyright' => 'Copyright © 2026, GenBI Provinsi Jambi',
            'site.footer_address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
            'site.footer_phone' => 'Support: 085669152702',
            'site.footer_email' => 'genbijambibi@gmail.com',
            'site.footer_recent_news_count' => 3,
            'site.banner_headline' => 'Bersama GenBI, tumbuh dan berdampak untuk Jambi.',
            'site.banner_headline_alt' => 'Ruang belajar, berkarya, dan mengabdi bersama.',
            'site.banner_subtitle' => 'Kami adalah komunitas penerima beasiswa Bank Indonesia di Jambi yang bergerak lewat edukasi, pengabdian, kepemimpinan, dan kolaborasi anak muda.',
            'site.banner_subtitle_alt' => 'Dari kampus ke masyarakat, GenBI Jambi hadir membawa semangat literasi kebanksentralan, kepedulian sosial, dan kontribusi nyata untuk daerah.',
            'site.banner_badge' => 'Energi untuk Negeri',
            'site.banner_image_1' => 'https://genbijambi.com/public/uploads/slider-1.png',
            'site.banner_image_2' => 'https://genbijambi.com/public/uploads/slider-4.png',
            'site.sidebar_heading_news' => 'Categories',
            'site.sidebar_heading_recent' => 'Recent Posts',
            'site.sidebar_heading_upcoming' => 'Upcoming Events',
            'site.sidebar_heading_past' => 'Past Events',
            'site.sidebar_heading_contact' => 'Quick Contact',
            'site.color_primary' => '#114b9a',
            'site.color_primary_hover' => '#0c3572',
            'site.color_primary_soft' => '#eef6ff',
            'site.video_resource_url' => 'https://www.youtube.com/embed/ashD1p7d29s?si=FFGjlxX7oNn_OWVq',
            'site.base_url' => 'https://genbijambi.com',
            'home.announcement_eyebrow' => 'Pengumuman',
            'home.announcement_title' => 'Info penting untuk anggota dan publik.',
            'home.announcement_description' => 'Pembaruan resmi, agenda penting, dan kabar prioritas GenBI Jambi ditampilkan dalam format ringkas agar mudah dipantau.',
            'home.program_eyebrow' => 'Program utama',
            'home.program_title' => 'Program yang dekat dengan anggota dan masyarakat.',
            'home.program_description' => 'Setiap program dirancang sebagai ruang belajar, ruang kolaborasi, dan ruang kontribusi agar anggota GenBI Jambi tumbuh sekaligus memberi manfaat.',
            'home.team_eyebrow' => 'GenBI Provinsi Jambi',
            'home.team_title' => 'Wajah pengurus yang menjaga arah gerak organisasi.',
            'home.team_description' => 'Badan Pengurus Inti menghubungkan ide, anggota, dan agenda kerja agar GenBI Jambi tetap solid, aktif, dan relevan bagi lingkungan sekitar.',
            'home.event_eyebrow' => 'Agenda utama',
            'home.event_title' => 'Kegiatan yang lahir dari kebutuhan sekitar.',
            'home.event_description' => 'Agenda GenBI Jambi tidak berhenti di seremoni. Setiap kegiatan menjadi kesempatan untuk belajar, melayani, dan membangun jejaring kebaikan.',
            'home.news_eyebrow' => 'Latest news',
            'home.news_title' => 'Berita terbaru',
            'theme.public_key' => 'genbi',
            'theme.admin_key' => 'genbi',
            'theme.custom_override_enabled' => false,
        ];
    }

    /** @return array<string, mixed>|null */
    private function loadFileCache(): ?array
    {
        $file = self::CACHE_FILE;
        if (!is_file($file) || (time() - filemtime($file)) > self::CACHE_TTL) {
            return null;
        }
        $data = @include $file;
        return is_array($data) ? $data : null;
    }

    /** @param array<string, mixed> $data */
    private function writeFileCache(array $data): void
    {
        $dir = dirname(self::CACHE_FILE);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $content = '<?php return ' . var_export($data, true) . ';' . "\n";
        @file_put_contents(self::CACHE_FILE, $content, LOCK_EX);
    }

    private function normalizeThemeKey(string $key): string
    {
        return in_array($key, ThemeRegistry::keys(), true) ? $key : 'genbi';
    }
}
