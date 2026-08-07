<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */
/** @var \App\Middleware\UangKasMiddleware $uangKasMiddleware */
// /** @var UangKasController $uangKasController */

// Bungkus SEMUA route keuangan ke dalam Grup yang dijaga oleh Middleware
$router->group([$uangKasMiddleware], static function ($router) {

    // Halaman Utama Uang Kas
    $router->get('/uang-kas', static function (Request $request, Response $response) {
        // Karena belum ada Controller, kita kembalikan HTML sederhana dulu untuk Testing
        $response->html('<h1>Terkunci!</h1><p>Selamat, Anda memiliki salah satu dari 6 akses eksklusif untuk melihat halaman ini.</p>');
    });
});
