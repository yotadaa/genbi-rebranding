<?php
$settingsData = $settingsData ?? [];
$json = json_encode($settingsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
?>
<section class="space-y-6">
  <div class="admin-card p-5 md:p-7">
    <p class="eyebrow">Settings</p>
    <h1 class="serif mt-3 text-4xl font-semibold tracking-tight text-[rgb(var(--text-primary))]">Live identity and theme settings</h1>
    <p class="mt-4 max-w-3xl text-base leading-7 text-[rgb(var(--text-secondary))]">Semua tab sekarang memakai data nyata dari backend. Perubahan yang disimpan akan mengalir ke shell admin dan website publik.</p>
  </div>

  <div class="admin-card p-5 md:p-7">
    <div id="settings-tabs" class="flex gap-2 overflow-x-auto pb-1"></div>
    <div id="settings-panel" class="mt-6"></div>
  </div>
</section>

<script>
window.GenBISettingsBootstrap = <?= $json ?>;
</script>
