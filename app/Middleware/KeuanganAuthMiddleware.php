<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use PDO;

final class KeuanganAuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        // 1. Cek apakah ada session user keuangan
        if (Session::has('keuangan_user_id')) {
            $next($request, $response);
            return;
        }

        // 2. Jika tidak ada, cek cookie remember_token
        if (isset($_COOKIE['keuangan_remember_token'])) {
            $token = $_COOKIE['keuangan_remember_token'];
            
            try {
                $db = Database::connection();
                $stmt = $db->prepare('SELECT id, role FROM tbl_user WHERE token = :token AND status = "Active" LIMIT 1');
                $stmt->execute([':token' => $token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Berhasil auto-login dari cookie
                    Session::set('keuangan_user_id', (int) $user['id']);
                    Session::set('keuangan_role', $user['role']);
                    
                    $next($request, $response);
                    return;
                }
            } catch (\Exception $e) {
                // Abaikan dan biarkan redirect
            }
        }

        // 3. Gagal autentikasi, redirect ke halaman login
        Session::flash('swal_error', 'Sesi Anda telah berakhir atau Anda belum login. Silakan login kembali.');
        $response->redirect('/keuangan/akun/login');
    }
}
