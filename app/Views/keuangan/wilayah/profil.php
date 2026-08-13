<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Bendahara Wilayah - GenBI Jambi</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS Tailwind & Kustom -->
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/assets/css/styles.css?v=<?= time() ?>">
    <style>
        body {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            background-color: #f8fafc;
            /* slate-50 */
        }

        body.page-ready {
            opacity: 1;
        }

        /* Mobile Sidebar Toggle Transition */
        #mobile-sidebar {
            transition: transform 0.3s ease-in-out;
        }

        #mobile-sidebar.open {
            transform: translateX(0);
        }

        #mobile-sidebar.closed {
            transform: translateX(-100%);
        }
    </style>
</head>

<body class="text-slate-800 antialiased selection:bg-blue-200 selection:text-blue-900 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex transition-all duration-300 relative z-20">
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="font-bold text-xl text-blue-700 tracking-tight flex items-center gap-2">
                <img src="/assets/images/logo-genbi.png" alt="Logo GenBI" class="h-16 object-contain" onerror="this.style.display='none'">
            </div>
        </div>

        <!-- Role Badge -->
        <div class="px-6 py-4">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 w-full justify-center">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                Bendahara Wilayah
            </span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 space-y-1.5 mt-2 overflow-y-auto">
            <p class="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Menu Utama</p>

            <?php $isDashboard = ($activeMenu ?? '') === 'dashboard'; ?>
            <a href="/keuangan/bendahara/wilayah/dashboard" class="flex items-center gap-3 px-3 py-2.5 <?= $isDashboard ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 <?= $isDashboard ? 'text-blue-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>

            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Komsat UNJA
            </a>

            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Komsat UIN
            </a>

            <p class="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6">Pengaturan</p>

            <?php $isProfil = ($activeMenu ?? '') === 'profil'; ?>
            <a href="/keuangan/bendahara/wilayah/profil" class="flex items-center gap-3 px-3 py-2.5 <?= $isProfil ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 <?= $isProfil ? 'text-blue-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profile
            </a>
        </nav>

        <!-- Logout Button -->
        <div class="p-4 border-t border-slate-100">
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Keluar
            </a>
        </div>
    </aside>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden" onclick="toggleMobileMenu()"></div>

    <!-- Mobile Sidebar -->
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col z-50 closed md:hidden">
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
            <div class="font-bold text-xl text-blue-700 tracking-tight flex items-center gap-2">
                <img src="/assets/images/logo-genbi.png" alt="Logo GenBI" class="h-16 object-contain" onerror="this.style.display='none'">
            </div>
            <button onclick="toggleMobileMenu()" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Role Badge -->
        <div class="px-6 py-4">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 w-full justify-center">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                Bendahara Wilayah
            </span>
        </div>

        <nav class="flex-1 px-4 space-y-1.5 mt-2 overflow-y-auto">
            <p class="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Menu Utama</p>
            <?php $isDashboardM = ($activeMenu ?? '') === 'dashboard'; ?>
            <a href="/keuangan/bendahara/wilayah/dashboard" class="flex items-center gap-3 px-3 py-2.5 <?= $isDashboardM ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 <?= $isDashboardM ? 'text-blue-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Komsat UNJA
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Komsat UIN
            </a>
            <p class="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6">Pengaturan</p>
            <?php $isProfilM = ($activeMenu ?? '') === 'profil'; ?>
            <a href="/keuangan/bendahara/wilayah/profil" class="flex items-center gap-3 px-3 py-2.5 <?= $isProfilM ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?> rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 <?= $isProfilM ? 'text-blue-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profile
            </a>
        </nav>
        <div class="p-4 border-t border-slate-100">
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-slate-50">

        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-20 relative">
            <div class="flex items-center">
                <button type="button" onclick="window.toggleMobileMenu()" class="md:hidden text-slate-500 hover:text-slate-700 focus:outline-none p-2 -ml-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-xl font-bold text-slate-800 ml-2 md:ml-0">Profil Pengguna</h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-slate-700 leading-tight">Nama Bendahara</p>
                    <p class="text-xs text-slate-500">Bendahara Wilayah</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 border-2 border-white shadow-sm flex items-center justify-center text-blue-700 font-bold overflow-hidden">
                    NB
                </div>
            </div>
        </header>

        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-6xl mx-auto space-y-6">

                <!-- Form Profil -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-800">Informasi Profil</h2>
                        <p class="text-sm text-slate-500 mt-1">Lengkapi data diri Anda sebagai Bendahara. Data ini akan digunakan untuk keperluan pencatatan dan laporan.</p>
                    </div>

                    <form action="#" method="POST" class="space-y-6">
                        <!-- Nama Bendahara -->
                        <div>
                            <label for="nama_bendahara" class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="nama_bendahara" name="nama_bendahara" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <!-- Periode -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tahun_periode_awal" class="block text-sm font-medium text-slate-700 mb-2">Tahun Periode Awal</label>
                                <input type="number" id="tahun_periode_awal" name="tahun_periode_awal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" placeholder="Contoh: 2026" required>
                            </div>
                            <div>
                                <label for="tahun_periode_akhir" class="block text-sm font-medium text-slate-700 mb-2">Tahun Periode Akhir</label>
                                <input type="number" id="tahun_periode_akhir" name="tahun_periode_akhir" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" placeholder="Contoh: 2027" required>
                            </div>
                        </div>

                        <!-- Tempat & Jenis Kelamin -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tempat" class="block text-sm font-medium text-slate-700 mb-2">Jabatan / Wilayah</label>
                                <select id="tempat" name="tempat" class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 cursor-not-allowed focus:outline-none" disabled>
                                    <option value="wilayah" selected>Bendahara Wilayah</option>
                                    <option value="komsat unja">Bendahara Komsat UNJA</option>
                                    <option value="komsat uin">Bendahara Komsat UIN</option>
                                </select>
                            </div>
                            <div>
                                <label for="jenis_kelamin" class="block text-sm font-medium text-slate-700 mb-2">Jenis Kelamin</label>
                                <select id="jenis_kelamin" name="jenis_kelamin" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" required>
                                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Program Studi & Semester -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="program_studi" class="block text-sm font-medium text-slate-700 mb-2">Program Studi</label>
                                <input type="text" id="program_studi" name="program_studi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" placeholder="Contoh: Sistem Informasi" required>
                            </div>
                            <div>
                                <label for="semester_studi" class="block text-sm font-medium text-slate-700 mb-2">Semester Studi</label>
                                <input type="number" id="semester_studi" name="semester_studi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none" placeholder="Contoh: 5" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/30 transition-all active:scale-[0.98]">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- Script to remove opacity and handle mobile menu -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('page-ready');
        });

        // Make it globally accessible
        window.toggleMobileMenu = function() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('mobile-overlay');

            if (sidebar.classList.contains('closed')) {
                // Open menu
                sidebar.classList.remove('closed');
                sidebar.classList.add('open');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                // Close menu
                sidebar.classList.remove('open');
                sidebar.classList.add('closed');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        };
    </script>
</body>

</html>