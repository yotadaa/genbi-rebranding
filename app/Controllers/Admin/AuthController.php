<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\CsrfService;
use App\Services\LoginThrottleService;

final class AuthController
{
    public function __construct(private AuthService $auth, private LoginThrottleService $throttle) {}

    public function showLogin(Request $request, Response $response): void
    {
        // If already logged in, redirect to dashboard
        if ($this->auth->check()) {
            $response->redirect('/admin/dashboard');
            return;
        }

        $error = Session::getFlash('login_error', '');
        $email = Session::getFlash('login_email', '');
        $csrf = CsrfService::hiddenInput();

        $html = $this->renderLoginPage($error, $email, $csrf);
        $response->html($html, 200, ['X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function login(Request $request, Response $response): void
    {
        // Validate CSRF
        $token = $_POST['_csrf_token'] ?? null;
        if (!CsrfService::validate($token)) {
            Session::flash('login_error', 'Sesi tidak valid. Silakan coba lagi.');
            $response->redirect('/admin/login');
            return;
        }

        $ip = $request->ip() ?? 'unknown';
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($this->throttle->isBlocked($email, $ip)) {
            Session::flash('login_error', 'Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.');
            $response->redirect('/admin/login');
            return;
        }
        $password = $_POST['password'] ?? '';

        // Basic input validation
        if ($email === '' || $password === '') {
            Session::flash('login_error', 'Email dan password wajib diisi');
            Session::flash('login_email', $email);
            $response->redirect('/admin/login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('login_error', 'Format email tidak valid');
            Session::flash('login_email', $email);
            $response->redirect('/admin/login');
            return;
        }

        $result = $this->auth->attempt($email, $password, $ip);

        if ($result['success']) {
            $this->throttle->clear($email, $ip);
            CsrfService::regenerate();
            $response->redirect('/admin/dashboard');
            return;
        }

        $this->throttle->recordFailure($email, $ip);
        Session::flash('login_error', $result['error'] ?? 'Login gagal');
        Session::flash('login_email', $email);
        $response->redirect('/admin/login');
    }

    public function logout(Request $request, Response $response): void
    {
        $this->auth->logout();
        $response->redirect('/admin/login');
    }

    private function renderLoginPage(string $error, string $email, string $csrf): string
    {
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $errorHtml = $error ? '<div class="admin-login-error">' . $e($error) . '</div>' : '';

        return <<<HTML
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Login Admin | GenBI Provinsi Jambi</title>
  <link rel="stylesheet" href="/assets/css/tailwind.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="admin-login-body page-ready">
  <main class="admin-login-page">
    <section class="admin-login-hero" aria-label="GenBI admin login introduction">
      <div class="admin-login-hero-bg"></div>
      <div class="admin-login-hero-content slide-in">
        <span class="admin-login-badge">GenBI CMS</span>
        <h1 class="serif mt-5 text-5xl font-semibold leading-[0.95] tracking-[-0.055em] text-white md:text-7xl">Ruang kerja editorial GenBI Jambi.</h1>
        <p class="mt-6 max-w-xl text-base leading-8 text-blue-50/85">Masuk untuk mengelola berita, prestasi, komentar, anggota, dan identitas publik dengan gaya visual yang sama seperti website utama.</p>
        <div class="admin-login-points mt-10">
          <div><strong>Clean URL</strong><span>/admin/login</span></div>
          <div><strong>Protected CMS</strong><span>Session + CSRF aktif</span></div>
          <div><strong>Editorial UI</strong><span>Soft blue, serif, rounded cards</span></div>
        </div>
      </div>
    </section>

    <section class="admin-login-panel" aria-label="Admin login form">
      <div class="admin-login-card slide-in" style="animation-delay:90ms">
        <a href="/" class="admin-login-brand" aria-label="Kembali ke website GenBI">
          <span class="logo-shell"><img src="https://genbijambi.com/public/uploads/logo.png" alt="GenBI Provinsi Jambi" class="h-9 w-auto"></span>
          <span><strong>GenBI Provinsi Jambi</strong><small>Admin Panel</small></span>
        </a>

        <div class="mt-10">
          <p class="eyebrow">Secure access</p>
          <h2 class="serif mt-3 text-4xl font-semibold tracking-[-0.045em] text-neutral-950">Masuk admin</h2>
          <p class="mt-3 text-sm leading-7 text-neutral-600">Gunakan akun admin yang sudah terdaftar untuk membuka dashboard CMS.</p>
        </div>

        {$errorHtml}

        <form class="mt-8 grid gap-5" method="POST" action="/admin/login">
          {$csrf}
          <label class="admin-login-field" for="email">
            <span>Email admin</span>
            <input type="email" id="email" name="email" value="{$e($email)}" autocomplete="email" placeholder="admin@genbijambi.com" required>
          </label>
          <label class="admin-login-field" for="password">
            <span>Password</span>
            <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required>
          </label>
          <div class="flex flex-col gap-3 text-sm text-neutral-600 sm:flex-row sm:items-center sm:justify-between">
            <label class="inline-flex items-center gap-2 font-semibold"><input type="checkbox" name="remember" value="1" class="admin-login-check">Ingat perangkat ini</label>
            <a href="#" class="font-bold text-blue-800 hover:text-blue-950">Lupa password?</a>
          </div>
          <button type="submit" class="btn btn-primary min-h-[3rem] w-full">Masuk ke Dashboard</button>
        </form>

        <div class="admin-login-note mt-8">
          <strong>Keamanan login</strong>
          <p>Form ini memakai CSRF token, session aman, dan autentikasi backend. Jangan bagikan akun admin ke pihak lain.</p>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
HTML;
    }
}
