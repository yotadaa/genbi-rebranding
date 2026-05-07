<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$status = $status ?? null;
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
$filterParams = array_filter(['status' => $status ?? ''], static fn($v) => $v !== '' && $v !== null);
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">View Prestasi</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.</p>
    </div>
    <div class="cms-actions">
      <a href="/admin/prestasi-add" class="btn btn-primary">Add Prestasi</a>
    </div>
  </header>
  <div class="mt-6">
    <?php if ($total > 0): ?>
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm text-neutral-600">
        <span>Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> prestasi.</span>
        <form class="flex items-center gap-2" method="get" action="/admin/prestasi">
          <?php if ($status): ?><input type="hidden" name="status" value="<?= $e($status) ?>" /><?php endif; ?>
          <label for="admin-prestasi-per-page" class="text-sm text-neutral-600">Show</label>
          <select id="admin-prestasi-per-page" name="per_page" class="config-input w-auto" onchange="this.form.submit()">
            <?php foreach ([10, 25, 50, 100] as $opt): ?>
              <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-sm text-neutral-600">entries</span>
        </form>
      </div>
    <?php endif; ?>
    <section class="admin-card overflow-hidden p-0">
      <table class="admin-table">
        <thead>
          <tr>
            <th>SL</th>
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
                <td><?= $startItem + $index ?></td>
                <td><strong><?= $e($item['title'] ?? '') ?></strong></td>
                <td><?= $e($item['name'] ?? '') ?></td>
                <td><span class="cms-pill"><?= $e($item['category'] ?? '') ?></span></td>
                <td><?= $e($item['year'] ?? '') ?></td>
                <td><?= $e($item['institution'] ?? '') ?></td>
                <td>
                  <?php $itemStatus = strtolower($item['status'] ?? 'draft'); ?>
                  <span class="cms-pill <?= $itemStatus === 'published' ? 'cms-pill-green' : ($itemStatus === 'draft' ? 'cms-pill-yellow' : '') ?>">
                    <?= $e(ucfirst($itemStatus)) ?>
                  </span>
                </td>
                <td>
                  <div class="flex gap-2">
                    <a class="btn btn-secondary btn-sm" href="/admin/prestasi-edit?id=<?= (int) $item['id'] ?>">Edit</a>
                    <button class="btn btn-danger btn-sm" data-delete-prestasi="<?= (int) $item['id'] ?>">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination prestasi" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/admin/prestasi?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/admin/prestasi?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/admin/prestasi?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
