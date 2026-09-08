<?php
$token = $token ?? '';
$tokenLabel = $tokenLabel ?? '';
$years = $years ?? [];
$categories = ['QRIS', 'KTI', 'Essay', 'Inovasi Desa', 'Kreativitas', 'Ekonomi Syariah'];
$campuses = ['Universitas Jambi', 'UIN Sultan Thaha', 'Alumni'];
?>
<section id="prestasi-submit-root" data-ssr="true">
  <section class="prestasi-submit-hero bg-stone py-14 md:py-20">
    <div class="site-container fade-up">
      <div class="mx-auto max-w-2xl text-center">
        <p class="eyebrow">Form Prestasi</p>
        <h1 class="page-title mt-5">Pengajuan Prestasi</h1>
        <?php if ($tokenLabel !== ''): ?>
          <p class="mt-4 text-sm text-neutral-600">Token: <strong><?= $e($tokenLabel) ?></strong></p>
        <?php endif; ?>
        <p class="lead mt-5">Isi form di bawah untuk mengajukan prestasi. Form ini hanya bisa digunakan sekali.</p>
      </div>
    </div>
  </section>

  <section class="prestasi-submit-body bg-cream py-12 md:py-16">
    <div class="site-container">
      <form id="prestasi-submit-form" data-ssr="true" data-token="<?= $e($token) ?>" class="mx-auto max-w-2xl" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken ?? '') ?>">

        <div class="soft-card p-6 md:p-8">
          <h2 class="text-lg font-bold text-neutral-950">Informasi Prestasi</h2>
          <div class="mt-6 grid gap-4">
            <label class="config-field">
              <span>Judul Prestasi <span class="text-red-500">*</span></span>
              <input class="input-soft" name="title" placeholder="Nama prestasi yang diraih" required>
            </label>
            <label class="config-field">
              <span>Kategori <span class="text-red-500">*</span></span>
              <input class="input-soft" name="category" list="prestasi-category-options" placeholder="Tulis kategori atau pilih rekomendasi" required>
              <datalist id="prestasi-category-options">
                <?php foreach ($categories as $category): ?><option value="<?= $e($category) ?>"><?php endforeach; ?>
              </datalist>
              <p class="config-hint mt-2">Bisa isi kategori baru jika tidak ada di pilihan rekomendasi.</p>
            </label>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="config-field">
                <span>Tahun <span class="text-red-500">*</span></span>
                <select class="input-soft js-custom-select" name="year" required>
                  <?php foreach ($years as $year): ?><option value="<?= (int) $year ?>"><?= (int) $year ?></option><?php endforeach; ?>
                </select>
              </label>
              <label class="config-field">
                <span>Komisariat <span class="text-red-500">*</span></span>
                <select class="input-soft js-custom-select" name="campus" required>
                  <?php foreach ($campuses as $campus): ?><option value="<?= $e($campus) ?>"><?= $e($campus) ?></option><?php endforeach; ?>
                </select>
              </label>
            </div>
          </div>
        </div>

        <div class="soft-card mt-6 p-6 md:p-8">
          <h2 class="text-lg font-bold text-neutral-950">Informasi Anggota</h2>
          <div class="mt-6 grid gap-4">
            <label class="config-field"><span>Nama Anggota <span class="text-red-500">*</span></span><input class="input-soft" name="name" placeholder="Nama lengkap penerima prestasi" required></label>
            <label class="config-field"><span>Institusi (opsional)</span><input class="input-soft" name="institution" placeholder="Nama institusi penyelenggara"></label>
          </div>
        </div>

        <div class="soft-card mt-6 p-6 md:p-8">
          <h2 class="text-lg font-bold text-neutral-950">Deskripsi</h2>
          <div class="mt-6 grid gap-4">
            <label class="config-field"><span>Deskripsi Singkat</span><textarea class="input-soft" name="description" rows="3" placeholder="Ringkasan singkat prestasi (maks 500 karakter)"></textarea></label>
            <label class="config-field"><span>Detail Prestasi</span><textarea class="input-soft" name="content" rows="6" placeholder="Ceritakan detail prestasi yang diraih..."></textarea></label>
          </div>
        </div>

        <div class="soft-card mt-6 p-6 md:p-8">
          <h2 class="text-lg font-bold text-neutral-950">Foto Prestasi</h2>
          <div class="mt-6 grid gap-4">
            <div class="public-upload-field public-prestasi-photo-card prestasi-photo-uploader" data-public-upload-field>
              <input id="prestasi-photo-input" class="hidden" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
              <div class="public-upload-empty" id="prestasi-photo-empty"><strong>Belum ada foto</strong></div>
              <button type="button" class="btn btn-secondary w-full" id="prestasi-photo-trigger">Upload Foto</button>
              <input class="input-soft" id="prestasi-photo-url" name="image_url" placeholder="URL gambar (opsional)">
              <p id="prestasi-photo-gallery-status" class="text-sm text-neutral-500">Belum ada galeri tambahan.</p>
              <div class="public-upload-list hidden" id="prestasi-photo-list" aria-live="polite"></div>
              <div class="public-upload-preview hidden" id="prestasi-photo-preview">
                <div class="public-upload-preview-controls"><strong>Preview foto terpilih</strong><span id="prestasi-photo-counter">1 / 1</span></div>
                <div class="public-upload-preview-slider">
                  <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-left" aria-label="Geser preview ke kiri">‹</button>
                  <div class="public-upload-preview-strip" id="prestasi-photo-preview-strip" aria-label="Preview foto terpilih"></div>
                  <button type="button" class="public-upload-scroll" id="prestasi-photo-scroll-right" aria-label="Geser preview ke kanan">›</button>
                </div>
              </div>
              <div class="public-upload-actions"><button type="button" class="chip chip-dark hidden" id="prestasi-photo-clear">Hapus Semua</button></div>
            </div>
            <p class="text-sm text-neutral-500">Bisa unggah 1 atau lebih foto. Maksimal 6 foto, masing-masing maksimal 5MB. Format: JPG, PNG, WebP, atau GIF.</p>
          </div>
        </div>

        <div class="mt-8 flex items-center justify-between">
          <p class="text-sm text-neutral-500">Form hanya bisa disubmit sekali.</p>
          <button type="submit" class="btn btn-primary">Submit Prestasi</button>
        </div>
      </form>
    </div>
  </section>
</section>
