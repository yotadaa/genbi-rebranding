<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 12;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$activeQ = $filters['q'] ?? '';
$activeCategory = $filters['category'] ?? '';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container">
    <p class="eyebrow">News</p>
    <h1 class="page-title mt-5">Berita GenBI Jambi.</h1>
    <p class="lead mt-7">Layout dibuat seperti publikasi editorial. Pengguna membaca daftar berita dengan ritme yang nyaman, bukan grid kartu yang padat.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="article-container">
    <form class="news-filter mb-8 grid gap-3 md:grid-cols-[1fr_220px]" method="get" action="/news" id="news-filter-form">
      <input id="news-search" name="q" class="input-soft" placeholder="Cari berita, kategori, atau topik" value="<?= $e($activeQ) ?>" aria-label="Cari berita" />
      <div id="news-category"><?php if ($activeCategory !== ''): ?><input type="hidden" name="category" value="<?= $e($activeCategory) ?>" /><?php endif; ?></div>
    </form>
    <div class="mb-4 text-sm text-neutral-600" id="news-count">
      <?php if ($total > 0): ?>
        Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> berita.
      <?php else: ?>
        Belum ada berita yang tersedia.
      <?php endif; ?>
    </div>
    <div id="news-list" data-ssr="true">
      <h2 class="sr-only">Daftar Berita</h2>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $index => $item): ?>
          <article>
          <a data-transition href="/news/<?= rawurlencode((string) $item['slug']) ?>" class="article-link <?= $index === 0 ? 'pt-0 border-t-0' : '' ?>">
            <div class="grid gap-5 md:grid-cols-[170px_1fr] md:items-start">
              <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-blue-50">
                <?php if (!empty($item['image'])): ?>
                  <img src="<?= $e($item['image']) ?>" alt="<?= $e($item['title']) ?>" class="h-full w-full object-cover" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
                <?php endif; ?>
              </div>
              <div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-neutral-500">
                  <span class="text-blue-800"><?= $e($item['category']) ?></span>
                  <span><?= $e(substr((string) ($item['date'] ?? ''), 0, 10)) ?></span>
                </div>
                <h3 class="serif mt-3 text-3xl font-semibold leading-tight tracking-tight text-neutral-950"><?= $e($item['title']) ?></h3>
                <p class="mt-4 text-base leading-7 text-neutral-600"><?= $e($item['excerpt']) ?></p>
                <span class="btn btn-secondary mt-5 w-fit">Detail</span>
              </div>
            </div>
          </a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600">
          Belum ada data berita yang tersedia.
        </div>
      <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <nav class="public-pagination mt-8" id="news-pagination" aria-label="Pagination berita" data-ssr="true">
        <?php
          $filterParams = array_filter([
            'q' => $activeQ,
            'category' => $activeCategory,
          ], static fn($v) => $v !== '' && $v !== null);
        ?>
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/news?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/news?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/news?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php else: ?>
      <div class="public-pagination mt-8" id="news-pagination" aria-label="Pagination berita"></div>
    <?php endif; ?>
  </div>
</section>
