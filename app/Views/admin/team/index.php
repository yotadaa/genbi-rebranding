<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 24;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$filterOptions = $filterOptions ?? ['divisions' => [], 'campuses' => [], 'years' => []];
$layout = $layout ?? 'grid';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);

// Build filter params for pagination links
$filterParams = array_filter([
  'q' => $filters['q'] ?? '',
  'division' => $filters['division'] ?? '',
  'campus' => $filters['campus'] ?? '',
  'year' => $filters['year'] ?? '',
  'layout' => $layout,
], static fn($v) => $v !== '' && $v !== null);
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">View Team Members</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Direktori anggota GenBI. Listing beranda memakai
        periode terbaru, lalu bisa dioverride dari Team Members lewat aksi BPI Beranda.</p>
    </div>
  </header>

  <div class="mt-6">
    <!-- Toolbar -->
    <section class="admin-card p-4 md:p-6">
      <form method="get" action="/admin/team-member" class="cms-toolbar team-control-toolbar">
        <input type="hidden" name="layout" value="<?= $e($layout) ?>">

        <div class="team-control-group team-control-primary">
          <div class="team-control-filters">
            <label class="team-filter-field text-sm text-neutral-600">
              Show
              <select name="per_page" class="config-input w-auto js-admin-custom-select" onchange="this.form.submit()">
                <?php foreach ([12, 24, 48, 100] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>>
                    <?= $opt ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="team-filter-field text-sm text-neutral-600">
              Divisi
              <select name="division" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Divisi</option>
                <?php foreach (($filterOptions['divisions'] ?? []) as $division): ?>
                  <option value="<?= $e((string) $division) ?>" <?= (string) ($filters['division'] ?? '') === (string) $division ? 'selected' : '' ?>>
                    <?= $e((string) $division) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label class="team-filter-field text-sm text-neutral-600">
              Komisariat
              <select name="campus" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Komisariat</option>
                <?php foreach (($filterOptions['campuses'] ?? []) as $campus): ?>
                  <option value="<?= $e((string) $campus) ?>" <?= (string) ($filters['campus'] ?? '') === (string) $campus ? 'selected' : '' ?>>
                    <?= $e((string) $campus) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label class="team-filter-field text-sm text-neutral-600">
              Tahun
              <select name="year" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php foreach (($filterOptions['years'] ?? []) as $year): ?>
                  <option value="<?= $e((string) $year) ?>" <?= (string) ($filters['year'] ?? '') === (string) $year ? 'selected' : '' ?>>
                    <?= $e((string) $year) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>

          <div class="team-control-actions">
            <label class="team-filter-field text-sm text-neutral-600">Cari</label>
            <label class="cms-search">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input name="q" placeholder="Cari nama, jabatan, komisariat, divisi..."
                value="<?= $e($filters['q'] ?? '') ?>">
            </label>
          </div>
        </div>

        <div class="team-control-group team-control-secondary">
          <div class="view-toggle" role="group" aria-label="Layout mode">
            <a href="?<?= $e(http_build_query(array_merge($filterParams, ['layout' => 'grid']))) ?>"
              class="view-toggle-btn <?= $layout === 'grid' ? 'is-active' : '' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z">
                </path>
              </svg>
              Grid
            </a>
            <a href="?<?= $e(http_build_query(array_merge($filterParams, ['layout' => 'list']))) ?>"
              class="view-toggle-btn <?= $layout === 'list' ? 'is-active' : '' ?>">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
              </svg>
              List
            </a>
          </div>

          <button type="button" class="cms-action edit" id="team-batch-toggle">Batch Operation</button>
        </div>
      </form>

      <div class="team-batch-bar mt-3 hidden" id="team-batch-bar">
        <a href="<?= $url('admin.team.add') ?>" class="cms-action edit">Add Team Member</a>
        <strong><span id="team-selection-count">0</span> dipilih</strong>
        <button type="button" class="cms-action" data-team-bulk="home_remove">Hapus BPI dari Beranda</button>
        <button type="button" class="cms-action" id="team-selection-clear">Clear</button>
      </div>

      <?php if ($total > 0): ?>
        <div class="mt-4 text-sm text-neutral-600">
          Menampilkan <?= $startItem ?>-<?= $endItem ?> dari <?= $total ?> anggota.
        </div>
      <?php endif; ?>
    </section>

    <!-- Team Cards -->
    <div class="<?= $layout === 'grid' ? 'team-card-grid' : 'team-card-list' ?> mt-5" id="admin-team-list"
      data-ssr="true">
      <?php if (empty($items)): ?>
        <div class="admin-card p-8 text-center text-sm text-neutral-500">Belum ada anggota.</div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <article class="team-admin-card <?= !empty($item['show_on_home']) ? 'is-home' : '' ?>"
            data-team-id="<?= (int) $item['id'] ?>">
            <label class="team-select-check hidden">
              <input type="checkbox" data-team-select="<?= (int) $item['id'] ?>"> Select
            </label>
            <div class="team-admin-photo">
              <?php if (!empty($item['photo'])): ?>
                <img src="<?= $e($item['photo']) ?>" alt="<?= $e($item['name']) ?>"
                  onerror="this.remove(); this.parentElement.textContent='<?= $e(mb_substr($item['name'] ?? '', 0, 2)) ?>';">
              <?php else: ?>
                <?= $e(mb_substr($item['name'] ?? '', 0, 2)) ?>
              <?php endif; ?>
            </div>
            <div class="team-admin-content">
              <h2><?= $e($item['name'] ?? '') ?></h2>
              <p><?= $e($item['role'] ?? '') ?></p>
              <div class="team-tags">
                <?php if (!empty($item['campus'])): ?><span><?= $e($item['campus']) ?></span><?php endif; ?>
                <?php if (!empty($item['division'])): ?><span><?= $e($item['division']) ?></span><?php endif; ?>
                <?php if (!empty($item['year'])): ?><span><?= $e($item['year']) ?></span><?php endif; ?>
              </div>
            </div>
            <div class="team-card-actions">
              <button type="button" class="cms-action" data-team-home="<?= (int) $item['id'] ?>"
                title="<?= !empty($item['show_on_home']) ? 'Hapus BPI dari Beranda' : 'Tambah Anggota ke Beranda' ?>"><?= !empty($item['show_on_home']) ? 'Remove' : 'Add' ?></button>
              <a href="<?= $url('admin.team.edit', ['id' => (int) $item['id']]) ?>" class="cms-action edit">Edit</a>
              <button class="cms-action delete" data-delete-team="<?= (int) $item['id'] ?>">Delete</button>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination anggota" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button"
            href="<?= $url('admin.team') ?>?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button"
              href="<?= $url('admin.team') ?>?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button"
            href="<?= $url('admin.team') ?>?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>