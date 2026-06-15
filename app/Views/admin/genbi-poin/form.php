<?php
$isEdit = $isEdit ?? false;
$item = $item ?? null;
$itemId = $isEdit ? (int) ($item['id'] ?? 0) : 0;
$teamId = $isEdit ? (int) ($item['team_id'] ?? 0) : 0;
$memberName = $isEdit ? (string) ($item['member_name'] ?? '') : '';
$activityName = $isEdit ? (string) ($item['activity_name'] ?? '') : '';
$points = $isEdit ? (int) ($item['points'] ?? 0) : 0;
$activityDate = $isEdit ? (string) ($item['activity_date'] ?? '') : '';
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit Aktivitas Poin' : 'Tambah Aktivitas Poin' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Tambahkan poin manual anggota di luar poin presensi event.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.genbiPoin') ?>" class="btn btn-secondary">View All</a>
    </div>
  </header>

  <div id="cms-body" class="mt-6">
    <form class="presensi-editor-form" id="genbi-point-form" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>">
      <div class="editor-config-sidebar prestasi-config-panel">
        <section class="config-card medium-config-card">
          <h2>Data Aktivitas</h2>
          <div class="config-field">
            <span>Nama</span>
            <div class="presensi-autocomplete" data-genbi-point-member-picker>
              <input class="config-input" id="genbi-point-member-search" value="<?= $e($memberName) ?>" placeholder="Cari nama anggota..." autocomplete="off" required>
              <input type="hidden" id="genbi-point-team-id" value="<?= $teamId ?>">
              <div class="presensi-suggestions hidden" id="genbi-point-member-suggestions"></div>
            </div>
          </div>
          <div class="config-field">
            <span>Nama Kegiatan</span>
            <input class="config-input" id="genbi-point-activity-name" value="<?= $e($activityName) ?>" maxlength="255" placeholder="Contoh: Fasilitator Edukasi BI" required>
          </div>
          <div class="config-field">
            <span>Jumlah Poin</span>
            <input class="config-input" id="genbi-point-amount" type="number" min="0" max="100000" step="1" value="<?= $points ?>" required>
          </div>
          <div class="config-field">
            <span>Tanggal</span>
            <input class="config-input" id="genbi-point-date" type="date" value="<?= $e($activityDate) ?>">
          </div>
        </section>

        <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Aktivitas' : 'Simpan Aktivitas' ?></button>
        <p class="config-hint" id="genbi-point-form-status" role="status"></p>
      </div>
    </form>
  </div>
</section>
