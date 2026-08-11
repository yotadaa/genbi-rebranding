<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */

$router->get('/keuangan/akun/login', static function (Request $request, Response $response) {
    $response->html('ini halaman login bendahra');
});

$router->get('/keuangan/akun/register', static function (Request $request, Response $response) {
    $response->html('ini halaman register bendahara');
});
