<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Event;
use App\Models\TeamMember;
use App\Models\Feature;
use App\Services\SiteSettings;

class HomeController extends Controller
{
    public function index(SiteSettings $siteSettings)
    {
        $payload = collect($siteSettings->site() ?? $this->siteData());
        
        $bpiMembers = TeamMember::bpiCore()->get()->map(function($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => current(array_filter([$member->jabatan_komsat, $member->jabatan_wilayah, 'Anggota'])),
                'komsat' => $member->komsat,
                'commission' => $member->divisi_wilayah ?? $member->divisi_komsat,
                'photo' => \App\Services\ImageResolver::resolve($member->photo, ''),
            ];
        });
        if ($bpiMembers->isEmpty()) {
            $bpiMembers = $this->fallbackBpiMembers();
        }

        $publicEvents = Event::published()->latestEvent()->take(4)->get()->map(function($event) {
            $images = [];
            if ($event->event_banner) $images[] = \App\Services\ImageResolver::resolve($event->event_banner, '');
            return [
                'id' => $event->event_id,
                'title' => $event->event_title,
                'slug' => $event->slug,
                'date' => $event->event_start_date,
                'type' => 'Agenda Komunitas',
                'description' => $event->event_desc,
                'images' => $images,
                'icon' => 'calendar',
            ];
        });
        if ($publicEvents->isEmpty()) {
            $publicEvents = $this->fallbackPublicEvents();
        }

        $announcements = News::published()->whereHas('category', function($q) {
            $q->where('category_name', 'Pengumuman');
        })->latestNews()->take(8)->get()->map(function($news) {
            return $this->mapNews($news);
        });

        $latestNews = News::published()->latestNews()->take(3)->get()->map(function($news) {
            return $this->mapNews($news);
        });

        $programs = Feature::with('images')->homeVisible(12)->map(function($feature) {
            $images = $feature->images->map(function($img) {
                return ['url' => \App\Services\ImageResolver::resolve($img->image_path, '')];
            })->toArray();
            return [
                'id' => $feature->id,
                'name' => $feature->name,
                'title' => $feature->title ?: $feature->name,
                'description' => $feature->description ?: $feature->content,
                'focus' => $feature->focus,
                'icon_key' => $feature->icon_key ?: $feature->icon,
                'icon_image' => \App\Services\ImageResolver::resolve($feature->icon_image, ''),
                'images' => $images,
            ];
        });

        return view('public.home.index', [
            'site' => $payload,
            'homeContent' => $payload['home'] ?? [],
            'stats' => $this->statsData(),
            'programs' => $programs,
            'bpiMembers' => $bpiMembers,
            'publicEvents' => $publicEvents,
            'announcements' => $announcements,
            'latestNews' => $latestNews,
            'scripts' => '<script defer src="/assets/js/dist/pages/home.js"></script>',
        ]);
    }

    private function mapNews($news)
    {
        $title = $news->news_title;
        return [
            'id' => $news->news_id,
            'slug' => $news->slug,
            'title' => $title,
            'excerpt' => $news->news_content_short ?: substr(strip_tags($news->news_content), 0, 150),
            'date' => $news->published_at ?: ($news->news_date ?: $news->created_at),
            'image' => \App\Services\ImageResolver::resolve($news->photo ?: $news->banner, '/uploads/slider-1.png'),
            'category' => $news->category ? $news->category->category_name : 'Berita GenBI',
        ];
    }

    private function siteData(): array
    {
        return [
            'email' => 'genbijambibi@gmail.com',
            'phone' => '089627896750',
            'address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
            'videoResourceUrl' => 'https://www.youtube.com/embed/ashD1p7d29s?si=FFGjlxX7oNn_OWVq',
            'heroSlides' => [
                [
                    'image' => '/uploads/slider-1.png',
                    'eyebrow' => 'GenBI Provinsi Jambi',
                    'title' => 'Bersama GenBI, tumbuh dan berdampak untuk Jambi.',
                    'caption' => 'Kami adalah komunitas penerima beasiswa Bank Indonesia di Jambi yang bergerak lewat edukasi, pengabdian, kepemimpinan, dan kolaborasi anak muda.',
                ],
                [
                    'image' => '/uploads/slider-4.png',
                    'eyebrow' => 'Energi untuk Negeri',
                    'title' => 'Ruang belajar, berkarya, dan mengabdi bersama.',
                    'caption' => 'Dari kampus ke masyarakat, GenBI Jambi hadir membawa semangat literasi kebanksentralan, kepedulian sosial, dan kontribusi nyata untuk daerah.',
                ],
            ],
        ];
    }

    private function statsData(): array
    {
        return [
            ['value' => '2011', 'label' => 'GenBI diresmikan secara nasional'],
            ['value' => '2', 'label' => 'Kampus utama di Jambi'],
            ['value' => '6+', 'label' => 'Program komunitas aktif'],
            ['value' => '100%', 'label' => 'Bergerak lewat kolaborasi'],
        ];
    }

    private function fallbackBpiMembers()
    {
        return collect([
            (object)['id' => 1, 'name' => 'Ilham Jaya Kusuma', 'role' => 'Ketua Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => '/uploads/team-member-37.jpg'],
            (object)['id' => 2, 'name' => 'Ananda Marisa Pertiwi', 'role' => 'Sekretaris Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => '/uploads/team-member-38.jpg'],
            (object)['id' => 3, 'name' => 'Depi Susanti', 'role' => 'Bendahara Umum GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => '/uploads/team-member-39.jpg'],
            (object)['id' => 4, 'name' => 'Raihan Aulia Aridestama', 'role' => 'Koordinator Tim Media GenBI Jambi', 'commission' => 'BPI GenBI Provinsi Jambi', 'photo' => '/uploads/team-member-40.jpg'],
        ]);
    }

    private function fallbackPublicEvents()
    {
        return collect([
            (object)['id' => 1, 'title' => 'GenBI PEKA', 'date' => '23 Januari 2025', 'type' => 'Sosial', 'icon' => 'heart', 'description' => 'Gerakan kepedulian anggota GenBI Jambi untuk hadir lebih dekat dengan masyarakat dan membangun empati melalui aksi nyata.'],
            (object)['id' => 2, 'title' => 'GenBI Ceria', 'date' => '21 Desember 2024', 'type' => 'Komunitas', 'icon' => 'users', 'description' => 'Agenda kebersamaan yang merawat solidaritas anggota, membuka ruang interaksi, dan menjaga semangat organisasi tetap hidup.'],
            (object)['id' => 3, 'title' => 'GenBI for UMKM', 'date' => '20 Desember 2024', 'type' => 'Literasi UMKM', 'icon' => 'chart', 'description' => 'Pendampingan sederhana untuk membantu pelaku usaha memahami pencatatan, promosi digital, dan peluang pembayaran non-tunai.'],
            (object)['id' => 4, 'title' => 'PTBI 2024', 'date' => '29 November 2024', 'type' => 'Kebanksentralan', 'icon' => 'calendar', 'description' => 'Kesempatan anggota GenBI Jambi memperluas wawasan tentang arah kebijakan Bank Indonesia dan dinamika ekonomi terkini.'],
        ]);
    }
}
