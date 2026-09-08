<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$items = $items ?? [];
$filters = $filters ?? [];
$status = $filters['status'] ?? '';
$query = $filters['q'] ?? '';
$showOnHome = $filters['show_on_home'] ?? '';
$filterParams = array_filter([
    'q' => $query,
    'status' => $status,
    'show_on_home' => $showOnHome,
], static fn($value) => $value !== '' && $value !== null);
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Program Utama</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Kelola program utama yang tampil di landing page, lengkap dengan ikon, status, dan slideshow gambar.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.feature.add') ?>" class="btn btn-primary"><?= $e('Add Program Utama') ?></a>
    </div>
  </header>
  <div id="cms-body" class="mt-6">
    <section class="admin-card p-4 md:p-6" id="admin-feature-list" data-ssr="true">
      <form class="cms-toolbar" method="get" action="/admin/feature">
        <div class="flex flex-wrap items-center gap-3">
          <label class="text-sm text-neutral-600">Show
            <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">
              <?php foreach ([10, 25, 50] as $opt): ?>
                <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
            entries
          </label>
          <select name="status" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()">
            <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua Status</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
          </select>
          <select name="show_on_home" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()">
            <option value="" <?= $showOnHome === '' ? 'selected' : '' ?>>Semua Visibilitas</option>
            <option value="1" <?= (string) $showOnHome === '1' ? 'selected' : '' ?>>Tampil di Beranda</option>
            <option value="0" <?= (string) $showOnHome === '0' ? 'selected' : '' ?>>Tidak Tampil</option>
          </select>
          <input name="q" class="config-input min-w-[16rem]" value="<?= $e($query) ?>" placeholder="Cari label, nama program, atau fokus..." />
        </div>
      </form>
      <div class="admin-responsive-table mt-5">
        <table class="cms-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Program</th>
              <th>Fokus</th>
              <th>Visual</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($items !== []): ?>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td><?= (($page - 1) * $perPage) + $index + 1 ?></td>
                  <td>
                    <div class="flex items-center gap-3">
                      <span class="program-list-icon" data-program-icon="<?= $e($item['icon_key'] ?? 'sparkles') ?>"></span>
                      <div>
                        <strong><?= $e($item['title'] ?: 'Tanpa label') ?></strong>
                        <p class="mt-1 text-sm text-neutral-600"><?= $e($item['name'] ?: 'Tanpa nama program') ?></p>
                      </div>
                    </div>
                  </td>
                  <td><p class="news-caption-cell"><?= $e($item['focus'] ?: '-') ?></p></td>
                  <td>
                    <div class="feature-image-microstack">
                      <?php if (!empty($item['images'])): ?>
                        <?php foreach (array_slice($item['images'], 0, 3) as $image): ?>
                          <img src="<?= $e($image['url']) ?>" alt="<?= $e($item['name'] ?: 'Program Utama') ?>" />
                        <?php endforeach; ?>
                        <span class="text-xs text-neutral-500"><?= count($item['images']) ?> gambar</span>
                      <?php else: ?>
                        <span class="text-sm text-neutral-500">Belum ada gambar</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div class="grid gap-2">
                      <span class="cms-pill <?= $item['status'] === 'published' ? 'cms-pill-green' : ($item['status'] === 'draft' ? 'cms-pill-yellow' : '') ?>"><?= $e(ucfirst($item['status'])) ?></span>
                      <span class="cms-pill <?= !empty($item['show_on_home']) ? 'cms-pill-blue' : '' ?>"><?= !empty($item['show_on_home']) ? 'Beranda' : 'Tersembunyi' ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="flex gap-2">
                      <a href="<?= $url('admin.feature.edit', ['id' => (int) $item['id']]) ?>" class="cms-action edit">Edit</a>
                      <button type="button" class="cms-action delete" data-delete-feature="<?= (int) $item['id'] ?>">Delete</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-neutral-500 py-8">Belum ada Program Utama.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($totalPages > 1): ?>
        <nav class="public-pagination mt-8" aria-label="Pagination Program Utama">
          <?php if ($page > 1): ?>
            <a class="pager-button" href="<?= $url('admin.feature') ?>?<?= $e(Paginator::buildQuery($page - 1, $filterParams + ['per_page' => $perPage])) ?>">Sebelumnya</a>
          <?php else: ?>
            <span class="pager-button" aria-disabled="true">Sebelumnya</span>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
              <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
            <?php else: ?>
              <a class="pager-button" href="<?= $url('admin.feature') ?>?<?= $e(Paginator::buildQuery($i, $filterParams + ['per_page' => $perPage])) ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <a class="pager-button" href="<?= $url('admin.feature') ?>?<?= $e(Paginator::buildQuery($page + 1, $filterParams + ['per_page' => $perPage])) ?>">Berikutnya</a>
          <?php else: ?>
            <span class="pager-button" aria-disabled="true">Berikutnya</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>
  </div>
</section>
