<?php $items = $items ?? []; ?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div><p class="eyebrow">Admin CMS</p><h1 class="section-title mt-3">View Categories</h1><p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Kategori berita dipakai untuk filter publik dan editor berita.</p></div>
    <button class="btn btn-primary" type="button" data-category-add>Add New</button>
  </header>
  <section class="admin-card mt-6 p-4 md:p-6" id="admin-category-list" data-ssr="true">
    <div class="admin-responsive-table"><table class="cms-table"><thead><tr><th>SL</th><th>Category Name</th><th>Category Banner</th><th>Action</th></tr></thead><tbody>
      <?php if ($items === []): ?><tr><td colspan="4" class="py-8 text-center text-neutral-500">Belum ada kategori.</td></tr><?php endif; ?>
      <?php foreach ($items as $index => $item): ?><tr><td><?= $index + 1 ?></td><td><strong><?= $e($item['name'] ?? '') ?></strong></td><td><?php if (!empty($item['banner'])): ?><img src="<?= $e($item['banner']) ?>" class="table-banner" alt="<?= $e($item['name'] ?? '') ?>"><?php else: ?><span class="text-neutral-500">Belum ada banner</span><?php endif; ?></td><td><div class="flex gap-2"><button class="cms-action edit" type="button" data-category-edit="<?= (int) ($item['id'] ?? 0) ?>" data-category-name="<?= $e($item['name'] ?? '') ?>">Edit</button><button class="cms-action delete" type="button" data-category-delete="<?= (int) ($item['id'] ?? 0) ?>">Delete</button></div></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </section>
</section>
