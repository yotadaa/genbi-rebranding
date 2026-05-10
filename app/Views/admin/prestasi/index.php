<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$activeQ = $filters['q'] ?? '';
$activeCategory = $filters['category'] ?? '';
$activeYear = $filters['year'] ?? '';
$activeStatus = $filters['status'] ?? '';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
$filterParams = array_filter([
    'q' => $activeQ,
    'category' => $activeCategory,
    'year' => $activeYear,
    'status' => $activeStatus,
], static fn($v) => $v !== '' && $v !== null);
$prestasiCategories = ['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', 'Harapan 2', 'Finalis', 'Peserta Terbaik'];
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">View Prestasi</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin/prestasi-token') ?>" class="btn btn-secondary">Buat Link Form Prestasi</a>
      <a href="<?= $url('admin.prestasi.add') ?>" class="btn btn-primary">Add Prestasi</a>
    </div>
  </header>
  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form class="cms-toolbar cms-toolbar-admin" method="get" action="/admin/prestasi" id="prestasi-filter-form">
        <div class="admin-toolbar-row">
          <label class="admin-toolbar-inline-label text-sm text-neutral-600">Show
            <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">
              <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
            entries
          </label>
          <select name="category" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($prestasiCategories as $cat): ?>
              <option value="<?= $e($cat) ?>" <?= $cat === $activeCategory ? 'selected' : '' ?>><?= $e($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="status" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="published" <?= $activeStatus === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $activeStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= $activeStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
          </select>
          <div class="cms-search">
            <input name="q" id="prestasi-search" placeholder="Search prestasi..." value="<?= $e($activeQ) ?>" />
            <noscript><button type="submit" class="btn btn-secondary btn-sm">Cari</button></noscript>
          </div>
        </div>

        <?php if ($total > 0): ?>
          <div class="admin-toolbar-summary text-sm text-neutral-600">
            Menampilkan <?= $startItem ?>-<?= $endItem ?> dari <?= $total ?> prestasi.
          </div>
        <?php endif; ?>
      </form>
    </section>

    <section class="admin-card p-0 mt-5">
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table admin-data-table-prestasi">
          <colgroup>
            <col class="admin-col-index">
            <col class="admin-col-title">
            <col class="admin-col-person">
            <col class="admin-col-meta">
            <col class="admin-col-year">
            <col class="admin-col-institution">
            <col class="admin-col-status">
            <col class="admin-col-actions">
          </colgroup>
          <thead>
            <tr>
              <th>No.</th>
              <th>Judul</th>
              <th>Nama Anggota</th>
              <th>Peringkat</th>
              <th>Tahun</th>
              <th>Penyelenggara</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="admin-prestasi-list" data-ssr="true">
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="8" class="text-center text-sm text-neutral-500">Belum ada data prestasi.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td class="admin-cell-index"><?= $startItem + $index ?></td>
                  <td class="admin-cell-title">
                    <div class="admin-table-media">
                      <?php if (!empty($item['image'])): ?>
                        <img src="<?= $e($item['image']) ?>" class="table-thumb rounded" alt="<?= $e($item['title'] ?? '') ?>" />
                      <?php else: ?>
                        <span class="admin-thumb-placeholder">No image</span>
                      <?php endif; ?>
                      <div class="admin-table-title">
                        <strong><?= $e($item['title'] ?? '') ?></strong>
                        <p><?= $e(mb_substr($item['description'] ?? '', 0, 90)) ?></p>
                      </div>
                    </div>
                  </td>
                  <td class="admin-cell-meta"><?= $e($item['name'] ?? '') ?></td>
                  <td class="admin-cell-meta"><span class="cms-pill"><?= $e($item['category'] ?? '') ?></span></td>
                  <td class="admin-cell-year"><?= $e($item['year'] ?? '') ?></td>
                  <td class="admin-cell-meta"><?= $e($item['institution'] ?? '') ?></td>
                  <td class="admin-cell-status">
                    <?php $itemStatus = strtolower($item['status'] ?? 'draft'); ?>
                    <span class="cms-pill <?= $itemStatus === 'published' ? 'cms-pill-green' : ($itemStatus === 'draft' ? 'cms-pill-yellow' : '') ?>">
                      <?= $e(ucfirst($itemStatus)) ?>
                    </span>
                  </td>
                  <td class="admin-cell-actions">
                    <div class="admin-table-actions">
                      <button class="btn btn-outline btn-sm" data-detail-prestasi="<?= (int) $item['id'] ?>">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M2.75 10s2.75-4.75 7.25-4.75S17.25 10 17.25 10 14.5 14.75 10 14.75 2.75 10 2.75 10Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="2.25" stroke="currentColor" stroke-width="1.5"/></svg>
                        Detail
                      </button>
                      <a class="btn btn-secondary btn-sm" href="<?= $url('admin.prestasi.edit', ['id' => (int) $item['id']]) ?>">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M13.25 3.75a2.1 2.1 0 1 1 2.97 2.97L7 16.94 3 17l.06-4 10.19-10.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="m12 5 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Edit
                      </a>
                      <button class="btn btn-danger btn-sm" data-delete-prestasi="<?= (int) $item['id'] ?>">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.5 5.5h11M8 5.5V4.25A1.25 1.25 0 0 1 9.25 3h1.5A1.25 1.25 0 0 1 12 4.25V5.5m-5.5 0 .5 10A1.25 1.25 0 0 0 8.25 16.75h3.5A1.25 1.25 0 0 0 13 15.5l.5-10M8.5 8.25v5M11.5 8.25v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination prestasi" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="<?= $url('admin.prestasi') ?>?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="<?= $url('admin.prestasi') ?>?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="<?= $url('admin.prestasi') ?>?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
