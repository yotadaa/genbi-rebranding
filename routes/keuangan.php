<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */
/** @var \App\Controllers\Keuangan\AuthController $keuanganAuthController */
/** @var \App\Controllers\Keuangan\WilayahController $keuanganWilayahController */
/** @var \App\Controllers\Keuangan\UnjaController $keuanganUnjaController */
/** @var \App\Controllers\Keuangan\UinController $keuanganUinController */
/** @var \App\Controllers\Keuangan\AnggotaController $keuanganAnggotaController */

$router->get('/keuangan/akun/login', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showLogin($request, $response);
});

$router->get('/keuangan/akun/register', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->showRegister($request, $response);
});

$router->post('/keuangan/akun/register', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->register($request, $response);
});

$router->post('/keuangan/akun/login', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->login($request, $response);
});

$router->post('/keuangan/akun/logout', static function (Request $request, Response $response) use ($keuanganAuthController) {
    $keuanganAuthController->logout($request, $response);
});

// Authentication Middleware instance
$authMw = new \App\Middleware\KeuanganAuthMiddleware();

// ---------------------------------------------------------
// Bendahara Wilayah Routes
// ---------------------------------------------------------
$router->group([$authMw, new \App\Middleware\KeuanganRoleMiddleware(['bendahara_wilayah'])], static function ($router) use ($keuanganWilayahController) {
    $router->get('/keuangan/bendahara/wilayah/dashboard', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->dashboard($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/transaksi', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksi($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/profil', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->profil($request, $response);
    });
    $router->post('/keuangan/bendahara/wilayah/profil', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->updateProfil($request, $response);
    });
    
    // Kegiatan
    $router->get('/keuangan/bendahara/wilayah/kegiatan', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->kegiatan($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/kegiatan/tambah', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->tambahKegiatan($request, $response);
    });
    $router->post('/keuangan/bendahara/wilayah/kegiatan/tambah', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->storeKegiatan($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/kegiatan/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->editKegiatan($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/wilayah/kegiatan/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->updateKegiatan($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/wilayah/kegiatan/hapus/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->hapusKegiatan($request, $response, $args);
    });
});

// ---------------------------------------------------------
// Bendahara Komsat UNJA Routes
// ---------------------------------------------------------
$router->group([$authMw, new \App\Middleware\KeuanganRoleMiddleware(['bendahara_unja'])], static function ($router) use ($keuanganUnjaController) {
    $router->get('/keuangan/bendahara/unja/dashboard', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->dashboard($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/profil', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->profil($request, $response);
    });
});

// ---------------------------------------------------------
// Bendahara Komsat UIN Routes
// ---------------------------------------------------------
$router->group([$authMw, new \App\Middleware\KeuanganRoleMiddleware(['bendahara_uin'])], static function ($router) use ($keuanganUinController) {
    $router->get('/keuangan/bendahara/uin/dashboard', static function (Request $request, Response $response) use ($keuanganUinController) {
        $keuanganUinController->dashboard($request, $response);
    });
    $router->get('/keuangan/bendahara/uin/profil', static function (Request $request, Response $response) use ($keuanganUinController) {
        $keuanganUinController->profil($request, $response);
    });
});

// ---------------------------------------------------------
// Anggota Routes
// ---------------------------------------------------------
$router->group([$authMw, new \App\Middleware\KeuanganRoleMiddleware(['anggota'])], static function ($router) use ($keuanganAnggotaController) {
    $router->get('/keuangan/home', static function (Request $request, Response $response) {
        $response->redirect('/keuangan/anggota/wilayah');
    });
    
    $router->get('/keuangan/anggota/wilayah', static function (Request $request, Response $response) use ($keuanganAnggotaController) {
        $keuanganAnggotaController->wilayah($request, $response);
    });
    
    $router->get('/keuangan/anggota/unja', static function (Request $request, Response $response) use ($keuanganAnggotaController) {
        $keuanganAnggotaController->unja($request, $response);
    });
    
    $router->get('/keuangan/anggota/uin', static function (Request $request, Response $response) use ($keuanganAnggotaController) {
        $keuanganAnggotaController->uin($request, $response);
    });
});
