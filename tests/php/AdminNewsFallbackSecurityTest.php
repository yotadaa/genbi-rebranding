<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap/app.php';

function expect_admin_news(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$isEdit = true;
$item = [
    'id' => 9,
    'title' => 'Legacy imported news',
    'excerpt' => 'Ringkasan',
    'content' => '<p onclick="alert(1)">Legacy paragraph</p><script>alert(2)</script><img src="x" onerror="alert(3)">',
    'date' => '2026-06-23',
    'category_id' => 1,
    'photo' => '',
    'status' => 'draft',
];
$categories = [
    ['category_id' => 1, 'category_name' => 'Berita'],
];
$e = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$url = static fn(string $name, array $params = []): string => match ($name) {
    'admin.news' => '/admin/news',
    default => '/admin',
};

ob_start();
require dirname(__DIR__, 2) . '/app/Views/admin/news/form.php';
$html = ob_get_clean();

expect_admin_news(str_contains($html, 'Legacy paragraph'), 'Admin news fallback should keep safe legacy rich text content.');
expect_admin_news(!str_contains($html, '<script'), 'Admin news fallback must strip legacy script tags before rendering.');
expect_admin_news(!str_contains($html, 'onclick='), 'Admin news fallback must strip legacy event handler attributes.');
expect_admin_news(!str_contains($html, 'onerror='), 'Admin news fallback must strip legacy image event handler attributes.');

echo "PHP admin news fallback security tests passed\n";
