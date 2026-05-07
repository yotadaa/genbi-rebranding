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
      <h1 class="section-title mt-3">View News</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Daftar berita dari <code>tbl_news</code>. Aksi hapus tetap memakai custom confirmation modal.</p>
    </div>
    <div class="cms-actions">
      <a href="/admin/news-add" class="btn btn-primary">Add News</a>
    </div>
  </header>
  <div class="mt-6">
    <?php if ($total > 0): ?>
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm text-neutral-600">
        <span>Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> berita.</span>
        <form class="flex items-center gap-2" method="get" action="/admin/news">
          <?php if ($status): ?><input type="hidden" name="status" value="<?= $e($status) ?>" /><?php endif; ?>
          <label for="admin-news-per-page" class="text-sm text-neutral-600">Show</label>
          <select id="admin-news-per-page" name="per_page" class="config-input w-auto" onchange="this.form.submit()">
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
            <th>Photo</th>
            <th>News Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="admin-news-list" data-ssr="true">
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="6" class="text-center text-sm text-neutral-500">Belum ada berita.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $index => $item): ?>
              <tr>
                <td><?= $startItem + $index ?></td>
                <td>
                  <?php if (!empty($item['photo'])): ?>
                    <img src="<?= $e($item['photo']) ?>" class="table-thumb" alt="<?= $e($item['title']) ?>" onerror="this.style.display='none'">
                  <?php else: ?>
                    <span class="text-neutral-400">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= $e($item['title']) ?></strong>
                  <p class="mt-1 text-xs text-neutral-500"><?= $e(substr((string) ($item['date'] ?? ''), 0, 10)) ?></p>
                </td>
                <td><?= $e($item['category']) ?></td>
                <td>
                  <span class="status-pill <?= $e(strtolower($item['status'] ?? 'draft')) ?>">
                    <?= $e(ucfirst($item['status'] ?? 'draft')) ?>
                  </span>
                </td>
                <td>
                  <div class="flex gap-2">
                    <a class="btn btn-secondary btn-sm" href="/admin/news-edit?id=<?= (int) $item['id'] ?>">Edit</a>
                    <button class="btn btn-danger btn-sm" data-delete-news="<?= (int) $item['id'] ?>">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination berita" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/admin/news?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/admin/news?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/admin/news?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
