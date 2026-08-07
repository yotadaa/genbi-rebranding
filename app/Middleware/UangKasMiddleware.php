<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class UangKasMiddleware
{
    public function __construct(private AuthService $auth) {}

    public function handle(Request $request, Response $response, callable $next): void
    {
        $user = $this->auth->user();

        // 1. Jika pengguna belum login sama sekali, usir ke halaman login
        if (!$user) {
            $response->redirect('/');
            return;
        }

        // 2. Daftar 6 Role 'Eksklusif' yang boleh masuk
        $allowedRoles = [
            'bendahara_wilayah',
            'bendahara_unja',
            'bendahara_uin',
            'akses_wilayah',
            'akses_unja',
            'akses_uin'
        ];

        // 3. Jika role user saat ini TIDAK ADA di dalam daftar di atas
        if (!in_array($user['role'] ?? '', $allowedRoles, true)) {
            // Tolak akses, lemparkan kembali ke dashboard utama admin dengan pesan error
            $response->redirect('/?error=unauthorized_keuangan');
            return;
        }

        // 4. Jika role-nya cocok, silakan masuk ke halaman yang dituju
        $next($request, $response);
    }
}
