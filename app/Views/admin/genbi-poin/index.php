<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$items = $items ?? [];
$activities = $activities ?? [];
$filters = $filters ?? [];
$query = (string) ($filters['q'] ?? '');
$startItem = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$endItem = min($page * $perPage, $total);
$filterParams = array_filter([
  'q' => $query,
  'per_page' => (string) $perPage,
], static fn($value) => $value !== '' && $value !== null);
$formatActivityDate = static function (mixed $value): string {
  $raw = trim((string) $value);
  if ($raw === '') {
    return '-';
  }

  try {
    return (new DateTimeImmutable($raw))->format('d M Y');
  } catch (Throwable) {
    return $raw;
  }
};
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">GenBI Poin</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Rekap poin anggota dari presensi yang sudah approved dan aktivitas manual.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.genbiPoin.add') ?>" class="btn btn-primary">Tambah Aktivitas</a>
    </div>
  </header>

  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form class="cms-toolbar cms-toolbar-admin" method="get" action="/admin/genbi-poin">
        <div class="admin-toolbar-row">
          <label class="admin-toolbar-inline-label text-sm text-neutral-600">Show
            <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">
              <?php foreach ([10, 25, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
            entries
          </label>
          <div class="cms-search">
            <input name="q" placeholder="Cari nama anggota..." value="<?= $e($query) ?>" />
            <noscript><button type="submit" class="btn btn-secondary btn-sm">Cari</button></noscript>
          </div>
        </div>
        <?php if ($total > 0): ?>
          <div class="admin-toolbar-summary text-sm text-neutral-600">
            Menampilkan <?= $startItem ?>-<?= $endItem ?> dari <?= $total ?> anggota.
          </div>
        <?php endif; ?>
      </form>
    </section>

    <section class="admin-card p-0 mt-5">
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Anggota</th>
              <th>Divisi</th>
              <th>Kampus</th>
              <th>Poin Presensi</th>
              <th>Poin Manual</th>
              <th>Total Poin</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($items === []): ?>
              <tr>
                <td colspan="7" class="text-center text-sm text-neutral-500">Belum ada anggota yang cocok.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td class="admin-cell-index"><?= $startItem + $index ?></td>
                  <td class="admin-cell-title">
                    <strong><?= $e($item['name'] ?? '') ?></strong>
                    <p><?= $e($item['role'] ?? '-') ?></p>
                  </td>
                  <td class="admin-cell-meta"><?= $e($item['division'] ?? '-') ?></td>
                  <td class="admin-cell-meta"><?= $e($item['campus'] ?? '-') ?></td>
                  <td class="admin-cell-meta"><?= (int) ($item['presensi_points'] ?? 0) ?> poin</td>
                  <td class="admin-cell-meta"><?= (int) ($item['manual_points'] ?? 0) ?> poin</td>
                  <td class="admin-cell-title"><strong><?= (int) ($item['total_points'] ?? 0) ?> poin</strong></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <?php if ($totalPages > 1): ?>
      <nav class="admin-pagination mt-5" aria-label="Pagination GenBI Poin">
        <?php if ($page > 1): ?>
          <a class="pager-button" href="<?= $url('admin.genbiPoin') ?>?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="pager-button is-active" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a class="pager-button" href="<?= $url('admin.genbiPoin') ?>?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a class="pager-button" href="<?= $url('admin.genbiPoin') ?>?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
        <?php else: ?>
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">Aktivitas Manual</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Riwayat Terbaru</h2>
        </div>
        <a href="<?= $url('admin.genbiPoin.add') ?>" class="btn btn-secondary btn-sm">Tambah Aktivitas</a>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table">
          <thead>
            <tr>
              <th>Anggota</th>
              <th>Kegiatan</th>
              <th>Poin</th>
              <th>Tanggal</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($activities === []): ?>
              <tr>
                <td colspan="5" class="text-center text-sm text-neutral-500">Belum ada aktivitas manual.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($activities as $activity): ?>
                <tr>
                  <td class="admin-cell-title">
                    <strong><?= $e($activity['member_name'] ?? '') ?></strong>
                  </td>
                  <td class="admin-cell-meta"><?= $e($activity['activity_name'] ?? '') ?></td>
                  <td class="admin-cell-meta"><?= (int) ($activity['points'] ?? 0) ?> poin</td>
                  <td class="admin-cell-meta"><?= $e($formatActivityDate($activity['activity_date'] ?? '')) ?></td>
                  <td class="admin-cell-actions">
                    <a class="btn btn-secondary btn-sm" href="<?= $url('admin.genbiPoin.edit', ['id' => (int) ($activity['id'] ?? 0)]) ?>">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</section>
