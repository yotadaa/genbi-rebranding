<?php
$site = $site ?? [];
$stats = $stats ?? [];
$programs = $programs ?? [];
$bpiMembers = $bpiMembers ?? [];
$publicEvents = $publicEvents ?? [];
$latestNews = $latestNews ?? [];
$heroSlides = $site['heroSlides'] ?? [];
$initialSlide = $heroSlides[0] ?? null;

$uploadExists = static function (string $url): bool {
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return false;
    }

    if (str_starts_with($path, '/public/uploads/')) {
        $path = substr($path, 7);
    }

    if (!str_starts_with($path, '/uploads/')) {
        return true;
    }

    return is_file(dirname(__DIR__, 4) . '/public' . $path);
};

$fallbackHeroImage = static function (int $index) use ($heroSlides): string {
    $heroCount = max(count($heroSlides), 1);
    return (string) ($heroSlides[$index % $heroCount]['image'] ?? $heroSlides[0]['image'] ?? 'https://genbijambi.com/public/uploads/slider-1.png');
};

$eventIconMarkup = static function (string $type): string {
    return match ($type) {
        'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.49-2.01-4.5-4.5-4.5A4.48 4.48 0 0 0 12 6.36a4.48 4.48 0 0 0-4.5-2.61C5.01 3.75 3 5.76 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75v-9"/></svg>',
        'bank' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M5.25 10.5h13.5M6.75 10.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5m3.5-7.5v7.5M4.5 18h15"/></svg>',
        'academic' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25 12 4.5l8.25 3.75L12 12 3.75 8.25Zm3 2.25v4.25c0 1.66 2.35 3 5.25 3s5.25-1.34 5.25-3V10.5"/></svg>',
        'spark' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.76 5.45h5.74l-4.64 3.37 1.77 5.45L12 13.9l-4.63 3.37 1.77-5.45L4.5 8.45h5.74L12 3Z"/></svg>',
        default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',
    };
};
?>
<div class="home-page-shell">
  <section class="hero-bg hero-section-compact home-hero-section relative overflow-hidden text-white">
    <div id="hero-slider" class="absolute inset-0"<?= $heroSlides !== [] ? ' data-ssr="true"' : '' ?>>
      <?php foreach ($heroSlides as $index => $slide): ?>
        <img src="<?= $e((string) ($slide['image'] ?? '')) ?>" alt="<?= $e((string) ($slide['caption'] ?? 'Hero slide')) ?>" class="hero-image hero-bg-image <?= $index === 0 ? 'is-active' : '' ?>" />
      <?php endforeach; ?>
    </div>
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(12,53,114,0.92),rgba(12,53,114,0.70)_42%,rgba(12,53,114,0.30)_70%,rgba(12,53,114,0.18))]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.16),transparent_25%),linear-gradient(180deg,rgba(0,0,0,0.16),rgba(0,0,0,0.18))]"></div>
    <div class="site-container hero-inner-compact relative z-10 flex items-center">
      <div class="fade-up max-w-4xl">
        <span id="hero-eyebrow" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/12 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-blue-50 backdrop-blur"><?= $initialSlide ? '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 3.7 8.4 8.1 4 9.5l4.4 1.4 1.4 4.4 1.4-4.4 4.4-1.4-4.4-1.4-1.4-4.4Zm7.4 6.7-.9 2.8-2.8.9 2.8.9.9 2.8.9-2.8 2.8-.9-2.8-.9-.9-2.8Z"/></svg> ' . htmlspecialchars((string) ($initialSlide['eyebrow'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?></span>
        <h1 id="hero-title" class="serif hero-title-compact mt-6 max-w-5xl font-semibold"><?= $e((string) ($initialSlide['title'] ?? '')) ?></h1>
        <p id="hero-caption" class="mt-5 max-w-2xl text-base leading-8 text-blue-50/85 md:text-lg"><?= $e((string) ($initialSlide['caption'] ?? '')) ?></p>
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
          <a data-transition href="<?= $url('about') ?>" class="btn btn-light">Kenali GenBI</a>
          <a data-transition href="<?= $url('news') ?>" class="btn btn-ghost-light">Baca Berita Terbaru</a>
          <button id="open-video" class="btn btn-ghost-light">Lihat Video</button>
        </div>
        <div class="mt-8 flex gap-2" id="hero-dots"<?= $heroSlides !== [] ? ' data-ssr="true"' : '' ?>>
          <?php foreach ($heroSlides as $index => $slide): ?>
            <button class="h-3 w-3 rounded-full transition hover:bg-white p-2 box-content <?= $index === 0 ? 'bg-white' : 'bg-white/40' ?>" aria-label="Slide <?= $index + 1 ?>" data-slide="<?= $index ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="home-section-surface py-14">
    <div class="site-container grid gap-6 border-y border-neutral-900/10 py-8 md:grid-cols-4" id="stats-row"<?= $stats !== [] ? ' data-ssr="true"' : '' ?>>
      <?php foreach ($stats as $item): ?>
        <div class="fade-up">
          <p class="serif text-4xl font-semibold tracking-tight text-neutral-950"><?= $e((string) ($item['value'] ?? '')) ?></p>
          <p class="mt-2 text-sm leading-6 text-neutral-600"><?= $e((string) ($item['label'] ?? '')) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="home-section-surface py-16 md:py-24">
    <div class="site-container">
      <div class="home-section-intro fade-up">
        <p class="eyebrow">Program utama</p>
        <h2 class="section-title mt-4">Program yang dekat dengan anggota dan masyarakat.</h2>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Setiap program dirancang sebagai ruang belajar, ruang kolaborasi, dan ruang kontribusi agar anggota GenBI Jambi tumbuh sekaligus memberi manfaat.</p>
        <a data-transition href="<?= $url('about') ?>" class="btn btn-dark mt-7">Lihat profil lengkap</a>
      </div>
      <div class="carousel-shell fade-up" data-carousel>
        <div class="carousel-control-row">
          <button class="carousel-nav" data-carousel-prev aria-label="Program sebelumnya">‹</button>
          <button class="carousel-nav" data-carousel-next aria-label="Program berikutnya">›</button>
        </div>
        <div class="horizontal-carousel program-carousel" id="program-list" aria-label="Daftar program utama" data-ssr="true">
          <?php if ($programs !== []): ?>
            <?php foreach ($programs as $index => $program): ?>
              <?php
              $images = $program['images'] ?? [];
              $firstImage = $images[0]['url'] ?? 'https://genbijambi.com/public/uploads/slider-1.png';
              ?>
              <article
                class="editorial-slide-card program-slide-card"
                role="group"
                aria-roledescription="slide"
                aria-label="Program <?= $index + 1 ?> dari <?= count($programs) ?>"
                data-program-slides="<?= $e(json_encode(array_values(array_map(static fn(array $image): string => (string) ($image['url'] ?? ''), $images)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]') ?>"
                style="--program-bg-image: url('<?= $e($firstImage) ?>');"
              >
                <span class="slide-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span class="program-icon mx-auto" data-program-icon="<?= $e($program['icon_key'] ?? 'sparkles') ?>"></span>
                <p class="slide-kicker"><?= $e($program['title'] ?? '') ?></p>
                <h3><?= $e($program['name'] ?? '') ?></h3>
                <p><?= $e($program['description'] ?? '') ?></p>
                <?php if (!empty($program['focus'])): ?>
                  <span class="blue-badge mx-auto mt-5"><?= $e($program['focus']) ?></span>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="home-section-surface py-16 md:py-24">
    <div class="site-container">
      <div class="home-section-intro fade-up">
        <p class="eyebrow">GenBI Provinsi Jambi</p>
        <h2 class="section-title mt-4">Wajah pengurus yang menjaga arah gerak organisasi.</h2>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Badan Pengurus Inti menghubungkan ide, anggota, dan agenda kerja agar GenBI Jambi tetap solid, aktif, dan relevan bagi lingkungan sekitar.</p>
        <a data-transition href="<?= $url('team') ?>" class="btn btn-secondary mt-7">Lihat direktori anggota</a>
      </div>
      <div class="carousel-shell fade-up" data-carousel>
        <div class="carousel-control-row">
          <button class="carousel-nav" data-carousel-prev aria-label="Anggota sebelumnya">‹</button>
          <button class="carousel-nav" data-carousel-next aria-label="Anggota berikutnya">›</button>
        </div>
        <div class="horizontal-carousel bpi-carousel" id="bpi-list" aria-label="Daftar GenBI Provinsi Jambi"<?= $bpiMembers !== [] ? ' data-ssr="true"' : '' ?>>
          <?php foreach ($bpiMembers as $index => $member): ?>
            <?php $photo = (string) ($member['photo'] ?? $member['image'] ?? ''); ?>
            <article class="editorial-slide-card bpi-slide-card" role="group" aria-roledescription="slide" aria-label="Anggota BPI <?= $index + 1 ?> dari <?= count($bpiMembers) ?>">
              <figure class="bpi-slide-photo">
                <span class="member-photo-skeleton" aria-hidden="true"></span>
                <img src="<?= $e($photo) ?>" alt="Foto <?= $e((string) ($member['name'] ?? 'Anggota BPI')) ?>" loading="lazy" onload="this.previousElementSibling.classList.add('is-hidden')" onerror="this.classList.add('is-hidden'); this.previousElementSibling.classList.remove('is-hidden')" />
              </figure>
              <div class="bpi-slide-content">
                <span class="bpi-number mx-auto"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <h3><?= $e((string) ($member['name'] ?? '')) ?></h3>
                <p><?= $e((string) ($member['role'] ?? '')) ?></p>
                <span class="blue-badge mx-auto mt-5"><?= $e((string) ($member['commission'] ?? $member['komsat'] ?? '')) ?></span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="home-section-surface py-16 md:py-24">
    <div class="site-container">
      <div class="home-section-intro fade-up">
        <p class="eyebrow">Agenda utama</p>
        <h2 class="section-title mt-4">Kegiatan yang lahir dari kebutuhan sekitar.</h2>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Agenda GenBI Jambi tidak berhenti di seremoni. Setiap kegiatan menjadi kesempatan untuk belajar, melayani, dan membangun jejaring kebaikan.</p>
        <a data-transition href="<?= $url('event') ?>" class="btn btn-dark mt-7">Lihat semua agenda</a>
      </div>
      <div class="carousel-shell fade-up" data-carousel>
        <div class="carousel-control-row">
          <button class="carousel-nav" data-carousel-prev aria-label="Agenda sebelumnya">‹</button>
          <button class="carousel-nav" data-carousel-next aria-label="Agenda berikutnya">›</button>
        </div>
        <div class="horizontal-carousel event-carousel" id="home-events" aria-label="Daftar agenda utama"<?= $publicEvents !== [] ? ' data-ssr="true"' : '' ?>>
          <?php foreach ($publicEvents as $index => $event): ?>
            <?php
            $slides = [];
            if (!empty($event['images']) && is_array($event['images'])) {
                $slides = array_values(array_filter(
                    array_map(static fn($image): string => (string) $image, $event['images']),
                    static fn(string $image): bool => $image !== '' && $uploadExists($image)
                ));
            }
            if ($slides === []) {
                $slides = [$fallbackHeroImage($index)];
            }
            $eventType = (string) ($event['type'] ?? $event['category'] ?? 'Agenda Komunitas');
            $eventDate = (string) ($event['date'] ?? $event['start_date'] ?? $event['start'] ?? '-');
            $eventDescription = (string) ($event['description'] ?? $event['excerpt'] ?? '');
            $eventIcon = (string) ($event['icon'] ?? 'calendar');
            ?>
            <article class="editorial-slide-card program-slide-card agenda-slide-card" role="group" aria-roledescription="slide" aria-label="Agenda <?= $index + 1 ?> dari <?= count($publicEvents) ?>" data-program-slides='<?= $e(json_encode($slides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]') ?>' style="--program-bg-image: url('<?= $e($slides[0]) ?>');">
              <span class="slide-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="program-icon mx-auto"><?= $eventIconMarkup($eventIcon) ?></span>
              <p class="slide-kicker"><?= $e($eventType) ?></p>
              <h3><?= $e((string) ($event['title'] ?? '')) ?></h3>
              <p><?= $e($eventDescription) ?></p>
              <span class="blue-badge mx-auto mt-5"><?= $e($eventDate) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="home-section-surface py-16 md:py-24">
    <div class="article-container">
      <div class="fade-up mb-9 flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
          <p class="eyebrow">Latest news</p>
          <h2 class="section-title mt-4">Berita terbaru</h2>
        </div>
        <a data-transition href="<?= $url('news') ?>" class="btn btn-secondary w-fit">Lihat semua berita</a>
      </div>
      <div class="fade-up" id="home-news"<?= $latestNews !== [] ? ' data-ssr="true"' : '' ?>>
        <?php if ($latestNews !== []): ?>
          <?php foreach ($latestNews as $item): ?>
            <a data-transition href="<?= $e('/news/' . ($item['slug'] ?? '')) ?>" class="home-news-card">
              <figure class="home-news-media"><img src="<?= $e((string) ($item['image'] ?? '')) ?>" alt="<?= $e((string) ($item['title'] ?? 'Berita GenBI')) ?>" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" /></figure>
              <div class="home-news-copy">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
                  <span class="text-blue-800"><?= $e((string) ($item['category'] ?? 'Berita GenBI')) ?></span>
                  <span><?= $e(!empty($item['date']) ? date('d M Y', strtotime((string) $item['date'])) : '-') ?></span>
                </div>
                <h3 class="serif text-2xl font-semibold leading-tight tracking-tight text-neutral-950 md:text-3xl"><?= $e((string) ($item['title'] ?? 'Berita GenBI')) ?></h3>
                <p class="text-base leading-7 text-neutral-600"><?= $e((string) ($item['excerpt'] ?? '')) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="home-section-surface py-14 md:py-20">
    <div class="site-container contact-prefooter fade-up" id="home-contact-card"<?= !empty($site) ? ' data-ssr="true"' : '' ?>>
      <div>
        <p class="eyebrow">Contact us</p>
        <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950 md:text-5xl">Mau berkolaborasi dengan GenBI Jambi?</h2>
        <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Hubungi kami untuk informasi kegiatan, publikasi, kolaborasi, dan agenda komunitas.</p>
      </div>
      <div class="contact-prefooter-card">
        <p class="contact-label">Address</p>
        <p><?= $e((string) ($site['address'] ?? '')) ?></p>
        <div class="mt-5 grid gap-2 text-sm">
          <a href="mailto:<?= $e((string) ($site['email'] ?? '')) ?>"><?= $e((string) ($site['email'] ?? '')) ?></a>
          <a href="tel:<?= $e((string) ($site['phone'] ?? '')) ?>"><?= $e((string) ($site['phone'] ?? '')) ?></a>
        </div>
        <a data-transition href="<?= $url('contact') ?>" class="btn btn-primary mt-6 w-fit">Contact Us</a>
      </div>
    </div>
  </section>
</div>

<div id="video-modal" class="fixed inset-0 z-[80] hidden bg-neutral-950/70 p-4 backdrop-blur-sm">
  <div class="mx-auto mt-10 max-w-4xl rounded-[1.75rem] bg-cream p-4 shadow-2xl modal-panel md:p-6">
    <div class="flex items-start justify-between gap-5 px-1 pb-4">
      <div>
        <p class="eyebrow">Video</p>
        <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950 md:text-3xl">Video profil GenBI Jambi</h3>
      </div>
      <button id="close-video" class="btn-icon" aria-label="Tutup video">×</button>
    </div>
    <div class="video-frame">
      <iframe id="profile-video" width="560" height="315" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>
  </div>
</div>
