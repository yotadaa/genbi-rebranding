<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 12;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container fade-up">
    <p class="eyebrow">Prestasi</p>
    <h1 class="page-title mt-5">Prestasi GenBI Jambi.</h1>
    <p class="lead mt-7">Pencapaian anggota GenBI Provinsi Jambi di berbagai bidang kompetisi dan pengabdian.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="article-container">
    <div class="prestasi-layout-switch mb-8" aria-label="Pilihan layout prestasi">
      <button type="button" class="prestasi-layout-toggle" data-prestasi-layout="list" aria-label="Tampilkan sebagai list">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h12M3 9h12M3 14h12"/></svg>
      </button>
      <button type="button" class="prestasi-layout-toggle is-active" data-prestasi-layout="grid" aria-label="Tampilkan sebagai grid">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="5" height="5" rx="1"/><rect x="11" y="2" width="5" height="5" rx="1"/><rect x="2" y="11" width="5" height="5" rx="1"/><rect x="11" y="11" width="5" height="5" rx="1"/></svg>
      </button>
    </div>
    <div class="fade-up mb-4 text-sm text-neutral-600">
      <?php if ($total > 0): ?>
        Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> prestasi.
      <?php else: ?>
        Belum ada data prestasi yang tersedia.
      <?php endif; ?>
    </div>
    <div class="prestasi-grid" id="prestasi-list" data-ssr="true">
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $index => $item): ?>
          <?php $image = (string) ($item['image'] ?? $item['photo'] ?? ''); ?>
          <a data-transition href="/prestasi/<?= rawurlencode((string) ($item['slug'] ?? $item['id'])) ?>" class="prestasi-row soft-row" data-id="<?= (int) $item['id'] ?>" data-index="<?= $index ?>">
            <figure class="prestasi-row-image" aria-hidden="<?= $image === '' ? 'true' : 'false' ?>">
              <?php if ($image !== ''): ?>
                <img src="<?= $e($image) ?>" alt="Dokumentasi <?= $e($item['title'] ?? '') ?>" loading="lazy" />
              <?php else: ?>
                <span>No image</span>
              <?php endif; ?>
            </figure>
            <span class="serif prestasi-number"><?= str_pad((string) ($startItem + $index), 2, '0', STR_PAD_LEFT) ?></span>
            <div class="prestasi-row-copy">
              <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
                <span><?= $e($item['category'] ?? '') ?></span><span>•</span><span><?= $e($item['year'] ?? '') ?></span>
              </div>
              <h3 class="serif mt-2 text-2xl font-semibold leading-tight tracking-tight text-neutral-950"><?= $e($item['title'] ?? '') ?></h3>
              <p class="mt-3 text-sm leading-7 text-neutral-600"><?= $e($item['description'] ?? '') ?></p>
            </div>
            <div class="prestasi-person">
              <strong><?= $e($item['name'] ?? '') ?></strong><br /><?= $e($item['campus'] ?? $item['institution'] ?? '') ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="p-8 text-center text-sm text-neutral-600">Belum ada data prestasi yang tersedia.</div>
      <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <nav class="public-pagination mt-8" id="prestasi-pagination" aria-label="Pagination prestasi" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/prestasi?<?= $e(Paginator::buildQuery($page - 1)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/prestasi?<?= $e(Paginator::buildQuery($i)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/prestasi?<?= $e(Paginator::buildQuery($page + 1)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php else: ?>
      <div class="public-pagination mt-8" id="prestasi-pagination" aria-label="Pagination prestasi"></div>
    <?php endif; ?>
  </div>
</section>
<div id="prestasi-modal" class="public-fixed-modal hidden"></div>
