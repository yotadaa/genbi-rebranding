<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$router = new Router();
$router->get('/news/{slug}', static function (Request $request, Response $response, array $params): void {
    $response->html('<h1>' . htmlspecialchars($params['slug'], ENT_QUOTES, 'UTF-8') . '</h1>');
});

$_SERVER['REQUEST_METHOD'] = 'HEAD';
$_SERVER['REQUEST_URI'] = '/news/genbi-goes-to-campus-uin-sts-jambi';

ob_start();
$router->dispatch(new Request(), new Response());
$body = ob_get_clean();

assert(http_response_code() === 200);
assert($body === '');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/news/genbi-goes-to-campus-uin-sts-jambi';

ob_start();
$router->dispatch(new Request(), new Response());
$body = ob_get_clean();

assert(http_response_code() === 200);
assert(str_contains($body, 'genbi-goes-to-campus-uin-sts-jambi'));

echo "PHP router HEAD request tests passed\n";
