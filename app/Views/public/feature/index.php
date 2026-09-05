<?php
$programs = is_array($programs ?? null) ? $programs : [];
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container fade-up">
    <p class="eyebrow">Program Utama</p>
    <h1 class="page-title mt-5">Program yang tumbuh bersama anggota dan masyarakat.</h1>
    <p class="lead mt-7">Rangkaian program GenBI Provinsi Jambi dalam edukasi, pengabdian, kepemimpinan, dan kolaborasi.</p>
  </div>
</section>

<section class="home-section-surface bg-cream py-16 md:py-24">
  <div class="site-container">
    <?php if ($programs !== []): ?>
      <div class="feature-page-grid" id="feature-program-list" data-ssr="true" aria-label="Daftar Program Utama">
        <?php foreach ($programs as $index => $program): ?>
          <?php
          $imageRows = is_array($program['images'] ?? null) ? $program['images'] : [];
          $images = array_values(array_filter(array_map(static function (mixed $image): string {
              if (is_array($image)) {
                  return trim((string) ($image['url'] ?? ''));
              }
              return is_string($image) ? trim($image) : '';
          }, $imageRows)));
          if ($images === []) {
              $images = ['/uploads/slider-1.png'];
          }
          $slidesJson = json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
          $title = trim((string) ($program['title'] ?? '')) ?: trim((string) ($program['name'] ?? ''));
          $name = trim((string) ($program['name'] ?? '')) ?: $title;
          ?>
          <article class="editorial-slide-card program-slide-card feature-page-card" data-program-slides="<?= $e($slidesJson) ?>" style="--program-bg-image: url('<?= $e($images[0]) ?>');">
            <span class="slide-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <span class="program-icon mx-auto" data-program-icon="<?= $e((string) ($program['icon_key'] ?? 'sparkles')) ?>"></span>
            <?php if ($title !== ''): ?><p class="slide-kicker"><?= $e($title) ?></p><?php endif; ?>
            <h2><?= $e($name) ?></h2>
            <?php if (trim((string) ($program['description'] ?? '')) !== ''): ?><p><?= $e((string) $program['description']) ?></p><?php endif; ?>
            <?php if (trim((string) ($program['focus'] ?? '')) !== ''): ?><span class="blue-badge mx-auto mt-5"><?= $e((string) $program['focus']) ?></span><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="soft-card p-8 text-center text-sm leading-7 text-neutral-600">Program Utama belum dipublikasikan.</div>
    <?php endif; ?>
  </div>
</section>


<style>
/* Fix icon and number color for program-slide-card in Feature page */
.program-slide-card .slide-index,
.program-slide-card .program-icon {
    color: #114b9a !important;
}

/* Ensure any SVG inside program-icon inherits the color if it uses stroke or fill */
.program-slide-card .program-icon svg,
.program-slide-card .program-icon i {
    color: #114b9a !important;
}
</style>
