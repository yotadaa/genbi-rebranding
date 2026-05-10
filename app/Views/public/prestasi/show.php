<?php $item = $item ?? null; ?>
<?php if (!$item): ?>
  <section class="bg-stone py-16">
    <div class="article-container text-sm text-neutral-600">Prestasi tidak ditemukan.</div>
  </section>
<?php else: ?>
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
          <?php if (!empty($item['image'])): ?>
            <div class="soft-card overflow-hidden p-0">
              <img src="<?= $e($item['image']) ?>" alt="<?= $e($item['title'] ?? '') ?>" class="w-full rounded-2xl object-cover" loading="eager" />
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
<?php endif; ?>
