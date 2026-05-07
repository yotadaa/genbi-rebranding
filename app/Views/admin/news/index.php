<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$categories = $categories ?? [];
$selectedCategories = $selectedCategories ?? [];
$layout = $layout ?? 'list';
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);

// Build filter params for pagination links
$filterParams = array_filter([
    'q' => $filters['q'] ?? '',
    'status' => $filters['status'] ?? '',
    'layout' => $layout,
], static fn($v) => $v !== '' && $v !== null);
foreach ($selectedCategories as $catId) {
    $filterParams['category[' . $catId . ']'] = $catId;
}
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
    <!-- Toolbar -->
    <section class="admin-card p-4 md:p-6">
      <form method="get" action="/admin/news" class="cms-toolbar" id="news-filter-form">
        <input type="hidden" name="layout" value="<?= $e($layout) ?>">
        <?php foreach ($selectedCategories as $catId): ?>
          <input type="hidden" name="category[]" value="<?= (int) $catId ?>">
        <?php endforeach; ?>
        
        <div class="flex flex-wrap items-center gap-3">
          <label class="cms-search">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input id="news-search" name="q" placeholder="Cari judul, konten, kategori..." value="<?= $e($filters['q'] ?? '') ?>">
          </label>
          
          <div class="admin-filter-group">
            <label class="text-sm text-neutral-600">Status</label>
            <select name="status" class="config-input w-auto js-admin-custom-select" onchange="this.form.submit()">
              <option value="">Semua Status</option>
              <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>

          <div class="admin-filter-group">
            <label class="text-sm text-neutral-600">Category</label>
            <div class="admin-multi-select" data-ssr="true">
              <button type="button" class="admin-multi-select-button" aria-expanded="false">
                <span id="category-label"><?= empty($selectedCategories) ? 'Semua Kategori' : count($selectedCategories) . ' dipilih' ?></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </button>
              <div class="admin-multi-select-menu hidden">
                <?php foreach ($categories as $cat): ?>
                  <label class="admin-multi-select-option">
                    <input type="checkbox" name="category[]" value="<?= (int) $cat['category_id'] ?>" <?= in_array((int) $cat['category_id'], $selectedCategories, true) ? 'checked' : '' ?>>
                    <span><?= $e($cat['category_name']) ?></span>
                  </label>
                <?php endforeach; ?>
                <div class="admin-multi-select-actions">
                  <button type="button" class="admin-multi-select-clear">Clear</button>
                  <button type="submit" class="admin-multi-select-apply">Apply</button>
                </div>
              </div>
            </div>
          </div>

          <label class="text-sm text-neutral-600">
            Show
            <select name="per_page" class="config-input w-auto" onchange="this.form.submit()">
              <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
            entries
          </label>
        </div>
      </form>

      <div class="view-toggle mt-4" role="group" aria-label="Layout mode">
        <a href="?<?= $e(http_build_query(array_merge($filterParams, ['layout' => 'list']))) ?>" class="view-toggle-btn <?= $layout === 'list' ? 'is-active' : '' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          List
        </a>
        <a href="?<?= $e(http_build_query(array_merge($filterParams, ['layout' => 'grid']))) ?>" class="view-toggle-btn <?= $layout === 'grid' ? 'is-active' : '' ?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
          Grid
        </a>
      </div>

      <?php if ($total > 0): ?>
        <div class="mt-4 text-sm text-neutral-600">
          Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> berita.
        </div>
      <?php endif; ?>
    </section>

    <!-- List Layout -->
    <?php if ($layout === 'list'): ?>
      <section class="admin-card overflow-hidden p-0 mt-5">
        <table class="admin-table">
          <thead>
            <tr>
              <th>SL</th>
              <th>News Title</th>
              <th>News Short Content</th>
              <th>Photo</th>
              <th>Category</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="admin-news-list" data-ssr="true">
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="7" class="text-center text-sm text-neutral-500">Belum ada berita.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td><?= $startItem + $index ?></td>
                  <td>
                    <strong><?= $e($item['title']) ?></strong>
                    <p class="mt-1 text-xs text-neutral-500"><?= $e(substr((string) ($item['date'] ?? ''), 0, 10)) ?></p>
                  </td>
                  <td>
                    <p class="news-caption-cell"><?= $e($item['excerpt']) ?></p>
                  </td>
                  <td>
                    <?php if (!empty($item['photo'])): ?>
                      <img src="<?= $e($item['photo']) ?>" class="table-thumb" alt="<?= $e($item['title']) ?>" onerror="this.style.display='none'">
                    <?php else: ?>
                      <span class="text-neutral-400">-</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="cms-pill"><?= $e($item['category']) ?></span></td>
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
    <?php endif; ?>

    <!-- Grid Layout -->
    <?php if ($layout === 'grid'): ?>
      <div class="admin-news-grid mt-5" id="admin-news-list" data-ssr="true">
        <?php if (empty($items)): ?>
          <div class="admin-card p-8 text-center text-sm text-neutral-500">Belum ada berita.</div>
        <?php else: ?>
          <?php foreach ($items as $item): ?>
            <article class="admin-news-card">
              <?php if (!empty($item['photo'])): ?>
                <img src="<?= $e($item['photo']) ?>" alt="<?= $e($item['title']) ?>" onerror="this.remove()">
              <?php else: ?>
                <div class="admin-news-card-placeholder">No Image</div>
              <?php endif; ?>
              <div class="admin-news-card-content">
                <h2><?= $e($item['title']) ?></h2>
                <p><?= $e(mb_substr($item['excerpt'], 0, 120)) ?><?= mb_strlen($item['excerpt']) > 120 ? '...' : '' ?></p>
                <div class="admin-news-card-meta">
                  <span class="cms-pill"><?= $e($item['category']) ?></span>
                  <span class="status-pill <?= $e(strtolower($item['status'] ?? 'draft')) ?>"><?= $e(ucfirst($item['status'] ?? 'draft')) ?></span>
                  <span class="text-xs text-neutral-500"><?= $e(substr((string) ($item['date'] ?? ''), 0, 10)) ?></span>
                </div>
                <div class="admin-news-card-actions">
                  <a class="btn btn-secondary btn-sm" href="/admin/news-edit?id=<?= (int) $item['id'] ?>">Edit</a>
                  <button class="btn btn-danger btn-sm" data-delete-news="<?= (int) $item['id'] ?>">Delete</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Pagination -->
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
