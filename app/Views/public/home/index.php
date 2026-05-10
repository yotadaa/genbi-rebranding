<?php
$programs = $programs ?? [];
$latestNews = $latestNews ?? [];
?>
<section class="hero-bg hero-section-compact relative overflow-hidden bg-blue-950 text-white">
  <div id="hero-slider" class="absolute inset-0"></div>
  <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(12,53,114,0.92),rgba(12,53,114,0.70)_42%,rgba(12,53,114,0.30)_70%,rgba(12,53,114,0.18))]"></div>
  <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.16),transparent_25%),linear-gradient(180deg,rgba(0,0,0,0.16),rgba(0,0,0,0.18))]"></div>
  <div class="site-container hero-inner-compact relative z-10 flex items-center">
    <div class="fade-up max-w-4xl">
      <span id="hero-eyebrow" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/12 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-blue-50 backdrop-blur"></span>
      <h1 id="hero-title" class="serif hero-title-compact mt-6 max-w-5xl font-semibold"></h1>
      <p id="hero-caption" class="mt-5 max-w-2xl text-base leading-8 text-blue-50/85 md:text-lg"></p>
      <div class="mt-7 flex flex-col gap-3 sm:flex-row">
        <a data-transition href="/about" class="btn btn-light">Kenali GenBI</a>
        <a data-transition href="/news" class="btn btn-ghost-light">Baca Berita Terbaru</a>
        <button id="open-video" class="btn btn-ghost-light">Lihat Video</button>
      </div>
      <div class="mt-8 flex gap-2" id="hero-dots"></div>
    </div>
  </div>
</section>

<section class="bg-cream py-14">
  <div class="site-container grid gap-6 border-y border-neutral-900/10 py-8 md:grid-cols-4" id="stats-row"></div>
</section>

<section class="bg-[var(--surface-soft)] py-16 md:py-24">
  <div class="site-container">
    <div class="home-section-intro fade-up">
      <p class="eyebrow">Program utama</p>
      <h2 class="section-title mt-4">Program yang dekat dengan anggota dan masyarakat.</h2>
      <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Setiap program dirancang sebagai ruang belajar, ruang kolaborasi, dan ruang kontribusi agar anggota GenBI Jambi tumbuh sekaligus memberi manfaat.</p>
      <a data-transition href="/about" class="btn btn-dark mt-7">Lihat profil lengkap</a>
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

<section class="bg-cream py-16 md:py-24">
  <div class="site-container">
    <div class="home-section-intro fade-up">
      <p class="eyebrow">GenBI Provinsi Jambi</p>
      <h2 class="section-title mt-4">Wajah pengurus yang menjaga arah gerak organisasi.</h2>
      <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Badan Pengurus Inti menghubungkan ide, anggota, dan agenda kerja agar GenBI Jambi tetap solid, aktif, dan relevan bagi lingkungan sekitar.</p>
      <a data-transition href="/team" class="btn btn-secondary mt-7">Lihat direktori anggota</a>
    </div>
    <div class="carousel-shell fade-up" data-carousel>
      <div class="carousel-control-row">
        <button class="carousel-nav" data-carousel-prev aria-label="Anggota sebelumnya">‹</button>
        <button class="carousel-nav" data-carousel-next aria-label="Anggota berikutnya">›</button>
      </div>
      <div class="horizontal-carousel bpi-carousel" id="bpi-list" aria-label="Daftar GenBI Provinsi Jambi"></div>
    </div>
  </div>
</section>

<section class="bg-[var(--surface-soft)] py-16 md:py-24">
  <div class="site-container">
    <div class="home-section-intro fade-up">
      <p class="eyebrow">Agenda utama</p>
      <h2 class="section-title mt-4">Kegiatan yang lahir dari kebutuhan sekitar.</h2>
      <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-neutral-600">Agenda GenBI Jambi tidak berhenti di seremoni. Setiap kegiatan menjadi kesempatan untuk belajar, melayani, dan membangun jejaring kebaikan.</p>
      <a data-transition href="/event" class="btn btn-secondary mt-7">Lihat semua event</a>
    </div>
    <div class="carousel-shell fade-up" data-carousel>
      <div class="carousel-control-row">
        <button class="carousel-nav" data-carousel-prev aria-label="Agenda sebelumnya">‹</button>
        <button class="carousel-nav" data-carousel-next aria-label="Agenda berikutnya">›</button>
      </div>
      <div class="horizontal-carousel event-carousel" id="home-events" aria-label="Daftar agenda utama"></div>
    </div>
  </div>
</section>

<section class="bg-cream py-16 md:py-24">
  <div class="article-container">
    <div class="fade-up mb-9 flex flex-col justify-between gap-5 md:flex-row md:items-end">
      <div>
        <p class="eyebrow">Latest news</p>
        <h2 class="section-title mt-4">Berita terbaru</h2>
      </div>
      <a data-transition href="/news" class="btn btn-secondary w-fit">Lihat semua berita</a>
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

<section class="bg-[var(--surface-soft)] py-14 md:py-20">
  <div class="site-container contact-prefooter fade-up" id="home-contact-card"></div>
</section>

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
