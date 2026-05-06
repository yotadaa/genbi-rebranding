<?php

declare(strict_types=1);

use App\Config\App;
use App\Config\Database;
use App\Config\Security;
use App\Core\Env;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

Env::load(__DIR__ . '/fixtures/test.env');

$app = App::config();
assert($app['env'] === 'testing');
assert($app['url'] === 'http://example.test');

$db = Database::config();
assert($db['host'] === 'db.example.test');
assert($db['port'] === '3307');
assert($db['name'] === 'genbi_test');
assert($db['user'] === 'tester');
assert($db['pass'] === 'secret value');
assert($db['charset'] === 'utf8mb4');

$security = Security::config();
assert($security['session_name'] === 'GENBI_TEST');
assert($security['session_secure'] === true);
assert($security['session_samesite'] === 'Strict');

echo "PHP foundation tests passed\n";
