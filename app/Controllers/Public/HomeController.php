<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\StaticPageRenderer;
use App\Core\ViewRenderer;
use App\Models\Event;
use App\Models\Feature;
use App\Models\News;
use App\Models\TeamMember;
use App\Services\SeoService;
use App\Services\SiteSettings;
use App\Services\StructuredData;

final class HomeController
{
    public function __construct(
        private StaticPageRenderer $renderer,
        private ?Feature $featureModel = null,
        private ?News $newsModel = null,
        private ?Event $eventModel = null,
        private ?TeamMember $teamModel = null,
        private ?ViewRenderer $viewRenderer = null,
        private ?SiteSettings $siteSettings = null,
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $seo = SeoService::forPage('index.html');
        $meta = SeoService::renderMetaBlock($seo);
        $jsonld = StructuredData::organization();

        if ($this->viewRenderer instanceof ViewRenderer && $this->featureModel instanceof Feature) {
            $html = $this->viewRenderer->renderWithLayout('public/home/index.php', 'layouts/public.php', [
                'site' => $this->siteSettings?->site() ?? $this->siteData(),
                'stats' => $this->statsData(),
                'programs' => $this->featureModel?->homeVisible(12) ?? [],
                'bpiMembers' => $this->bpiMembers(),
                'publicEvents' => $this->publicEvents(),
                'latestNews' => $this->newsModel?->paginate([], 3, 0) ?? [],
                'meta' => $meta,
                'jsonld' => $jsonld,
                'bodyClass' => 'page-home',
                'scripts' => '<script defer src="/assets/js/dist/pages/home.js?v=20260508e"></script>',
            ]);
            $response->html($html);
            return;
        }

        $response->html($this->renderer->render('index.html', ['meta' => $meta]));
    }

    /** @return array<string, mixed> */
    private function siteData(): array
    {
        return [
            'email' => 'genbijambibi@gmail.com',
            'phone' => '089627896750',
            'address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
            'videoResourceUrl' => 'https://www.youtube.com/embed/ashD1p7d29s?si=FFGjlxX7oNn_OWVq',
            'heroSlides' => [
                [
                    'image' => 'https://genbijambi.com/public/uploads/slider-1.png',
                    'eyebrow' => 'GenBI Provinsi Jambi',
                    'title' => 'Bersama GenBI, tumbuh dan berdampak untuk Jambi.',
                    'caption' => 'Kami adalah komunitas penerima beasiswa Bank Indonesia di Jambi yang bergerak lewat edukasi, pengabdian, kepemimpinan, dan kolaborasi anak muda.',
                ],
                [
                    'image' => 'https://genbijambi.com/public/uploads/slider-4.png',
                    'eyebrow' => 'Energi untuk Negeri',
                    'title' => 'Ruang belajar, berkarya, dan mengabdi bersama.',
                    'caption' => 'Dari kampus ke masyarakat, GenBI Jambi hadir membawa semangat literasi kebanksentralan, kepedulian sosial, dan kontribusi nyata untuk daerah.',
                ],
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function statsData(): array
    {
        return [
            ['value' => '2011', 'label' => 'GenBI diresmikan secara nasional'],
            ['value' => '2', 'label' => 'Kampus utama di Jambi'],
            ['value' => '6+', 'label' => 'Program komunitas aktif'],
            ['value' => '100%', 'label' => 'Bergerak lewat kolaborasi'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function bpiMembers(): array
    {
        $members = $this->teamModel?->bpiCore(10) ?? [];
        if ($members !== []) {
            return $members;
        }

        return [
            ['id' => 1, 'name' => 'Ilham Jaya Kusuma', 'role' => 'Ketua Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => 'https://genbijambi.com/public/uploads/team-member-37.jpg'],
            ['id' => 2, 'name' => 'Ananda Marisa Pertiwi', 'role' => 'Sekretaris Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => 'https://genbijambi.com/public/uploads/team-member-38.jpg'],
            ['id' => 3, 'name' => 'Depi Susanti', 'role' => 'Bendahara Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => 'https://genbijambi.com/public/uploads/team-member-39.jpg'],
            ['id' => 4, 'name' => 'Raihan Aulia Aridestama', 'role' => 'Koordinator Tim Media GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => 'https://genbijambi.com/public/uploads/team-member-40.jpg'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function publicEvents(): array
    {
        $events = $this->eventModel?->paginate([], 4, 0) ?? [];
        if ($events !== []) {
            return $events;
        }

        return [
            ['id' => 1, 'title' => 'GenBI PEKA', 'date' => '23 Januari 2025', 'type' => 'Sosial', 'icon' => 'heart', 'description' => 'Gerakan kepedulian anggota GenBI Jambi untuk hadir lebih dekat dengan masyarakat dan membangun empati melalui aksi nyata.'],
            ['id' => 2, 'title' => 'GenBI Ceria', 'date' => '21 Desember 2024', 'type' => 'Komunitas', 'icon' => 'users', 'description' => 'Agenda kebersamaan yang merawat solidaritas anggota, membuka ruang interaksi, dan menjaga semangat organisasi tetap hidup.'],
            ['id' => 3, 'title' => 'GenBI for UMKM', 'date' => '20 Desember 2024', 'type' => 'Literasi UMKM', 'icon' => 'chart', 'description' => 'Pendampingan sederhana untuk membantu pelaku usaha memahami pencatatan, promosi digital, dan peluang pembayaran non-tunai.'],
            ['id' => 4, 'title' => 'PTBI 2024', 'date' => '29 November 2024', 'type' => 'Kebanksentralan', 'icon' => 'calendar', 'description' => 'Kesempatan anggota GenBI Jambi memperluas wawasan tentang arah kebijakan Bank Indonesia dan dinamika ekonomi terkini.'],
        ];
    }
}
