<?php
$isEdit = $isEdit ?? false;
$item = $item ?? [];
$itemId = (int) ($item['id'] ?? 0);
$content = \App\Services\HtmlSanitizer::sanitize((string) ($item['content'] ?? ''));
$start = substr((string) ($item['start_date'] ?? date('Y-m-d')), 0, 10);
$end = substr((string) ($item['end_date'] ?? $start), 0, 10);
$itemJson = json_encode($item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
?>
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div><p class="eyebrow">Admin CMS</p><h1 class="section-title mt-3"><?= $isEdit ? 'Edit Agenda' : 'Add Agenda' ?></h1><p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Agenda ditulis di server terlebih dahulu; Editor.js hanya memperkaya proses penyuntingan.</p></div>
    <a href="/admin/event" class="btn btn-secondary">View All Agenda</a>
  </header>
  <form class="medium-editor-layout mt-6" id="event-form" data-ssr="true" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ?>" data-item="<?= $e($itemJson) ?>">
    <main class="medium-editor-canvas">
      <div class="medium-editor-kicker">Agenda editor</div>
      <section class="story-main-block"><label for="event-title-field">Agenda Title</label><div id="event-title-field" class="story-title-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis judul agenda..."><?= $e($item['title'] ?? '') ?></div></section>
      <section class="story-main-block"><label for="event-short-content-field">Agenda Short Content</label><div id="event-short-content-field" class="story-excerpt-field" contenteditable="true" spellcheck="true" data-placeholder="Tulis ringkasan singkat untuk agenda list..."><?= $e($item['excerpt'] ?? '') ?></div></section>
      <div class="medium-editor-divider"><div class="medium-editor-kicker">Agenda content</div></div>
      <div id="news-editor" class="medium-editor-host"></div>
      <div id="editor-fallback" class="editor-fallback"><article contenteditable="true"><?= $content ?></article></div>
      <p class="medium-editor-help">Konten awal tersedia tanpa JavaScript. Editor.js dapat digunakan bila JavaScript aktif.</p>
    </main>
    <aside class="editor-config-sidebar medium-config-sidebar">
      <section class="config-card medium-config-card"><h2>Publishing</h2>
        <label class="config-field"><span>Agenda Start Date</span><input id="event-start-date" class="config-input" type="date" value="<?= $e($start) ?>"></label>
        <label class="config-field"><span>Agenda End Date</span><input id="event-end-date" class="config-input" type="date" value="<?= $e($end) ?>"></label>
        <label class="config-field"><span>Location</span><input id="event-location" class="config-input" value="<?= $e($item['location'] ?? '') ?>" placeholder="Lokasi agenda..."></label>
        <label class="config-field"><span>Primary Map URL</span><textarea id="event-map" class="config-input" rows="6" placeholder="Paste iframe Google Maps atau URL embed..."><?= $e($item['map'] ?? '') ?></textarea></label>
      </section>
      <section class="config-card medium-config-card"><h2>SEO Information</h2>
        <label class="config-field"><span>Meta Title</span><input id="event-meta-title" class="config-input" value="<?= $e($item['meta_title'] ?? $item['title'] ?? '') ?>"></label>
        <label class="config-field"><span>Meta Description</span><textarea id="event-meta-description" class="config-input" rows="5"><?= $e($item['meta_description'] ?? $item['excerpt'] ?? '') ?></textarea></label>
      </section>
      <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Update Agenda' : 'Submit Agenda' ?></button>
    </aside>
  </form>
</section>
