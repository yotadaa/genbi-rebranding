<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 12;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$members = $members ?? [];
$filters = $filters ?? [];
$filterOptions = $filterOptions ?? ['divisions' => [], 'campuses' => [], 'years' => []];
$activeQ = $filters['q'] ?? '';
$activeDivision = $filters['division'] ?? '';
$activeCampus = $filters['campus'] ?? '';
$activeYear = $filters['year'] ?? '';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
$filterParams = array_filter([
    'q' => $activeQ,
    'division' => $activeDivision,
    'campus' => $activeCampus,
    'year' => $activeYear,
], static fn($v) => $v !== '' && $v !== null);
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="site-container fade-up">
    <p class="eyebrow">Team</p>
    <h1 class="page-title mt-5">Tim GenBI Jambi.</h1>
    <p class="lead mt-7">Direktori anggota GenBI Provinsi Jambi. Gunakan filter untuk menemukan anggota berdasarkan divisi, komisariat, atau tahun.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="site-container">
    <form class="fade-up mb-8" method="get" action="/team" id="team-filter-form">
      <div class="grid gap-3 md:grid-cols-[1fr_180px_180px_120px]">
        <input id="team-search" name="q" class="input-soft" placeholder="Cari nama, jabatan, divisi..." value="<?= $e($activeQ) ?>" />
        <select name="division" id="team-division" class="input-soft" onchange="this.form.submit()">
          <option value="">Semua Divisi</option>
          <?php foreach ($filterOptions['divisions'] ?? [] as $div): ?>
            <option value="<?= $e($div) ?>" <?= $div === $activeDivision ? 'selected' : '' ?>><?= $e($div) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="campus" id="team-campus" class="input-soft" onchange="this.form.submit()">
          <option value="">Semua Kampus</option>
          <?php foreach ($filterOptions['campuses'] ?? [] as $campus): ?>
            <option value="<?= $e($campus) ?>" <?= $campus === $activeCampus ? 'selected' : '' ?>><?= $e($campus) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="year" id="team-year" class="input-soft" onchange="this.form.submit()">
          <option value="">Semua Tahun</option>
          <?php foreach ($filterOptions['years'] ?? [] as $year): ?>
            <option value="<?= $e((string) $year) ?>" <?= (string) $year === $activeYear ? 'selected' : '' ?>><?= $e((string) $year) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <noscript><button type="submit" class="btn btn-primary mt-3">Filter</button></noscript>
    </form>
    <div class="fade-up mb-4 flex flex-wrap items-center justify-between gap-3">
      <span class="text-sm text-neutral-600" id="team-count">
        <?php if ($total > 0): ?>
          Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> anggota.
        <?php else: ?>
          Belum ada data anggota yang tersedia.
        <?php endif; ?>
      </span>
      <div class="flex gap-1">
        <button id="team-layout-grid" class="chip is-active" type="button">Grid</button>
        <button id="team-layout-list" class="chip" type="button">List</button>
      </div>
    </div>
    <div class="fade-up team-public-grid" id="team-list" data-ssr="true">
      <?php if (!empty($members)): ?>
        <?php foreach ($members as $member): ?>
          <article class="team-public-card">
            <div class="team-public-photo">
              <?php if (!empty($member['photo'])): ?>
                <img src="<?= $e($member['photo']) ?>" alt="<?= $e($member['name']) ?>" loading="lazy" />
              <?php else: ?>
                <span><?= $e(mb_substr($member['name'] ?? '', 0, 2)) ?></span>
              <?php endif; ?>
            </div>
            <div>
              <p class="eyebrow"><?= $e($member['year'] ?? '') ?></p>
              <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950"><?= $e($member['name'] ?? '') ?></h3>
              <p class="mt-2 text-sm font-semibold text-blue-800"><?= $e($member['role'] ?? '') ?></p>
              <p class="mt-3 text-sm leading-6 text-neutral-600"><?= $e($member['division'] ?? '') ?></p>
              <p class="text-sm leading-6 text-neutral-500"><?= $e($member['campus'] ?? '') ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600 col-span-full">
          Belum ada data anggota yang cocok dengan filter.
        </div>
      <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <nav class="public-pagination mt-8" id="team-pagination" aria-label="Pagination anggota" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/team?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/team?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/team?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php else: ?>
      <div class="public-pagination mt-8" id="team-pagination" aria-label="Pagination anggota"></div>
    <?php endif; ?>
  </div>
</section>
<div id="member-modal" class="public-fixed-modal hidden"></div>
