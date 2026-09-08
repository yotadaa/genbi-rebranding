<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap/app.php';

use App\Core\Paginator;

// --- resolve: basic defaults ---
$p = Paginator::resolve([], 12, 24);
assert($p['page'] === 1, 'default page is 1');
assert($p['per_page'] === 12, 'default per_page is 12');
assert($p['offset'] === 0, 'default offset is 0');

// --- resolve: page 3 ---
$p = Paginator::resolve(['page' => '3'], 12, 24);
assert($p['page'] === 3, 'page 3');
assert($p['per_page'] === 12, 'per_page stays default');
assert($p['offset'] === 24, 'offset for page 3 is 24');

// --- resolve: page 1 explicit ---
$p = Paginator::resolve(['page' => '1'], 10, 50);
assert($p['page'] === 1);
assert($p['offset'] === 0);

// --- resolve: invalid page values ---
$p = Paginator::resolve(['page' => '-5'], 12, 24);
assert($p['page'] === 1, 'negative page becomes 1');

$p = Paginator::resolve(['page' => '0'], 12, 24);
assert($p['page'] === 1, 'zero page becomes 1');

$p = Paginator::resolve(['page' => 'abc'], 12, 24);
assert($p['page'] === 1, 'non-numeric page becomes 1');

// --- resolve: per_page clamped to max ---
$p = Paginator::resolve(['per_page' => '999'], 12, 24);
assert($p['per_page'] === 24, 'per_page clamped to max');

// --- resolve: per_page zero uses default ---
$p = Paginator::resolve(['per_page' => '0'], 12, 24);
assert($p['per_page'] === 12, 'per_page 0 uses default');

// --- resolve: per_page negative uses default ---
$p = Paginator::resolve(['per_page' => '-3'], 12, 24);
assert($p['per_page'] === 12, 'per_page negative uses default');

// --- resolve: per_page within range ---
$p = Paginator::resolve(['per_page' => '20'], 12, 24);
assert($p['per_page'] === 20, 'per_page 20 accepted');

// --- resolve: per_page at max boundary ---
$p = Paginator::resolve(['per_page' => '24'], 12, 24);
assert($p['per_page'] === 24, 'per_page at max accepted');

// --- resolve: combined page + per_page ---
$p = Paginator::resolve(['page' => '5', 'per_page' => '25'], 25, 100);
assert($p['page'] === 5);
assert($p['per_page'] === 25);
assert($p['offset'] === 100, 'offset for page 5 with per_page 25');

// --- totalPages ---
assert(Paginator::totalPages(80, 12) === 7, '80/12 = 7 pages');
assert(Paginator::totalPages(0, 12) === 1, '0 items = 1 page');
assert(Paginator::totalPages(12, 12) === 1, '12/12 = 1 page');
assert(Paginator::totalPages(13, 12) === 2, '13/12 = 2 pages');
assert(Paginator::totalPages(1, 1) === 1, '1/1 = 1 page');
assert(Paginator::totalPages(100, 25) === 4, '100/25 = 4 pages');
assert(Paginator::totalPages(101, 25) === 5, '101/25 = 5 pages');

// --- meta ---
$m = Paginator::meta(2, 12, 80);
assert($m['page'] === 2);
assert($m['per_page'] === 12);
assert($m['total'] === 80);
assert($m['total_pages'] === 7);

$m = Paginator::meta(1, 10, 0);
assert($m['total_pages'] === 1, 'zero total = 1 page in meta');

// --- buildQuery preserves filters ---
$qs = Paginator::buildQuery(3, ['q' => 'bank', 'category' => 'BI', 'empty' => '']);
assert(str_contains($qs, 'page=3'), 'query contains page');
assert(str_contains($qs, 'q=bank'), 'query contains q');
assert(str_contains($qs, 'category=BI'), 'query contains category');
assert(!str_contains($qs, 'empty'), 'query excludes empty');

// --- buildQuery with no filters ---
$qs = Paginator::buildQuery(1, []);
assert($qs === 'page=1', 'query with no filters');

// --- buildQuery with null values ---
$qs = Paginator::buildQuery(2, ['q' => null, 'status' => 'draft']);
assert(str_contains($qs, 'page=2'));
assert(str_contains($qs, 'status=draft'));
assert(!str_contains($qs, 'q='), 'null values excluded');

// --- buildQuery page=1 still included (for canonical) ---
$qs = Paginator::buildQuery(1, ['q' => 'test']);
assert(str_contains($qs, 'page=1'));
assert(str_contains($qs, 'q=test'));

echo "PHP paginator tests passed\n";
