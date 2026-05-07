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
                <td><?= $index + 1 ?></td>
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
  </div>
</section>
