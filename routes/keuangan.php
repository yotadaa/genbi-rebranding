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
    $router->get('/keuangan/bendahara/wilayah/transaksi/create', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksiCreate($request, $response);
    });
    $router->post('/keuangan/bendahara/wilayah/transaksi/store', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksiStore($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/transaksi/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksiEdit($request, $response, $args['id'] ?? null);
    });
    $router->post('/keuangan/bendahara/wilayah/transaksi/update/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksiUpdate($request, $response, $args['id'] ?? null);
    });
    $router->post('/keuangan/bendahara/wilayah/transaksi/delete/{id}', static function (Request $request, Response $response, array $args) use ($keuanganWilayahController) {
        $keuanganWilayahController->transaksiDestroy($request, $response, $args['id'] ?? null);
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

    // Komsat Read-Only Views
    $router->get('/keuangan/bendahara/wilayah/unja', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->unja($request, $response);
    });
    $router->get('/keuangan/bendahara/wilayah/uin', static function (Request $request, Response $response) use ($keuanganWilayahController) {
        $keuanganWilayahController->uin($request, $response);
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
    $router->post('/keuangan/bendahara/unja/profil', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->updateProfil($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/kegiatan', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->kegiatan($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/kegiatan/tambah', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->tambahKegiatan($request, $response);
    });
    $router->post('/keuangan/bendahara/unja/kegiatan/tambah', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->storeKegiatan($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/kegiatan/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->editKegiatan($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/unja/kegiatan/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->updateKegiatan($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/unja/kegiatan/hapus/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->hapusKegiatan($request, $response, $args);
    });
    $router->get('/keuangan/bendahara/unja/transaksi', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksi($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/transaksi/tambah', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksiCreate($request, $response);
    });
    $router->post('/keuangan/bendahara/unja/transaksi/tambah', static function (Request $request, Response $response) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksiStore($request, $response);
    });
    $router->get('/keuangan/bendahara/unja/transaksi/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksiEdit($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/unja/transaksi/edit/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksiUpdate($request, $response, $args);
    });
    $router->post('/keuangan/bendahara/unja/transaksi/hapus/{id}', static function (Request $request, Response $response, array $args) use ($keuanganUnjaController) {
        $keuanganUnjaController->transaksiHapus($request, $response, $args);
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
