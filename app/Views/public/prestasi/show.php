<?php $item = $item ?? null; ?>
<?php if (!$item): ?>
  <section class="bg-stone py-16">
    <div class="article-container text-sm text-neutral-600">Prestasi tidak ditemukan.</div>
  </section>
<?php else: ?>
  <?php $images = array_values(array_filter($item['images'] ?? [], static fn($image) => is_string($image) && $image !== '')); ?>
  <?php if (empty($images) && !empty($item['image'])) { $images = [(string) $item['image']]; } ?>
  <section class="public-inner-hero py-16 md:py-24">
    <div class="article-container">
      <a data-transition href="/prestasi" class="chip chip-dark mb-7">← Kembali ke Prestasi</a>
      <p class="eyebrow"><?= $e($item['category'] ?? 'Prestasi') ?> · <?= $e($item['year'] ?? '') ?></p>
      <h1 class="page-title mt-5"><?= $e($item['title'] ?? '') ?></h1>
      <?php if (!empty($item['description'])): ?>
        <p class="lead mt-7"><?= $e($item['description']) ?></p>
      <?php endif; ?>
    </div>
  </section>
  <section class="bg-cream py-12 md:py-16">
    <div class="article-container">
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main card -->
        <div class="lg:col-span-2">
          <?php if (!empty($images)): ?>
            <div class="soft-card overflow-hidden p-0 prestasi-gallery" data-prestasi-gallery>
              <div class="prestasi-gallery-strip" role="list" aria-label="Galeri foto prestasi">
                <?php foreach ($images as $index => $image): ?>
                  <button
                    type="button"
                    class="prestasi-gallery-card"
                    data-prestasi-thumb
                    data-image-src="<?= $e($image) ?>"
                    data-image-alt="<?= $e(($item['title'] ?? 'Prestasi') . ' foto ' . ($index + 1)) ?>"
                    aria-label="Buka foto prestasi <?= $index + 1 ?>"
                  >
                    <img src="<?= $e($image) ?>" alt="<?= $e(($item['title'] ?? 'Prestasi') . ' foto ' . ($index + 1)) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" />
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($item['content'])): ?>
            <div class="soft-card mt-6 p-6 md:p-8">
              <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500">Deskripsi</h2>
              <div class="prose-soft mt-4" id="prestasi-detail-root" data-ssr="true">
                <?= $item['content'] ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <!-- Sidebar info card -->
        <div class="flex flex-col gap-6">
          <div class="soft-card p-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500">Informasi</h2>
            <dl class="mt-4 grid gap-4">
              <div>
                <dt class="text-xs font-semibold text-neutral-500">Nama</dt>
                <dd class="mt-1 font-semibold text-neutral-950"><?= $e($item['name'] ?? '-') ?></dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-neutral-500">Peringkat</dt>
                <dd class="mt-1 font-semibold text-neutral-950"><?= $e($item['category'] ?? '-') ?></dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-neutral-500">Tahun</dt>
                <dd class="mt-1 font-semibold text-neutral-950"><?= $e($item['year'] ?? '-') ?></dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-neutral-500">Komisariat</dt>
                <dd class="mt-1 font-semibold text-neutral-950"><?= $e($item['campus'] ?? '-') ?></dd>
              </div>
              <?php if (!empty($item['institution'])): ?>
                <div>
                  <dt class="text-xs font-semibold text-neutral-500">Penyelenggara</dt>
                  <dd class="mt-1 font-semibold text-neutral-950"><?= $e($item['institution']) ?></dd>
                </div>
              <?php endif; ?>
            </dl>
          </div>
          <div class="soft-card p-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500">Bagikan</h2>
            <div class="mt-4 flex gap-3">
              <a href="https://wa.me/?text=<?= rawurlencode(($item['title'] ?? '') . ' ' . $seo['canonical'] ?? '') ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" aria-label="Bagikan ke WhatsApp">WhatsApp</a>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($seo['canonical'] ?? '') ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" aria-label="Bagikan ke Facebook">Facebook</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="prestasi-image-modal hidden" data-prestasi-image-modal aria-hidden="true">
    <button type="button" class="prestasi-image-modal-backdrop" data-prestasi-modal-close aria-label="Tutup preview foto"></button>
    <div class="prestasi-image-modal-panel" role="dialog" aria-modal="true" aria-label="Preview foto prestasi">
      <button type="button" class="prestasi-image-modal-close" data-prestasi-modal-close aria-label="Tutup preview foto">×</button>
      <img src="" alt="" data-prestasi-modal-image />
    </div>
  </div>
<?php endif; ?>
