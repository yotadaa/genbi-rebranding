<?php
$isEdit = $isEdit ?? false;
$item = $item ?? null;
$name = $isEdit ? ($item['name'] ?? '') : '';
$prestasiTitle = $isEdit ? ($item['title'] ?? '') : '';
$category = $isEdit ? ($item['category'] ?? '') : '';
$year = $isEdit ? ($item['year'] ?? (string) date('Y')) : (string) date('Y');
$description = $isEdit ? ($item['description'] ?? '') : '';
$content = $isEdit ? ($item['content'] ?? $item['detail'] ?? '') : '';
$image = $isEdit ? ($item['image'] ?? '') : '';
$institution = $isEdit ? ($item['institution'] ?? '') : '';
$status = $isEdit ? ($item['status'] ?? 'draft') : 'draft';
$metaTitle = $isEdit ? ($item['meta_title'] ?? '') : '';
$metaKeyword = $isEdit ? ($item['meta_keyword'] ?? '') : '';
$metaDesc = $isEdit ? ($item['meta_description'] ?? '') : '';
$itemId = $isEdit ? (int) ($item['id'] ?? 0) : 0;
$prestasiCategories = ['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', 'Harapan 2', 'Finalis', 'Peserta Terbaik'];
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit Prestasi' : 'Add Prestasi' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Kelola data prestasi anggota GenBI Jambi.</p>
    </div>
    <div class="cms-actions">
      <a href="/admin/prestasi" class="btn btn-secondary">View All</a>
    </div>
  </header>
  <div id="cms-body" class="mt-6">
    <form class="prestasi-editor-form" id="prestasi-editor-form" data-ssr="true" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>">
      <div class="editor-config-sidebar prestasi-config-panel">
        <section class="config-card medium-config-card">
          <h2>Informasi Prestasi</h2>
          <div class="config-field">
            <span>Nama</span>
            <input class="config-input" id="prestasi-member-search" list="prestasi-member-list" value="<?= $e($name) ?>" placeholder="Cari nama anggota..." autocomplete="off" />
            <datalist id="prestasi-member-list"></datalist>
          </div>
          <div class="config-field">
            <span>Penyelenggara</span>
            <input class="config-input" id="prestasi-institution" value="<?= $e($institution) ?>" placeholder="Nama penyelenggara" />
          </div>
          <div class="config-field">
            <span>Tahun</span>
            <input class="config-input" id="prestasi-year" type="number" min="1900" max="2099" step="1" value="<?= $e($year) ?>" placeholder="<?= date('Y') ?>" />
          </div>
          <div class="config-field">
            <span>Peringkat</span>
            <input class="config-input" id="prestasi-category" value="<?= $e($category) ?>" list="prestasi-rank-list" placeholder="Contoh: Juara 1" />
            <datalist id="prestasi-rank-list">
              <?php foreach ($prestasiCategories as $cat): ?>
                <option value="<?= $e($cat) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Deskripsi</h2>
          <div class="config-field">
            <span>Deskripsi Singkat</span>
            <textarea class="config-input" id="prestasi-desc-field" rows="5" placeholder="Tulis deskripsi singkat prestasi..."><?= $e($description) ?></textarea>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Detail Prestasi</h2>
          <div id="prestasi-editor" class="medium-editor-host"></div>
          <div id="prestasi-editor-fallback" class="editor-fallback <?= empty($content) ? '' : '' ?>">
            <textarea class="config-input" id="prestasi-content-field" rows="8" placeholder="Tulis detail prestasi..."><?= $e($content) ?></textarea>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Foto Prestasi</h2>
          <?php if (!empty($image)): ?>
            <img src="<?= $e($image) ?>" class="config-preview rounded" alt="Foto prestasi" />
          <?php else: ?>
            <div class="config-empty">Belum ada foto</div>
          <?php endif; ?>
          <input id="prestasi-image-file" class="hidden" type="file" accept="image/*" />
          <button type="button" id="prestasi-upload-btn" class="btn btn-secondary w-full mt-2">Upload Foto</button>
          <input class="config-input mt-2" id="prestasi-image-url" value="<?= $e($image) ?>" placeholder="URL gambar (opsional)" />
        </section>
        <section class="config-card medium-config-card">
          <h2>SEO Information</h2>
          <div class="config-field">
            <span>Meta Title</span>
            <input class="config-input" id="prestasi-meta-title" value="<?= $e($metaTitle ?: $prestasiTitle) ?>" />
          </div>
          <div class="config-field">
            <span>Meta Keywords</span>
            <textarea class="config-input" id="prestasi-meta-keyword" rows="3"><?= $e($metaKeyword ?: ($category ? $category . ', GenBI Jambi, prestasi' : '')) ?></textarea>
          </div>
          <div class="config-field">
            <span>Meta Description</span>
            <textarea class="config-input" id="prestasi-meta-desc" rows="4"><?= $e($metaDesc ?: $description) ?></textarea>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Status</h2>
          <div class="config-field">
            <span>Publish Status</span>
            <select class="config-input js-admin-custom-select" id="prestasi-status">
              <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </section>
        <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Prestasi' : 'Submit Prestasi' ?></button>
      </div>
    </form>
  </div>
</section>
