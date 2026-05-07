<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 24;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? [];
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
$filterParams = array_filter([
    'q' => $filters['q'] ?? '',
    'division' => $filters['division'] ?? '',
    'campus' => $filters['campus'] ?? '',
    'year' => $filters['year'] ?? '',
], static fn($v) => $v !== '' && $v !== null);
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">View Team Members</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Direktori anggota GenBI. Aksi hapus memakai custom confirmation modal.</p>
    </div>
    <div class="cms-actions">
      <a href="/admin/team-member-add" class="btn btn-primary">Add Team Member</a>
    </div>
  </header>
  <div class="mt-6">
    <?php if ($total > 0): ?>
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm text-neutral-600">
        <span>Menampilkan <?= $startItem ?>–<?= $endItem ?> dari <?= $total ?> anggota.</span>
        <form class="flex items-center gap-2" method="get" action="/admin/team-member">
          <?php foreach ($filterParams as $k => $v): ?><input type="hidden" name="<?= $e($k) ?>" value="<?= $e($v) ?>" /><?php endforeach; ?>
          <label for="admin-team-per-page" class="text-sm text-neutral-600">Show</label>
          <select id="admin-team-per-page" name="per_page" class="config-input w-auto" onchange="this.form.submit()">
            <?php foreach ([12, 24, 48, 100] as $opt): ?>
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
            <th>Name</th>
            <th>Division</th>
            <th>Campus</th>
            <th>Year</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="admin-team-list" data-ssr="true">
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="7" class="text-center text-sm text-neutral-500">Belum ada anggota.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $index => $item): ?>
              <tr>
                <td><?= $startItem + $index ?></td>
                <td>
                  <?php if (!empty($item['photo'])): ?>
                    <img src="<?= $e($item['photo']) ?>" class="table-thumb rounded-full" alt="<?= $e($item['name']) ?>" onerror="this.style.display='none'">
                  <?php else: ?>
                    <span class="text-neutral-400"><?= $e(mb_substr($item['name'] ?? '', 0, 2)) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= $e($item['name'] ?? '') ?></strong>
                  <p class="mt-1 text-xs text-neutral-500"><?= $e($item['role'] ?? '') ?></p>
                </td>
                <td><?= $e($item['division'] ?? '') ?></td>
                <td><?= $e($item['campus'] ?? '') ?></td>
                <td><?= $e($item['year'] ?? '') ?></td>
                <td>
                  <div class="flex gap-2">
                    <a class="btn btn-secondary btn-sm" href="/admin/team-member-edit?id=<?= (int) $item['id'] ?>">Edit</a>
                    <button class="btn btn-danger btn-sm" data-delete-team="<?= (int) $item['id'] ?>">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination anggota" data-ssr="true">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="/admin/team-member?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="/admin/team-member?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="/admin/team-member?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
