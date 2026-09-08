<?php
$isEdit = $isEdit ?? false;
$item = $item ?? null;
$itemId = $isEdit ? (int) ($item['id'] ?? 0) : 0;
$title = $isEdit ? ($item['title'] ?? '') : '';
$name = $isEdit ? ($item['name'] ?? '') : '';
$focus = $isEdit ? ($item['focus'] ?? '') : '';
$description = $isEdit ? ($item['description'] ?? '') : '';
$iconKey = $isEdit ? ($item['icon_key'] ?? 'sparkles') : 'sparkles';
$showOnHome = $isEdit ? !empty($item['show_on_home']) : true;
$status = $isEdit ? ($item['status'] ?? 'draft') : 'draft';
$sortOrder = $isEdit ? (int) ($item['sort_order'] ?? 0) : 0;
$images = $isEdit ? ($item['images'] ?? []) : [];
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit Program Utama' : 'Add Program Utama' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Program Utama ditulis seperti section hero ringkas: label pendek, nama program, deskripsi, ikon, dan rangkaian gambar latar.</p>
    </div>
    <div class="cms-actions">
      <a href="<?= $url('admin.feature') ?>" class="btn btn-secondary">View All</a>
    </div>
  </header>
  <div id="cms-body" class="mt-6">
    <form class="feature-editor-form" id="feature-editor-form" data-ssr="true" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>">
      <div class="feature-editor-main">
        <section class="feature-story-card">
          <label class="feature-label" for="feature-title">Label Singkat</label>
          <input id="feature-title" class="story-title-input" value="<?= $e($title) ?>" placeholder="Contoh: KKG" maxlength="120" />
          <label class="feature-label mt-5" for="feature-name">Nama Program</label>
          <input id="feature-name" class="story-name-input" value="<?= $e($name) ?>" placeholder="Nama Program Utama..." maxlength="255" />
          <div class="feature-grid mt-6">
            <div class="config-field">
              <span>Fokus</span>
              <input id="feature-focus" class="config-input" value="<?= $e($focus) ?>" placeholder="Contoh: Literasi digital" maxlength="120" />
            </div>
            <div class="config-field">
              <span>Urutan</span>
              <input id="feature-sort-order" class="config-input" type="number" min="0" step="1" value="<?= $sortOrder ?>" />
            </div>
          </div>
          <label class="feature-label mt-6" for="feature-description">Deskripsi</label>
          <textarea id="feature-description" class="news-body-block smaller" rows="7" placeholder="Manfaat program untuk anggota dan masyarakat..."><?= $e($description) ?></textarea>
        </section>

        <section class="feature-media-card">
          <div class="feature-media-head">
            <div>
              <p class="eyebrow">Slideshow Images</p>
              <h2>Visual Program Utama</h2>
              <p>Upload lebih dari satu gambar untuk background slideshow kartu di landing page.</p>
            </div>
            <div class="flex gap-2">
              <input id="feature-image-files" class="hidden" type="file" accept="image/*" multiple />
              <button type="button" class="btn btn-secondary" id="feature-upload-btn">Upload Gambar</button>
            </div>
          </div>
          <div class="feature-image-board" id="feature-image-board">
            <?php if ($images !== []): ?>
              <?php foreach ($images as $index => $image): ?>
                <article class="feature-image-card" data-image-id="<?= (int) ($image['id'] ?? 0) ?>" data-image-path="<?= $e($image['path'] ?? $image['url'] ?? '') ?>">
                  <img src="<?= $e($image['url'] ?? '') ?>" alt="<?= $e($name ?: 'Program Utama') ?>" />
                  <div class="feature-image-card-meta">
                    <span>#<?= $index + 1 ?></span>
                    <div class="flex gap-2">
                      <button type="button" class="feature-image-move" data-direction="up" aria-label="Geser ke atas">↑</button>
                      <button type="button" class="feature-image-move" data-direction="down" aria-label="Geser ke bawah">↓</button>
                      <button type="button" class="feature-image-remove" aria-label="Hapus gambar">Hapus</button>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="feature-image-empty" id="feature-image-empty">Belum ada gambar. Upload minimal satu gambar agar kartu tampil hidup di landing page.</div>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <aside class="editor-config-sidebar feature-config-sidebar">
        <section class="config-card medium-config-card">
          <h2>Ikon Program</h2>
          <div class="feature-icon-picker" id="feature-icon-picker" data-selected-icon="<?= $e($iconKey) ?>">
            <button type="button" class="feature-icon-button" id="feature-icon-button">
              <span class="feature-icon-button-preview" data-feature-icon-preview="<?= $e($iconKey) ?>"></span>
              <span>
                <strong id="feature-icon-label"><?= $e($iconKey) ?></strong>
                <small>Buka popup Heroicons tersegmentasi</small>
              </span>
            </button>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Visibility</h2>
          <div class="config-field">
            <span>Tampil di Beranda</span>
            <select class="config-input js-admin-custom-select" id="feature-show-home">
              <option value="1" <?= $showOnHome ? 'selected' : '' ?>>Show</option>
              <option value="0" <?= !$showOnHome ? 'selected' : '' ?>>Hide</option>
            </select>
          </div>
          <div class="config-field">
            <span>Status</span>
            <select class="config-input js-admin-custom-select" id="feature-status">
              <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </section>
        <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Program Utama' : 'Submit Program Utama' ?></button>
      </aside>
    </form>
  </div>
</section>
