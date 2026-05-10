<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $e(\App\Services\CsrfService::token()) ?>">
  <?= $meta ?? '<title>GenBI Provinsi Jambi</title>' ?>
  <link rel="stylesheet" href="/assets/css/tailwind.css?v=20260508e">
  <link rel="stylesheet" href="/assets/css/styles.css?v=20260510a">
  <?= $jsonld ?? '' ?>
</head>
<body class="<?= $e($bodyClass ?? '') ?>">
  <div id="site-header"></div>
  <main id="main-content">
    <?= $content ?>
  </main>
  <div id="site-footer"></div>
  <div id="modal-root"></div>
  <script src="/assets/js/data.js?v=20260508e"></script>
  <script src="/assets/js/api-core.js?v=20260508e"></script>
  <script src="/assets/js/api.js?v=20260508e"></script>
  <script src="/assets/js/app.js?v=20260508e"></script>
  <script src="/assets/js/lib/ui.js?v=20260510a"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
