<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Daftar Keuangan | GenBI Provinsi Jambi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/assets/css/styles.css?v=<?= time() ?>">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-serif-title {
            font-family: 'Source Serif 4', serif;
            letter-spacing: -0.02em;
        }
    </style>
</head>

<body class="page-ready bg-slate-50 text-slate-800 antialiased selection:bg-blue-100 selection:text-blue-900">

    <div class="flex min-h-screen bg-slate-50">

        <!-- Left Panel: Hero Section -->
        <div class="hidden lg:flex lg:w-[800px] relative bg-blue-900 overflow-hidden items-end p-12 lg:p-20">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1579621970588-a35d0e7ab9b6?auto=format&fit=crop&q=80" alt="Finance Setup Background" class="w-full h-full object-cover opacity-30 mix-blend-overlay">
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-blue-950 via-blue-900/80 to-transparent"></div>
                <!-- Grid Pattern Overlay -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
            </div>

            <div class="relative z-10 max-w-lg">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-semibold tracking-wide uppercase mb-6 backdrop-blur-md">
                    Registrasi Akses
                </div>
                <h1 class="text-4xl lg:text-5xl font-serif-title font-semibold text-white leading-tight mb-6">
                    Akses eksklusif untuk pengelola keuangan.
                </h1>
                <p class="text-blue-100/80 text-lg leading-relaxed mb-12">
                    Daftarkan akun sebagai bendahara wilayah atau bendahara komsat untuk mulai mengelola laporan dan pembukuan GenBI.
                </p>

                <div class="grid grid-cols-2 gap-x-8 gap-y-4 pt-8 border-t border-white/10 text-sm">
                    <div>
                        <div class="text-white font-medium mb-1">Verifikasi Data</div>
                        <div class="text-blue-200/70">Wajib Email Valid</div>
                    </div>
                    <div>
                        <div class="text-white font-medium mb-1">Akses Sistem</div>
                        <div class="text-blue-200/70">Terbatas (Role Based)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Register Form -->
        <div class="flex-1 flex flex-col justify-center py-8 px-4 sm:px-8 lg:px-16 xl:px-24 bg-slate-50 relative overflow-y-auto">
            <div class="mx-auto w-full max-w-md my-auto bg-white lg:bg-transparent p-6 sm:p-10 lg:p-0 rounded-[2rem] shadow-2xl shadow-slate-200/50 lg:shadow-none border border-white/60 lg:border-none">

                <!-- Logo & Header -->
                <div class="flex items-center gap-4 mb-10">
                    <div class="bg-white p-2.5 rounded-xl shadow-sm border border-slate-200 flex items-center justify-center">
                        <img src="/assets/images/genbi-logo-with-text.png" alt="GenBI Logo" class="h-8 w-auto object-contain">
                    </div>
                    <div class="h-10 w-px bg-slate-200"></div>
                    <div>
                        <div class="text-sm font-bold text-slate-900 leading-tight">GenBI Provinsi Jambi</div>
                        <div class="text-xs font-medium text-slate-500">Keuangan Panel</div>
                    </div>
                </div>

                <div class="mb-8">
                    <div class="text-[11px] font-bold tracking-widest text-blue-600 uppercase mb-2">Secure Access</div>
                    <h2 class="text-3xl font-serif-title font-semibold text-slate-900 mb-2">Daftar akun</h2>
                    <p class="text-sm text-slate-500">Daftarkan akun baru khusus untuk tim pengurus keuangan.</p>
                </div>

                <form action="/keuangan/akun/register" method="POST" class="space-y-5">

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-slate-700">Email bendahara</label>
                        <input type="email" id="email" name="email" required placeholder="bendahara@genbijambi.com" class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                    </div>

                    <div class="space-y-2">
                        <label for="role" class="block text-sm font-medium text-slate-700">Jabatan / Peran</label>
                        <div class="relative">
                            <select id="role" name="role" required class="block w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih jabatan Anda...</option>
                                <option value="bendahara_unja">Bendahara Komsat UNJA</option>
                                <option value="bendahara_uin">Bendahara Komsat UIN</option>
                                <option value="bendahara_wilayah">Bendahara Wilayah Provinsi Jambi</option>
                                <option value="anggota">GenBI Jambi 2026</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required placeholder="••••••••" class="block w-full pl-4 pr-12 py-3 rounded-xl border border-slate-200 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                            <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                <svg id="eye-icon-1" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" class="block w-full pl-4 pr-12 py-3 rounded-xl border border-slate-200 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm">
                            <button type="button" onclick="togglePassword('confirm_password', 'eye-icon-2')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                <svg id="eye-icon-2" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors mt-2">
                        Buat Akun
                    </button>
                </form>

                <!-- Security Note Footer -->
                <div class="mt-8 bg-slate-50 lg:bg-blue-50/50 rounded-2xl border border-slate-200 lg:border-blue-100 p-5">
                    <h3 class="text-sm font-semibold text-slate-800 mb-1">Keamanan registrasi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Form ini memakai CSRF token, session aman, dan autentikasi backend. Pastikan data jabatan Anda benar.</p>
                </div>

                <p class="mt-8 text-center text-sm text-slate-500">
                    Sudah punya akun? <a href="/keuangan/akun/login" class="font-semibold text-blue-600 hover:text-blue-500">Masuk ke Dasbor</a>
                </p>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            }
        }
        <?php if ($error = \App\Core\Session::getFlash('swal_error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= addslashes($error) ?>',
                confirmButtonColor: '#2563eb',
                customClass: {
                    confirmButton: 'rounded-lg'
                }
            });
        <?php endif; ?>

        <?php if ($success = \App\Core\Session::getFlash('swal_success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($success) ?>',
                confirmButtonColor: '#2563eb',
                customClass: {
                    confirmButton: 'rounded-lg'
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>