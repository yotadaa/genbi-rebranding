<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 9;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$activeQ = $filters['q'] ?? '';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
?>
<section class="bg-stone py-16 md:py-24">
  <div class="site-container">
    <p class="eyebrow">Event</p>
    <h1 class="page-title mt-5 max-w-4xl">Agenda dan kegiatan GenBI Jambi.</h1>
    <p class="lead mt-7 max-w-3xl">Daftar kegiatan komunitas yang sudah dan akan dilaksanakan oleh GenBI Provinsi Jambi.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="site-container">
    <form class="mb-8 grid gap-3 rounded-[1.75rem] border border-neutral-900/10 bg-white/80 p-4 shadow-sm md:grid-cols-[1fr_180px]" method="get" action="/event" id="event-filter-form">
      <input id="event-search" name="q" class="input-soft" placeholder="Cari event, lokasi, atau topik" value="<?= $e($activeQ) ?>" />
      <button type="submit" class="chip">Cari</button>
    </form>
    <div class="mb-5 text-sm text-neutral-600" id="event-count">
      <?php if ($total > 0): ?>
        Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> event.
      <?php else: ?>
        Belum ada event yang tersedia.
      <?php endif; ?>
    </div>
    <div class="event-grid" id="event-list" data-ssr="true">
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $item): ?>
          <article class="event-card">
            <div class="event-card-image">
              <?php if (!empty($item['image'])): ?>
                <img src="<?= $e($item['image']) ?>" alt="<?= $e($item['title']) ?>" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
              <?php else: ?>
                <img src="https://genbijambi.com/public/uploads/slider-1.png" alt="<?= $e($item['title']) ?>" loading="lazy" />
              <?php endif; ?>
              <span class="event-card-badge <?= ($item['status'] ?? '') === 'Upcoming' ? 'upcoming' : '' ?>"><?= $e($item['status'] ?? 'Event') ?></span>
            </div>
            <div class="event-card-body">
              <p class="eyebrow"><?= $e($item['start_date'] ?? '') ?><?php if (!empty($item['end_date']) && $item['end_date'] !== ($item['start_date'] ?? '')): ?> – <?= $e($item['end_date']) ?><?php endif; ?></p>
              <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950"><?= $e($item['title'] ?? '') ?></h3>
              <p class="mt-3 text-sm leading-6 text-neutral-600"><?= $e($item['excerpt'] ?? '') ?></p>
              <?php if (!empty($item['location'])): ?>
                <p class="mt-2 text-sm font-semibold text-blue-800"><?= $e($item['location']) ?></p>
              <?php endif; ?>
            </div>
            <a href="/event/<?= (int) $item['id'] ?>" class="btn btn-secondary open-event" data-id="<?= (int) $item['id'] ?>">Detail</a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600 col-span-full">Belum ada event yang cocok.</div>
      <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <nav class="public-pagination mt-8" id="event-pagination" aria-label="Pagination event" data-ssr="true">
        <?php
          $filterParams = array_filter([
            'q' => $activeQ,
          ], static fn($v) => $v !== '' && $v !== null);
        ?>
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/event?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/event?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/event?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php else: ?>
      <div class="public-pagination mt-8" id="event-pagination" aria-label="Pagination event"></div>
    <?php endif; ?>
  </div>
</section>
<div id="event-modal" class="public-fixed-modal hidden"></div>
