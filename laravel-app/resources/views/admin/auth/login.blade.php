<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Admin Login | GenBI Provinsi Jambi</title>
  <link rel="stylesheet" href="/assets/css/tailwind.css" />
  <link rel="stylesheet" href="/assets/css/styles.css" />
  <link rel="icon" href="/uploads/logo.png" />
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
          <div>
            <strong>Clean URL</strong>
            <span>/admin/login</span>
          </div>
          <div>
            <strong>Protected CMS</strong>
            <span>Session + CSRF ready</span>
          </div>
          <div>
            <strong>Editorial UI</strong>
            <span>Soft blue, serif, rounded cards</span>
          </div>
        </div>
      </div>
    </section>

    <section class="admin-login-panel" aria-label="Admin login form">
      <div class="admin-login-card slide-in" style="animation-delay:90ms">
        <a href="/" class="admin-login-brand" aria-label="Kembali ke website GenBI">
          <span class="logo-shell"><img src="https://genbijambi.com/public/uploads/logo.png" alt="GenBI Provinsi Jambi" class="h-9 w-auto" /></span>
          <span>
            <strong>GenBI Provinsi Jambi</strong>
            <small>Admin Panel</small>
          </span>
        </a>

        <div class="mt-10">
          <p class="eyebrow">Secure access</p>
          <h2 class="serif mt-3 text-4xl font-semibold tracking-[-0.045em] text-neutral-950">Masuk admin</h2>
          <p class="mt-3 text-sm leading-7 text-neutral-600">Gunakan akun admin GenBI Jambi yang sudah terdaftar untuk mengakses panel manajemen konten.</p>
        </div>

        @if ($errors->any())
          <div class="admin-login-error bg-red-50 text-red-700 p-4 rounded-xl text-sm border border-red-200 mt-4">
            {{ $errors->first() }}
          </div>
        @endif
        @if (session('error'))
          <div class="admin-login-error bg-red-50 text-red-700 p-4 rounded-xl text-sm border border-red-200 mt-4">
            {{ session('error') }}
          </div>
        @endif

        <form class="mt-8 grid gap-5" action="{{ route('admin.login.submit') }}" method="post">
          @csrf
          <label class="admin-login-field">
            <span>Email admin</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="Masukkan email admin" required autofocus />
          </label>
          <label class="admin-login-field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required />
          </label>
          <div class="flex flex-col gap-3 text-sm text-neutral-600 sm:flex-row sm:items-center sm:justify-between">
            <label class="inline-flex items-center gap-2 font-semibold">
              <input type="checkbox" name="remember" value="1" class="admin-login-check" />
              Ingat perangkat ini
            </label>
          </div>
          <button type="submit" class="btn btn-primary min-h-[3rem] w-full">Masuk ke Dashboard</button>
        </form>

      </div>
    </section>

  </main>
</body>
</html>
