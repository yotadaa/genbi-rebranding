<?php

declare(strict_types=1);

use App\Core\ViewRenderer;

require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

$root = dirname(__DIR__, 2);
$tmpViewDir = sys_get_temp_dir() . '/genbi-view-test-' . bin2hex(random_bytes(4));
mkdir($tmpViewDir . '/layouts', 0777, true);
mkdir($tmpViewDir . '/partials', 0777, true);

// Test 1: Basic rendering with escaping
file_put_contents($tmpViewDir . '/sample.php', '<p><?= $e($title) ?></p>');
$renderer = new ViewRenderer($tmpViewDir);
$html = $renderer->render('sample.php', ['title' => '<script>alert(1)</script>']);
assert($html === '<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>', 'Escaping failed');

// Test 2: Layout rendering
file_put_contents($tmpViewDir . '/layouts/base.php', '<main><?= $content ?></main>');
$layoutHtml = $renderer->renderWithLayout('sample.php', 'layouts/base.php', ['title' => 'GenBI']);
assert($layoutHtml === '<main><p>GenBI</p></main>', 'Layout rendering failed');

// Test 3: Missing view returns 404
$missing = $renderer->render('missing.php');
assert(str_contains($missing, '404'), 'Missing view should return 404');

// Test 4: Partial with CSRF token
file_put_contents($tmpViewDir . '/partials/head.php', '<meta name="csrf-token" content="<?= $e($csrfToken ?? "") ?>">');
file_put_contents($tmpViewDir . '/layouts/admin.php', '<!doctype html><html><head><?php require __DIR__ . "/../partials/head.php"; ?></head><body><?= $content ?></body></html>');

$adminHtml = $renderer->renderWithLayout('sample.php', 'layouts/admin.php', ['title' => 'Admin', 'csrfToken' => 'abc123']);
assert(str_contains($adminHtml, '<meta name="csrf-token" content="abc123">'), 'CSRF token injection failed');
assert(str_contains($adminHtml, '<p>Admin</p>'), 'Admin layout content failed');

// Test 5: Helper functions available
file_put_contents($tmpViewDir . '/helpers.php', '<?= $asset("css/main.css") ?> <?= $url("/about") ?>');
$helpersHtml = $renderer->render('helpers.php');
assert($helpersHtml === '/assets/css/main.css /about', 'Helper functions failed');

// Cleanup
array_map('unlink', glob($tmpViewDir . '/partials/*') ?: []);
rmdir($tmpViewDir . '/partials');
array_map('unlink', glob($tmpViewDir . '/layouts/*') ?: []);
rmdir($tmpViewDir . '/layouts');
array_map('unlink', glob($tmpViewDir . '/*') ?: []);
rmdir($tmpViewDir);

echo "PHP ViewRenderer tests passed\n";
