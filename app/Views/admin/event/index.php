<?php $items = $items ?? []; $query = $query ?? ''; ?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Agenda</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Agenda komunitas dari <code>tbl_event</code>, sama dengan data yang tampil di halaman publik.</p>
    </div>
    <a href="/admin/event-add" class="btn btn-primary">Add Agenda</a>
  </header>

  <section class="admin-card mt-6 p-4 md:p-6">
    <form method="get" action="/admin/event" class="cms-toolbar">
      <label class="cms-search"><span class="sr-only">Cari agenda</span><input name="q" value="<?= $e($query) ?>" placeholder="Cari agenda, lokasi, atau ringkasan..."></label>
      <button class="btn btn-secondary" type="submit">Cari</button>
    </form>
    <div class="admin-responsive-table mt-5" id="admin-event-list" data-ssr="true">
      <table class="cms-table">
        <thead><tr><th>SL</th><th>Agenda</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php if ($items === []): ?>
            <tr><td colspan="6" class="py-8 text-center text-neutral-500">Belum ada agenda.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $index => $item): ?>
              <tr data-event-id="<?= (int) ($item['id'] ?? 0) ?>">
                <td><?= $index + 1 ?></td>
                <td><strong><?= $e($item['title'] ?? '') ?></strong><p class="mt-1 text-xs text-neutral-500"><?= $e($item['excerpt'] ?? '') ?></p></td>
                <td><?= $e($item['start_date'] ?? '-') ?></td>
                <td><?= $e($item['end_date'] ?? '-') ?></td>
                <td><span class="cms-pill muted"><?= $e($item['status'] ?? '-') ?></span></td>
                <td><div class="flex gap-2"><a href="/admin/event-edit?id=<?= (int) ($item['id'] ?? 0) ?>" class="cms-action edit">Edit</a><button type="button" class="cms-action delete" data-agenda-delete="<?= (int) ($item['id'] ?? 0) ?>">Delete</button></div></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
