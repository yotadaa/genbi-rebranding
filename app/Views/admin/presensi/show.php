<?php
$item = $item ?? null;
$submissions = $submissions ?? [];
$roleOptions = $item && is_array($item['role_options'] ?? null)
  ? $item['role_options']
  : array_map(static fn($role): array => ['name' => (string) $role, 'score' => 0], $item && is_array($item['roles'] ?? null) ? $item['roles'] : []);
$roleScores = [];
foreach ($roleOptions as $roleOption) {
  $roleName = (string) ($roleOption['name'] ?? '');
  if ($roleName !== '') {
    $roleScores[$roleName] = (int) ($roleOption['score'] ?? 0);
  }
}
$formatPresensiTime = static function (mixed $value): string {
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

  $day = $days[$date->format('l')] ?? $date->format('l');
  $month = $months[(int) $date->format('n')] ?? $date->format('m');
  return sprintf('%s, %s %s %s %s', $day, $date->format('H:i'), $date->format('j'), $month, $date->format('Y'));
};
$members = $item && is_array($item['members'] ?? null) ? $item['members'] : [];
$submissionsByTeamId = [];
$memberIds = [];
foreach ($members as $member) {
  $memberId = (int) ($member['id'] ?? 0);
  if ($memberId > 0) {
    $memberIds[$memberId] = true;
  }
}
foreach ($submissions as $submission) {
  $teamId = (int) ($submission['team_id'] ?? 0);
  if ($teamId > 0 && !isset($submissionsByTeamId[$teamId])) {
    $submissionsByTeamId[$teamId] = $submission;
  }
}
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Detail Presensi</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600"><?= $item ? $e($item['event_name'] ?? '') : 'Event tidak ditemukan.' ?></p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.presensi') ?>" class="btn btn-secondary">View All</a>
      <?php if ($item): ?>
        <a href="<?= $url('admin.presensi.edit', ['id' => (int) $item['id']]) ?>" class="btn btn-primary">Edit Event</a>
      <?php endif; ?>
    </div>
  </header>

  <?php if (!$item): ?>
    <section class="admin-card mt-6 p-6 text-sm text-neutral-600">Event presensi tidak ditemukan.</section>
  <?php else: ?>
    <?php
      $publicUrl = (string) ($item['public_url'] ?? '');
      $absoluteUrl = $publicUrl !== '' ? $publicUrl : '#';
    ?>
    <div class="mt-6 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
      <section class="admin-card p-5 md:p-6">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <p class="eyebrow">Event</p>
            <h2 class="mt-2 text-2xl font-bold text-neutral-950"><?= $e($item['event_name'] ?? '') ?></h2>
            <p class="mt-2 text-sm text-neutral-600"><?= $e($item['location'] ?? '') ?></p>
          </div>
          <div>
            <p class="eyebrow">Status</p>
            <div class="mt-2 flex flex-wrap gap-2">
              <span class="cms-pill cms-pill-green"><?= $e(ucfirst((string) ($item['status'] ?? 'open'))) ?></span>
              <span class="cms-pill"><?= (int) ($item['member_count'] ?? 0) ?> anggota</span>
              <span class="cms-pill"><?= count($submissions) ?> presensi</span>
            </div>
          </div>
        </div>
        <div class="mt-5">
          <p class="eyebrow">Role</p>
          <div class="mt-2 flex flex-wrap gap-2">
            <?php foreach ($roleOptions as $role): ?>
              <?php
                $roleName = (string) ($role['name'] ?? '');
                $roleScore = (int) ($role['score'] ?? 0);
              ?>
              <?php if ($roleName !== ''): ?>
                <span class="cms-pill"><?= $e($roleName) ?>: <?= $roleScore ?> poin</span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <aside class="admin-card p-5 md:p-6">
        <p class="eyebrow">Public Link</p>
        <div class="mt-3 presensi-public-link">
          <input class="config-input" readonly value="<?= $e($publicUrl) ?>">
          <button class="btn btn-secondary" type="button" data-copy-link="<?= $e($publicUrl) ?>">Copy</button>
        </div>
        <div class="mt-4 presensi-qr-box" data-presensi-qr="<?= $e($absoluteUrl) ?>" aria-label="QR presensi <?= $e($item['event_name'] ?? '') ?>"></div>
      </aside>
    </div>

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">List Presensi Anggota</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Daftar Kehadiran</h2>
        </div>
        <p class="text-sm font-semibold text-neutral-500"><?= count($members) ?> anggota event</p>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Role</th>
              <th>Skor</th>
              <th>Foto</th>
              <th>Waktu</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="presensi-submission-list" data-event-id="<?= (int) $item['id'] ?>">
            <?php if (empty($members) && empty($submissions)): ?>
              <tr><td colspan="7" class="text-center text-sm text-neutral-500">Belum ada anggota event.</td></tr>
            <?php else: ?>
              <?php foreach ($members as $member): ?>
                <?php
                  $memberId = (int) ($member['id'] ?? 0);
                  $submission = $memberId > 0 ? ($submissionsByTeamId[$memberId] ?? null) : null;
                ?>
                <?php if ($submission): ?>
                  <?php
                    $status = strtolower((string) ($submission['status'] ?? 'pending'));
                    $submissionRole = (string) ($submission['role'] ?? '');
                    $submissionScore = (int) ($roleScores[$submissionRole] ?? 0);
                    $submissionTime = $formatPresensiTime($submission['created_at'] ?? '');
                    $detailSubmission = array_merge($submission, ['role_score' => $submissionScore, 'created_at_label' => $submissionTime]);
                    $photoPayload = [
                      'url' => (string) ($submission['photo_url'] ?? ''),
                      'name' => (string) ($submission['member_name'] ?? $member['name'] ?? ''),
                    ];
                  ?>
                  <tr>
                    <td class="admin-cell-title"><strong><?= $e($submission['member_name'] ?? $member['name'] ?? '') ?></strong></td>
                    <td class="admin-cell-meta"><?= $e($submission['role'] ?? '') ?></td>
                    <td class="admin-cell-meta"><?= $submissionScore ?> poin</td>
                    <td class="admin-cell-meta">
                      <?php if (!empty($submission['photo_url'])): ?>
                        <button class="btn btn-outline btn-sm" type="button" data-presensi-photo='<?= $e(json_encode($photoPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') ?>'>Lihat Foto</button>
                      <?php endif; ?>
                    </td>
                    <td class="admin-cell-meta"><?= $e($submissionTime) ?></td>
                    <td class="admin-cell-status"><span class="cms-pill <?= $status === 'approved' ? 'cms-pill-green' : 'cms-pill-yellow' ?>"><?= $e(ucfirst($status)) ?></span></td>
                    <td class="admin-cell-actions">
                      <div class="admin-table-actions">
                        <button class="btn btn-outline btn-sm" type="button" data-presensi-detail='<?= $e(json_encode($detailSubmission, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') ?>'>Detail</button>
                        <?php if ($status !== 'approved'): ?>
                          <button class="btn btn-primary btn-sm" type="button" data-approve-presensi="<?= (int) ($submission['id'] ?? 0) ?>">Approve</button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <tr class="presensi-row-missing">
                    <td class="admin-cell-title"><strong><?= $e($member['name'] ?? '') ?></strong></td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-meta">0 poin</td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-status"><span class="cms-pill presensi-pill-missing">Belum Presensi</span></td>
                    <td class="admin-cell-actions">-</td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
              <?php foreach ($submissions as $submission): ?>
                <?php
                  $teamId = (int) ($submission['team_id'] ?? 0);
                  if ($teamId > 0 && isset($memberIds[$teamId])) {
                    continue;
                  }
                  $status = strtolower((string) ($submission['status'] ?? 'pending'));
                  $submissionRole = (string) ($submission['role'] ?? '');
                  $submissionScore = (int) ($roleScores[$submissionRole] ?? 0);
                  $submissionTime = $formatPresensiTime($submission['created_at'] ?? '');
                  $detailSubmission = array_merge($submission, ['role_score' => $submissionScore, 'created_at_label' => $submissionTime]);
                  $photoPayload = [
                    'url' => (string) ($submission['photo_url'] ?? ''),
                    'name' => (string) ($submission['member_name'] ?? ''),
                  ];
                ?>
                <tr>
                  <td class="admin-cell-title"><strong><?= $e($submission['member_name'] ?? '') ?></strong></td>
                  <td class="admin-cell-meta"><?= $e($submission['role'] ?? '') ?></td>
                  <td class="admin-cell-meta"><?= $submissionScore ?> poin</td>
                  <td class="admin-cell-meta">
                    <?php if (!empty($submission['photo_url'])): ?>
                      <button class="btn btn-outline btn-sm" type="button" data-presensi-photo='<?= $e(json_encode($photoPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') ?>'>Lihat Foto</button>
                    <?php endif; ?>
                  </td>
                  <td class="admin-cell-meta"><?= $e($submissionTime) ?></td>
                  <td class="admin-cell-status"><span class="cms-pill <?= $status === 'approved' ? 'cms-pill-green' : 'cms-pill-yellow' ?>"><?= $e(ucfirst($status)) ?></span></td>
                  <td class="admin-cell-actions">
                    <div class="admin-table-actions">
                      <button class="btn btn-outline btn-sm" type="button" data-presensi-detail='<?= $e(json_encode($detailSubmission, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') ?>'>Detail</button>
                      <?php if ($status !== 'approved'): ?>
                        <button class="btn btn-primary btn-sm" type="button" data-approve-presensi="<?= (int) ($submission['id'] ?? 0) ?>">Approve</button>
                      <?php endif; ?>
                    </div>
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
