<?php
$isEdit = $isEdit ?? false;
$item = $item ?? null;
$categories = $categories ?? [];
$title = $isEdit ? ($item['title'] ?? 'Edit News') : '';
$excerpt = $isEdit ? ($item['excerpt'] ?? '') : '';
$content = $isEdit ? ($item['content'] ?? '') : '';
$date = $isEdit ? substr((string) ($item['date'] ?? $item['published_at'] ?? ''), 0, 10) : date('Y-m-d');
$categoryId = $isEdit ? ($item['category_id'] ?? 0) : 0;
$photo = $isEdit ? ($item['photo'] ?? '') : '';
$banner = $isEdit ? ($item['banner'] ?? '') : '';
$pewarta = $isEdit ? ($item['contributor_pewarta'] ?? '') : '';
$editor = $isEdit ? ($item['contributor_editor'] ?? '') : '';
$metaTitle = $isEdit ? ($item['meta_title'] ?? '') : '';
$metaKeyword = $isEdit ? ($item['meta_keyword'] ?? '') : '';
$metaDesc = $isEdit ? ($item['meta_description'] ?? '') : '';
$status = $isEdit ? ($item['status'] ?? 'draft') : 'draft';
$itemId = $isEdit ? (int) ($item['id'] ?? 0) : 0;
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3"><?= $isEdit ? 'Edit News' : 'Add News' ?></h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Ruang tulis memakai Editor.js. Judul, paragraf, quote, list, dan gambar disusun sebagai blok.</p>
    </div>
    <div class="cms-actions">
      <a href="/admin/news" class="btn btn-secondary">View All</a>
    </div>
  </header>
  <div id="cms-body" class="mt-6">
    <form class="medium-editor-layout" id="news-editor-form" data-ssr="true" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>">
      <main class="medium-editor-canvas">
        <div class="medium-editor-kicker">Story editor</div>
        <section class="story-main-block">
          <label for="news-title-field">News Title</label>
          <div id="news-title-field" class="story-title-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis judul berita..."><?= $e($title) ?></div>
        </section>
        <section class="story-main-block">
          <label for="news-short-content-field">News Short Content</label>
          <div id="news-short-content-field" class="story-excerpt-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis caption atau ringkasan singkat untuk news list..."><?= $e($excerpt) ?></div>
        </section>
        <div class="medium-editor-divider">
          <div class="medium-editor-kicker">News content</div>
        </div>
        <div id="news-editor" class="medium-editor-host"></div>
        <div id="editor-fallback" class="editor-fallback hidden">
          <article contenteditable="true"><?= $content ?></article>
        </div>
        <p class="medium-editor-help">Tekan <strong>Enter</strong> untuk membuat blok baru. Sisipkan gambar di antara paragraf lewat tombol plus Editor.js atau panel kanan.</p>
      </main>
      <aside class="editor-config-sidebar medium-config-sidebar">
        <section class="config-card medium-config-card">
          <h2>Quick Insert</h2>
          <p class="config-hint">Sisipkan gambar sebagai blok konten, bukan hanya featured image.</p>
          <button type="button" id="insert-paragraph" class="btn btn-secondary w-full">Tambah Paragraf</button>
          <button type="button" id="insert-heading" class="btn btn-secondary w-full mt-2">Tambah Heading</button>
          <button type="button" id="insert-quote" class="btn btn-secondary w-full mt-2">Tambah Quote</button>
          <label class="config-field mt-4"><span>Image URL</span><input id="inline-image-url" class="config-input" placeholder="https://..." /></label>
          <button type="button" id="insert-image-url" class="btn btn-primary w-full mt-2">Insert Image URL</button>
          <input id="inline-image-file" class="hidden" type="file" accept="image/*" />
          <button type="button" id="insert-image-file" class="btn btn-secondary w-full mt-2">Upload Image Block</button>
          <p id="news-inline-upload-status" class="config-hint mt-3 hidden">Mengupload gambar...</p>
        </section>
        <section class="config-card medium-config-card">
          <h2>Publishing</h2>
          <div class="config-field"><span>News Publish Date</span><input class="config-input" type="date" value="<?= $e($date) ?>" /></div>
          <div class="config-field"><span>Category</span>
            <select class="config-input js-admin-custom-select" id="news-category-select">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['category_id'] ?>" <?= (int) $cat['category_id'] === (int) $categoryId ? 'selected' : '' ?>><?= $e($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="config-field"><span>Comment</span>
            <select class="config-input js-admin-custom-select" id="news-comment-select">
              <option value="On" selected>On</option>
              <option value="Off">Off</option>
            </select>
          </div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Photo and Banner</h2>
          <?php if (!empty($photo)): ?>
            <img src="<?= $e($photo) ?>" class="config-preview" alt="Featured photo" />
          <?php else: ?>
            <div class="config-empty">Belum ada foto utama</div>
          <?php endif; ?>
          <input id="news-photo-file" class="hidden" type="file" accept="image/*" />
          <button type="button" id="news-photo-upload-btn" class="btn btn-secondary w-full mt-2">Upload Featured Photo</button>
          <input class="config-input mt-2" id="news-photo-url" value="<?= $e($photo) ?>" placeholder="URL foto utama" />
          <input id="news-banner-file" class="hidden" type="file" accept="image/*" />
          <button type="button" id="news-banner-upload-btn" class="btn btn-secondary w-full mt-2">Upload Banner</button>
          <input class="config-input mt-2" id="news-banner-url" value="<?= $e($banner) ?>" placeholder="URL banner" />
        </section>
        <section class="config-card medium-config-card">
          <h2>Contributors</h2>
          <div class="config-field"><span>Pewarta</span><input class="config-input" id="news-pewarta" value="<?= $e($pewarta) ?>" /></div>
          <div class="config-field"><span>Editor</span><input class="config-input" id="news-editor-name" value="<?= $e($editor) ?>" /></div>
        </section>
        <section class="config-card medium-config-card">
          <h2>SEO Information</h2>
          <div class="config-field"><span>Meta Title</span><input class="config-input" id="news-meta-title" value="<?= $e($metaTitle ?: $title) ?>" /></div>
          <div class="config-field"><span>Meta Keywords</span><textarea class="config-input" id="news-meta-keyword" rows="4"><?= $e($metaKeyword) ?></textarea></div>
          <div class="config-field"><span>Meta Description</span><textarea class="config-input" id="news-meta-desc" rows="5"><?= $e($metaDesc ?: $excerpt) ?></textarea></div>
        </section>
        <section class="config-card medium-config-card">
          <h2>Status</h2>
          <div class="config-field"><span>Publish Status</span>
            <select class="config-input js-admin-custom-select" id="news-status">
              <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </section>
        <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update News' : 'Submit News' ?></button>
      </aside>
    </form>
  </div>
</section>
