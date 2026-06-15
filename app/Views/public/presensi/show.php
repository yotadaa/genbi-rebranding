<?php
$event = $event ?? [];
$roles = is_array($event['roles'] ?? null) ? $event['roles'] : [];
$roleOptions = is_array($event['role_options'] ?? null)
  ? $event['role_options']
  : array_map(static fn($role): array => ['name' => (string) $role, 'score' => 0], $roles);
?>
<section class="presensi-public-page bg-white">
  <div class="presensi-public-container">
    <form id="public-presensi-form" class="presensi-public-card" data-token="<?= $e($token ?? '') ?>" enctype="multipart/form-data">
      <header class="presensi-public-head">
        <p class="eyebrow">Presensi Event</p>
        <h1><?= $e($event['event_name'] ?? 'Presensi GenBI') ?></h1>
        <?php if (!empty($event['location'])): ?>
          <p><?= $e($event['location'] ?? '') ?></p>
        <?php endif; ?>
      </header>

      <div class="config-field">
        <span>Nama</span>
        <div class="presensi-autocomplete" data-public-member-picker>
          <input class="config-input" id="public-presensi-name" placeholder="Cari nama anggota..." autocomplete="off" required>
          <input type="hidden" id="public-presensi-team-id" name="team_id">
          <div class="presensi-suggestions hidden" id="public-presensi-suggestions"></div>
        </div>
      </div>

      <div class="config-field">
        <span>Role</span>
        <select class="config-input js-custom-select" id="public-presensi-role" name="role" required>
          <option value="">Pilih role</option>
          <?php foreach ($roleOptions as $role): ?>
            <?php
              $roleName = (string) ($role['name'] ?? '');
              $roleScore = (int) ($role['score'] ?? 0);
            ?>
            <?php if ($roleName !== ''): ?>
              <option value="<?= $e($roleName) ?>"><?= $e($roleName . ($roleScore > 0 ? ' (' . $roleScore . ' poin)' : '')) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="config-field">
        <span>Bukti Foto</span>
        <input class="config-input" id="public-presensi-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" capture="environment" required>
      </div>

      <button type="submit" class="btn btn-primary w-full">Submit Presensi</button>
      <p id="public-presensi-status" class="config-hint" role="status"></p>
    </form>
  </div>
</section>
