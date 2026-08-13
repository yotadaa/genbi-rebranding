<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */
/** @var \App\Controllers\Keuangan\AuthController $keuanganAuthController */
/** @var \App\Controllers\Keuangan\WilayahController $keuanganWilayahController */
/** @var \App\Controllers\Keuangan\UnjaController $keuanganUnjaController */
/** @var \App\Controllers\Keuangan\UinController $keuanganUinController */

$router->get('/keuangan/akun/login', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showLogin($request, $response);
});

$router->get('/keuangan/akun/register', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showRegister($request, $response);
});

$router->post('/keuangan/akun/register', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->register($request, $response);
});

// Bendahara Wilayah Routes
$router->get('/keuangan/bendahara/wilayah/dashboard', static function (Request $request, Response $response) use ($keuanganWilayahController) {
    $keuanganWilayahController->dashboard($request, $response);
});
$router->get('/keuangan/bendahara/wilayah/profil', static function (Request $request, Response $response) use ($keuanganWilayahController) {
    $keuanganWilayahController->profil($request, $response);
});

// Bendahara Komsat UNJA Routes
$router->get('/keuangan/bendahara/unja/dashboard', static function (Request $request, Response $response) use ($keuanganUnjaController) {
    $keuanganUnjaController->dashboard($request, $response);
});
$router->get('/keuangan/bendahara/unja/profil', static function (Request $request, Response $response) use ($keuanganUnjaController) {
    $keuanganUnjaController->profil($request, $response);
});

// Bendahara Komsat UIN Routes
$router->get('/keuangan/bendahara/uin/dashboard', static function (Request $request, Response $response) use ($keuanganUinController) {
    $keuanganUinController->dashboard($request, $response);
});
