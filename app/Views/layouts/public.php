<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= $meta ?? '<title>GenBI Provinsi Jambi</title>' ?>
  <link rel="stylesheet" href="/assets/css/tailwind.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <?= $jsonld ?? '' ?>
</head>
<body class="<?= $e($bodyClass ?? '') ?>">
  <div id="site-header"></div>
  <main id="main-content">
    <?= $content ?>
  </main>
  <div id="site-footer"></div>
  <div id="modal-root"></div>
  <script src="/assets/js/data.js"></script>
  <script src="/assets/js/api-core.js"></script>
  <script src="/assets/js/api.js"></script>
  <script src="/assets/js/app.js"></script>
  <script src="/assets/js/lib/ui.js"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
