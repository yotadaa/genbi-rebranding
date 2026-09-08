<?php

/**
 * @var callable $e
 * @var callable $url
 */
$isEdit = $isEdit ?? false;
$item = $item ?? [];
$options = $options ?? ['divisions' => [], 'commissions' => []];
$itemId = (int) ($item['id'] ?? 0);
$itemJson = json_encode($item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit Team Member' : 'Add Team Member' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Data anggota tersedia pada HTML awal; upload dan simpan hanya menambahkan interaksi di atas form ini.</p>
    </div>
    <a href="/admin/team-member" class="btn btn-secondary">View All</a>
  </header>
  <form class="editor-workspace compact mt-6" id="team-form" data-ssr="true" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>" data-item="<?= $e($itemJson) ?>">
    <main class="block-writing-surface">
      <label class="config-field"><span>Nama Anggota</span><input id="team-name" class="config-input" value="<?= $e($item['name'] ?? '') ?>" placeholder="Nama anggota..." required></label>
      <div class="team-form-grid">
        <label class="config-field"><span>Jabatan</span><input id="team-designation" class="config-input" value="<?= $e($item['role'] ?? $item['designation'] ?? '') ?>" required></label>
        <label class="config-field"><span>Komisariat</span><select id="team-komsat-id" class="config-input js-admin-custom-select">
            <option value="">Pilih Komisariat</option><?php foreach (($options['commissions'] ?? []) as $commission): ?><option value="<?= (int) $commission['id'] ?>" <?= (int) ($item['komsat_id'] ?? 0) === (int) $commission['id'] ? 'selected' : '' ?>><?= $e($commission['nama'] ?? '') ?></option><?php endforeach; ?>
          </select></label>
        <label class="config-field"><span>Divisi</span><select id="team-divisi-id" class="config-input js-admin-custom-select">
            <option value="">Pilih Divisi</option><?php foreach (($options['divisions'] ?? []) as $division): ?><option value="<?= (int) $division['id'] ?>" <?= (int) ($item['divisi_id'] ?? $item['division_id'] ?? 0) === (int) $division['id'] ? 'selected' : '' ?>><?= $e($division['nama'] ?? '') ?></option><?php endforeach; ?>
          </select></label>
        <label class="config-field"><span>Tahun</span><input id="team-year" class="config-input" type="number" value="<?= $e($item['year'] ?? $item['tahun'] ?? date('Y')) ?>" required></label>
        <label class="config-field"><span>Email</span><input id="team-email" class="config-input" type="email" value="<?= $e($item['email'] ?? '') ?>"></label>
        <label class="config-field"><span>Phone</span><input id="team-phone" class="config-input" value="<?= $e($item['phone'] ?? '') ?>"></label>
      </div>
      <label class="config-field"><span>Deskripsi / Bio</span><textarea id="team-detail" class="news-body-block smaller" rows="8" placeholder="Description / bio singkat anggota..."><?= $e($item['detail'] ?? $item['bio'] ?? '') ?></textarea></label>
    </main>
    <aside class="editor-config-sidebar">
      <section class="config-card">
        <h2>Photo</h2>
        <div class="member-preview-avatar" id="team-photo-preview"><?php if (!empty($item['photo'])): ?><img src="<?= $e($item['photo']) ?>" alt="<?= $e($item['name'] ?? '') ?>"><?php else: ?><?= $e(mb_substr((string) ($item['name'] ?? 'Member'), 0, 2)) ?><?php endif; ?></div><label class="config-field"><span>Photo URL</span><input id="team-photo" class="config-input" value="<?= $e($item['photo_raw'] ?? $item['photo'] ?? '') ?>"></label><label class="config-field"><span>Upload Photo</span><input class="config-input" id="team-photo-upload" type="file" accept="image/*"></label>
      </section>
      <section class="config-card">
        <h2>Visibility</h2><label class="config-field"><span>Tampilkan di Beranda</span><select id="team-show-home" class="config-input js-admin-custom-select">
            <option value="1" <?= !empty($item['show_on_home']) ? 'selected' : '' ?>>Show</option>
            <option value="0" <?= empty($item['show_on_home']) ? 'selected' : '' ?>>Hide</option>
          </select></label>
      </section>
      <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Member' : 'Submit Member' ?></button>
    </aside>
  </form>
</section>