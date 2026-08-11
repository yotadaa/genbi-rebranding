<?php

declare(strict_types=1);

namespace App\Controllers\Keuangan;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ViewRenderer;
use PDO;

final class AuthController
{
    public function __construct(private ViewRenderer $renderer) {}

    public function showLogin(Request $request, Response $response): void
    {
        $response->html($this->renderer->render('keuangan/login.php'));
    }

    public function showRegister(Request $request, Response $response): void
    {
        $response->html($this->renderer->render('keuangan/register.php'));
    }

    public function register(Request $request, Response $response): void
    {
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Validasi Kosong
        if (empty($email) || empty($role) || empty($password) || empty($confirmPassword)) {
            Session::flash('swal_error', 'Semua kolom wajib diisi!');
            $response->redirect('/keuangan/akun/register');
            return;
        }

        // 2. Validasi Format Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('swal_error', 'Format email tidak valid!');
            $response->redirect('/keuangan/akun/register');
            return;
        }

        // 3. Validasi Kesamaan Password
        if ($password !== $confirmPassword) {
            Session::flash('swal_error', 'Password dan Konfirmasi Password tidak cocok!');
            $response->redirect('/keuangan/akun/register');
            return;
        }

        // 4. Validasi Password Kuat (Minimal 8 karakter, mengandung huruf & angka)
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            Session::flash('swal_error', 'Password minimal 8 karakter dan harus mengandung kombinasi huruf dan angka!');
            $response->redirect('/keuangan/akun/register');
            return;
        }

        try {
            $db = Database::connection();

            // 5. Validasi Role (1 Role = 1 Akun)
            $stmtCheck = $db->prepare('SELECT COUNT(*) FROM tbl_user WHERE role = :role');
            $stmtCheck->execute([':role' => $role]);
            $roleExists = (int) $stmtCheck->fetchColumn();

            if ($roleExists > 0) {
                // Formatting role name for the error message
                $roleName = str_replace('_', ' ', strtoupper($role));
                Session::flash('swal_error', "Jabatan {$roleName} sudah memiliki akun yang terdaftar. Tidak dapat mendaftar lagi!");
                $response->redirect('/keuangan/akun/register');
                return;
            }
            
            // Check if email already exists just in case
            $stmtCheckEmail = $db->prepare('SELECT COUNT(*) FROM tbl_user WHERE email = :email');
            $stmtCheckEmail->execute([':email' => $email]);
            if ((int) $stmtCheckEmail->fetchColumn() > 0) {
                Session::flash('swal_error', 'Alamat email ini sudah terdaftar!');
                $response->redirect('/keuangan/akun/register');
                return;
            }

            // 6. Insert ke Database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $db->prepare('
                INSERT INTO tbl_user (email, password, role, status, photo, token, failed_login_count) 
                VALUES (:email, :password, :role, :status, :photo, :token, 0)
            ');

            $stmtInsert->execute([
                ':email' => $email,
                ':password' => $hashedPassword,
                ':role' => $role,
                ':status' => 'Active',
                ':photo' => '',
                ':token' => ''
            ]);

            Session::flash('swal_success', 'Akun berhasil didaftarkan! Silakan masuk.');
            $response->redirect('/keuangan/akun/login');

        } catch (\Exception $e) {
            Session::flash('swal_error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
            $response->redirect('/keuangan/akun/register');
        }
    }
}
