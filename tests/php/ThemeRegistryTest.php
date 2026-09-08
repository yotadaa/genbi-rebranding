<?php

declare(strict_types=1);

use App\Config\ThemeRegistry;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$keys = ThemeRegistry::keys();
assert(count($keys) === 21);
assert(ThemeRegistry::get('unknown')['key'] === 'genbi');
assert(count(ThemeRegistry::summaries()) === 21);

echo "PHP theme registry tests passed\n";
