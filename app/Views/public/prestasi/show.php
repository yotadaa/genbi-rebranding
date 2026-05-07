<?php $item = $item ?? null; ?>
<?php if (!$item): ?>
  <section class="bg-stone py-16">
    <div class="article-container text-sm text-neutral-600">Prestasi tidak ditemukan.</div>
  </section>
<?php else: ?>
  <section class="bg-stone py-16 md:py-24">
    <div class="article-container fade-up">
      <a data-transition href="/prestasi" class="chip chip-dark mb-7">← Kembali ke Prestasi</a>
      <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-blue-800">
        <span><?= $e($item['category'] ?? '') ?></span><span>•</span><span><?= $e($item['year'] ?? '') ?></span>
      </div>
      <h1 class="page-title mt-5"><?= $e($item['title'] ?? '') ?></h1>
      <p class="lead mt-7"><?= $e($item['description'] ?? '') ?></p>
    </div>
  </section>
  <section class="bg-cream py-12 md:py-16">
    <div class="article-container fade-up">
      <?php if (!empty($item['image'])): ?>
        <figure class="mb-8 overflow-hidden rounded-2xl">
          <img src="<?= $e($item['image']) ?>" alt="<?= $e($item['title'] ?? '') ?>" class="w-full object-cover" loading="lazy" />
        </figure>
      <?php endif; ?>
      <div class="prose-soft" id="prestasi-detail-root" data-ssr="true">
        <?php if (!empty($item['content'])): ?>
          <?= $item['content'] ?>
        <?php elseif (!empty($item['description'])): ?>
          <p><?= $e($item['description']) ?></p>
        <?php endif; ?>
      </div>
      <div class="mt-10 rounded-[1.5rem] border border-neutral-900/10 bg-white/80 p-5">
        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
          <div><span class="text-xs font-semibold text-neutral-500">Nama</span><p class="mt-1 font-semibold text-neutral-950"><?= $e($item['name'] ?? '') ?></p></div>
          <div><span class="text-xs font-semibold text-neutral-500">Kampus</span><p class="mt-1 font-semibold text-neutral-950"><?= $e($item['campus'] ?? '') ?></p></div>
          <div><span class="text-xs font-semibold text-neutral-500">Kategori</span><p class="mt-1 font-semibold text-neutral-950"><?= $e($item['category'] ?? '') ?></p></div>
          <div><span class="text-xs font-semibold text-neutral-500">Tahun</span><p class="mt-1 font-semibold text-neutral-950"><?= $e($item['year'] ?? '') ?></p></div>
        </div>
        <?php if (!empty($item['institution'])): ?>
          <div class="mt-4 border-t border-neutral-900/10 pt-4">
            <span class="text-xs font-semibold text-neutral-500">Penyelenggara</span>
            <p class="mt-1 font-semibold text-neutral-950"><?= $e($item['institution']) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endif; ?>
