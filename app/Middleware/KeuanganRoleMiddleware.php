<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class KeuanganRoleMiddleware implements Middleware
{
    private array $allowedRoles;

    /**
     * @param array<string> $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $userRole = Session::get('keuangan_role');

        if (!in_array($userRole, $this->allowedRoles, true)) {
            // Jika role tidak diizinkan, kita akan lempar ke halaman yang sesuai rolenya
            // atau cukup lempar kembali ke login
            Session::flash('swal_error', 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
            
            // Redirect based on current real role if possible
            if ($userRole === 'bendahara_wilayah') {
                $response->redirect('/keuangan/bendahara/wilayah/dashboard');
                return;
            }
            if ($userRole === 'bendahara_unja') {
                $response->redirect('/keuangan/bendahara/unja/dashboard');
                return;
            }
            if ($userRole === 'bendahara_uin') {
                $response->redirect('/keuangan/bendahara/uin/dashboard');
                return;
            }
            if ($userRole === 'anggota') {
                $response->redirect('/keuangan/home');
                return;
            }

            // Fallback
            $response->redirect('/keuangan/akun/login');
            return;
        }

        $next($request, $response);
    }
}
