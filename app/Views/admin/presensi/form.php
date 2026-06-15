<?php
$isEdit = $isEdit ?? false;
$item = $item ?? null;
$itemId = $isEdit ? (int) ($item['id'] ?? 0) : 0;
$eventName = $isEdit ? (string) ($item['event_name'] ?? '') : '';
$location = $isEdit ? (string) ($item['location'] ?? '') : '';
$roleOptions = $isEdit && is_array($item['role_options'] ?? null)
  ? $item['role_options']
  : array_map(static fn($role): array => ['name' => (string) $role, 'score' => 0], $isEdit && is_array($item['roles'] ?? null) ? $item['roles'] : []);
$members = $isEdit && is_array($item['members'] ?? null) ? $item['members'] : [];
$status = $isEdit ? (string) ($item['status'] ?? 'open') : 'open';
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit Presensi' : 'Add Presensi' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Buat event presensi dengan role dinamis dan anggota dari data team.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.presensi') ?>" class="btn btn-secondary">View All</a>
      <?php if ($isEdit): ?>
        <a href="<?= $url('admin.presensi.show', ['id' => $itemId]) ?>" class="btn btn-outline">Detail</a>
      <?php endif; ?>
    </div>
  </header>

  <div id="cms-body" class="mt-6">
    <form class="presensi-editor-form" id="presensi-editor-form" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>">
      <div class="editor-config-sidebar prestasi-config-panel">
        <section class="config-card medium-config-card">
          <h2>Informasi Event</h2>
          <div class="config-field">
            <span>Nama Event</span>
            <input class="config-input" id="presensi-event-name" value="<?= $e($eventName) ?>" maxlength="255" required>
          </div>
          <div class="config-field">
            <span>Lokasi</span>
            <input class="config-input" id="presensi-location" value="<?= $e($location) ?>" maxlength="255" required>
          </div>
          <div class="config-field">
            <span>Status</span>
            <select class="config-input js-admin-custom-select" id="presensi-status">
              <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
              <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
              <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </section>

        <section class="config-card medium-config-card">
          <h2>Role Event</h2>
          <div class="presensi-inline-control">
            <input class="config-input" id="presensi-role-input" maxlength="120" placeholder="Panitia">
            <input class="config-input presensi-score-input" id="presensi-role-score" type="number" min="0" max="100000" step="1" placeholder="Skor">
            <button type="button" class="btn btn-secondary" id="presensi-role-add">Add</button>
          </div>
          <input type="hidden" id="presensi-roles-json" value="<?= $e(json_encode(array_values($roleOptions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]') ?>">
          <div class="presensi-chip-list" id="presensi-role-list"></div>
        </section>

        <section class="config-card medium-config-card">
          <h2>Anggota Event</h2>
          <div class="presensi-member-control">
            <div class="presensi-member-actions">
              <button type="button" class="btn btn-secondary" id="presensi-member-modal-open">
                <span class="presensi-btn-icon" aria-hidden="true">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5c0-2.07-2.69-3.75-6-3.75s-6 1.68-6 3.75M9 12.75A3.75 3.75 0 1 0 9 5.25a3.75 3.75 0 0 0 0 7.5Zm12 6.75c0-1.74-1.9-3.2-4.45-3.62M15 5.58a3.75 3.75 0 0 1 0 6.84"/></svg>
                </span>
                Pilih dari daftar
              </button>
            </div>
            <div class="presensi-autocomplete" data-presensi-member-picker>
              <input class="config-input" id="presensi-member-search" placeholder="Cari nama anggota..." autocomplete="off">
              <div class="presensi-suggestions hidden" id="presensi-member-suggestions"></div>
            </div>
          </div>
          <input type="hidden" id="presensi-members-json" value="<?= $e(json_encode(array_values($members), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]') ?>">
          <div class="presensi-member-list" id="presensi-member-list"></div>
        </section>

        <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Presensi' : 'Submit Presensi' ?></button>
        <p class="config-hint" id="presensi-form-status" role="status"></p>
      </div>
    </form>
  </div>
</section>
