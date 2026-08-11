<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */
/** @var \App\Controllers\Keuangan\AuthController $keuanganAuthController */

$router->get('/keuangan/akun/login', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showLogin($request, $response);
});

$router->get('/keuangan/akun/register', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showRegister($request, $response);
});
