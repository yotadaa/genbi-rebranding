<?php
$member = $member ?? null;
$teamId = (int) ($teamId ?? ($member['id'] ?? 0));
$presensiActivities = $presensiActivities ?? [];
$manualActivities = $manualActivities ?? [];
$formatDateTime = static function (mixed $value): string {
  $raw = trim((string) $value);
  if ($raw === '') {
    return '-';
  }

  try {
    $date = new DateTimeImmutable($raw);
  } catch (Throwable) {
    return $raw;
  }

  $days = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu',
  ];
  $months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
  ];

  return sprintf(
    '%s, %s %s %s %s',
    $days[$date->format('l')] ?? $date->format('l'),
    $date->format('H:i'),
    $date->format('j'),
    $months[(int) $date->format('n')] ?? $date->format('m'),
    $date->format('Y')
  );
};
$formatDate = static function (mixed $value): string {
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
      <h1 class="section-title mt-3">Detail Aktivitas Poin</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600"><?= $member ? $e($member['name'] ?? '') : 'Anggota tidak ditemukan.' ?></p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.genbiPoin') ?>" class="btn btn-secondary">Kembali</a>
      <a href="<?= $url('admin.genbiPoin.add', ['team_id' => $teamId]) ?>" class="btn btn-primary">Tambah Aktivitas</a>
    </div>
  </header>

  <?php if (!$member): ?>
    <section class="admin-card mt-6 p-6 text-sm text-neutral-600">Data anggota tidak ditemukan.</section>
  <?php else: ?>
    <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_1.25fr]">
      <section class="admin-card p-5 md:p-6">
        <p class="eyebrow">Anggota</p>
        <h2 class="mt-2 text-2xl font-bold text-neutral-950"><?= $e($member['name'] ?? '') ?></h2>
        <p class="mt-2 text-sm font-semibold text-neutral-600"><?= $e($member['role'] ?? '-') ?></p>
        <div class="mt-5 grid gap-3 text-sm text-neutral-700">
          <div><span class="font-bold text-blue-900">Divisi:</span> <?= $e($member['division'] ?? '-') ?></div>
          <div><span class="font-bold text-blue-900">Kampus:</span> <?= $e($member['campus'] ?? '-') ?></div>
        </div>
      </section>

      <section class="admin-card p-5 md:p-6">
        <p class="eyebrow">Rekap Poin</p>
        <div class="mt-4 genbi-point-summary">
          <div>
            <span>Poin Presensi</span>
            <strong><?= (int) ($member['presensi_points'] ?? 0) ?></strong>
          </div>
          <div>
            <span>Poin Manual</span>
            <strong><?= (int) ($member['manual_points'] ?? 0) ?></strong>
          </div>
          <div>
            <span>Total Poin</span>
            <strong><?= (int) ($member['total_points'] ?? 0) ?></strong>
          </div>
        </div>
      </section>
    </div>

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">Presensi</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Aktivitas dari Event</h2>
        </div>
        <p class="text-sm font-semibold text-neutral-500"><?= count($presensiActivities) ?> aktivitas</p>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table genbi-point-detail-table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Role</th>
              <th>Poin</th>
              <th>Status</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($presensiActivities === []): ?>
              <tr><td colspan="5" class="text-center text-sm text-neutral-500">Belum ada aktivitas presensi.</td></tr>
            <?php else: ?>
              <?php foreach ($presensiActivities as $activity): ?>
                <?php $status = strtolower((string) ($activity['status'] ?? 'pending')); ?>
                <tr>
                  <td class="admin-cell-title">
                    <strong><?= $e($activity['event_name'] ?? '') ?></strong>
                    <p><?= $e($activity['location'] ?? '-') ?></p>
                  </td>
                  <td class="admin-cell-meta"><?= $e($activity['role'] ?? '-') ?></td>
                  <td class="admin-cell-meta"><?= (int) ($activity['points'] ?? 0) ?> poin</td>
                  <td class="admin-cell-status">
                    <span class="cms-pill <?= $status === 'approved' ? 'cms-pill-green' : 'cms-pill-yellow' ?>"><?= $e(ucfirst($status)) ?></span>
                  </td>
                  <td class="admin-cell-meta"><?= $e($formatDateTime($activity['created_at'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">Manual</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Aktivitas Tambahan</h2>
        </div>
        <p class="text-sm font-semibold text-neutral-500"><?= count($manualActivities) ?> aktivitas</p>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table genbi-point-detail-table">
          <thead>
            <tr>
              <th>Kegiatan</th>
              <th>Poin</th>
              <th>Tanggal</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($manualActivities === []): ?>
              <tr><td colspan="4" class="text-center text-sm text-neutral-500">Belum ada aktivitas manual.</td></tr>
            <?php else: ?>
              <?php foreach ($manualActivities as $activity): ?>
                <tr>
                  <td class="admin-cell-title"><strong><?= $e($activity['activity_name'] ?? '') ?></strong></td>
                  <td class="admin-cell-meta"><?= (int) ($activity['points'] ?? 0) ?> poin</td>
                  <td class="admin-cell-meta"><?= $e($formatDate($activity['activity_date'] ?? '')) ?></td>
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
  <?php endif; ?>
</section>
