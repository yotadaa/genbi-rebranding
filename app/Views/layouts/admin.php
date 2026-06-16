<?php
/** @var callable $e */
$siteSettings = $siteSettings ?? null;
$sitePayload = is_object($siteSettings) && method_exists($siteSettings, 'clientPayload') ? $siteSettings->clientPayload() : ($site ?? []);
$themeKey = is_object($siteSettings) && method_exists($siteSettings, 'themeKey') ? $siteSettings->themeKey('admin') : 'genbi';
$inlineThemeCss = is_object($siteSettings) && method_exists($siteSettings, 'themeInlineCss') ? $siteSettings->themeInlineCss('admin') : '';
$settingsJson = json_encode($sitePayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$content = $content ?? '';
?>
<!doctype html>
<html lang="id" data-theme="<?= $e($themeKey) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?= $e($csrfToken ?? '') ?>">
  <title><?= $e($title ?? 'Admin GenBI') ?></title>
  <?php if ($inlineThemeCss !== ''): ?><style><?= $inlineThemeCss ?></style><?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700&display=swap">
  <link rel="stylesheet" href="/assets/css/tailwind.css?v=20260616g">
  <link rel="stylesheet" href="/assets/css/theme.css?v=20260517a">
  <link rel="stylesheet" href="/assets/css/styles.min.css?v=20260616i">
  <?php if (!empty($sitePayload['favicon'])): ?><link rel="icon" href="<?= $e((string) $sitePayload['favicon']) ?>"><?php endif; ?>
</head>
<body class="admin-body" data-cms-page="<?= $e($cmsPage ?? '') ?>" data-cms-mode="<?= $e($cmsMode ?? '') ?>">
  <div class="admin-shell">
    <aside id="admin-sidebar" class="admin-sidebar"></aside>
    <div class="admin-main">
      <div id="admin-topbar" class="admin-topbar"></div>
      <main class="px-4 py-8 md:px-8 lg:px-10" id="admin-content">
        <?= $content ?>
      </main>
    </div>
  </div>
  <div id="admin-mobile-backdrop" class="fixed inset-0 z-[70] hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden"></div>
  <div id="admin-toast" class="admin-toast rounded-2xl bg-blue-950 px-5 py-4 text-sm font-semibold text-white shadow-2xl">Perubahan disimpan.</div>
  <div id="admin-modal-root"></div>
  <script>window.GenBISiteSettings = <?= $settingsJson ?>;</script>
  <script defer src="/assets/js/dist/data.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/api-core.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/api.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/app.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/lib/ui.js?v=20260616g"></script>
  <script defer src="/assets/js/dist/admin/admin.js?v=20260616g"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
