<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?= $e($csrfToken ?? '') ?>">
  <title><?= $e($title ?? 'Admin GenBI') ?></title>
  <link rel="stylesheet" href="/assets/css/tailwind.css?v=20260508e">
  <link rel="stylesheet" href="/assets/css/styles.css?v=20260508e">
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
  <script src="/assets/js/data.js?v=20260508e"></script>
  <script src="/assets/js/api-core.js?v=20260508e"></script>
  <script src="/assets/js/api.js?v=20260508e"></script>
  <script src="/assets/js/app.js?v=20260508e"></script>
  <script src="/assets/js/admin/admin.js?v=20260508e"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
