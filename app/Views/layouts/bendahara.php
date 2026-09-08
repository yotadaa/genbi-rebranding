<?php

/** @var callable $e */
$siteSettings = $siteSettings ?? null;
$sitePayload = is_object($siteSettings) && method_exists($siteSettings, 'clientPayload') ? $siteSettings->clientPayload() : ($site ?? []);
$themeKey = is_object($siteSettings) && method_exists($siteSettings, 'themeKey') ? $siteSettings->themeKey('public') : 'genbi';
$inlineThemeCss = is_object($siteSettings) && method_exists($siteSettings, 'themeInlineCss') ? $siteSettings->themeInlineCss('public') : '';
$settingsJson = json_encode($sitePayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$content = $content ?? '';
$activeMenu = $activeMenu ?? 'dashboard';

// Helper for title
$pageTitle = 'Dashboard';
if ($activeMenu === 'transaksi') $pageTitle = 'Transaksi';
if ($activeMenu === 'profil') $pageTitle = 'Profil';
if ($activeMenu === 'komsat_unja') $pageTitle = 'Komsat UNJA';
if ($activeMenu === 'komsat_uin') $pageTitle = 'Komsat UIN';
?>
<!doctype html>
<html lang="id" data-theme="<?= $e($themeKey) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $e(\App\Services\CsrfService::token()) ?>">
    <?= $meta ?? '<title>' . $e($title ?? 'Bendahara GenBI') . ' - Keuangan</title>' ?>
    <?php if ($inlineThemeCss !== ''): ?><style>
            <?= $inlineThemeCss ?>
        </style><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap">
    </noscript>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?= $jsonld ?? '' ?>
    <?php if (!empty($sitePayload['favicon'])): ?>
        <link rel="icon" href="<?= $e((string) $sitePayload['favicon']) ?>"><?php endif; ?>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7fb;
        }

        #sidebar-menu {
            background: linear-gradient(180deg, #f6fbff 0%, #eaf4ff 52%, #dfeefc 100%);
            border-right: 1px solid #c9dff3;
            box-shadow: 14px 0 34px rgb(36 106 167 / 0.08);
        }

        #topbar-header {
            background: rgba(242, 248, 255, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201, 223, 243, 0.5);
            box-shadow: 0 10px 30px rgb(36 106 167 / 0.07);
        }

        .font-serif-title {
            font-family: 'Source Serif 4', serif;
            letter-spacing: -0.02em;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0.25rem;
        }

        .sidebar-link.active {
            background-color: #3b5998;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(59, 89, 152, 0.3);
        }

        .sidebar-link.inactive {
            color: #193b5d;
        }

        .sidebar-link.inactive:hover {
            background-color: rgba(255, 255, 255, 0.5);
            color: #0c3572;
        }

        .sidebar-section-title {
            color: #64748b;
        }
    </style>
</head>

<body class="page-ready <?= $e($bodyClass ?? '') ?> text-slate-800 antialiased" data-ssr="true">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-blue-700 focus:rounded focus:shadow-lg">Langsung ke konten</a>

    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Backdrop -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden md:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar-menu" class="fixed md:static inset-y-0 left-0 w-64 flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shrink-0 shadow-xl md:shadow-none">

            <!-- Sidebar Header (Logo) -->
            <div class="h-[72px] flex items-center px-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <img src="https://genbijambi.com/public/uploads/logo.png" alt="GenBI" class="h-6 w-auto object-contain">
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900 leading-tight tracking-tight">GenBI Wilayah </div>
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Keuangan Panel</div>
                    </div>
                </div>
                <!-- Close mobile menu button -->
                <button type="button" id="btn-close-mobile-menu" class="md:hidden ml-auto text-slate-400 hover:text-slate-600 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <div class="p-4 flex flex-col gap-1.5 flex-1 overflow-y-auto">

                <a href="/keuangan/bendahara/wilayah/dashboard" class="sidebar-link <?= $activeMenu === 'dashboard' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'dashboard' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="/keuangan/bendahara/wilayah/transaksi" class="sidebar-link <?= $activeMenu === 'transaksi' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'transaksi' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Transaksi
                </a>

                <a href="/keuangan/bendahara/wilayah/kegiatan" class="sidebar-link <?= $activeMenu === 'kegiatan' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'kegiatan' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Kegiatan
                </a>

                <div class="mt-6 mb-2 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akses Komsat</div>

                <a href="/keuangan/bendahara/wilayah/unja" class="sidebar-link <?= $activeMenu === 'komsat_unja' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'komsat_unja' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Komsat UNJA
                </a>

                <a href="/keuangan/bendahara/wilayah/uin" class="sidebar-link <?= $activeMenu === 'komsat_uin' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'komsat_uin' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Komsat UIN
                </a>

                <div class="mt-6 mb-2 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pengaturan</div>

                <a href="/keuangan/bendahara/wilayah/profil" class="sidebar-link <?= $activeMenu === 'profil' ? 'active' : 'inactive' ?>">
                    <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'profil' ? 'text-white' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profil
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#f8fafc]">

            <!-- Top Navbar -->
            <header id="topbar-header" class="h-[72px] flex items-center justify-between px-6 shrink-0 sticky top-0 z-30">
                <!-- Left Side: Title & Menu Trigger -->
                <div class="flex items-center gap-4">
                    <button type="button" id="btn-toggle-sidebar" class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 focus:outline-none transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div>
                        <div class="text-[10px] font-bold text-blue-600 uppercase tracking-widest leading-none mb-1">Keuangan Panel</div>
                        <div class="text-xl font-bold text-slate-800 leading-none"><?= $e($pageTitle) ?></div>
                    </div>
                </div>

                <!-- Right Side: Actions -->
                <div class="flex items-center gap-3">

                    <form action="/keuangan/akun/logout" method="POST" class="inline">
                        <input type="hidden" name="_csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">
                        <button type="submit" class="hidden sm:flex items-center px-4 py-2 rounded-full border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition-all shadow-sm">
                            Logout
                        </button>
                        <!-- Mobile Logout Icon Only -->
                        <button type="submit" class="sm:hidden flex items-center justify-center w-10 h-10 rounded-full border border-slate-200 bg-white text-slate-500 hover:text-red-600 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>

                    <div class="w-10 h-10 bg-white rounded-full border border-slate-200 p-1.5 shadow-sm flex items-center justify-center ml-2">
                        <img src="https://genbijambi.com/public/uploads/logo.png" alt="GenBI" class="w-full h-full object-contain">
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" class="flex-1 overflow-y-auto p-6 lg:p-10">
                <?= $content ?>
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 text-center text-slate-500 text-xs border-t border-slate-200 bg-white shrink-0">
                Copyright &copy; Bendahara GenBI Jambi
            </footer>
        </div>
    </div>

    <div id="modal-root"></div>
    <script>
        window.GenBISiteSettings = <?= $settingsJson ?>;

        // Sidebar Toggle Logic
        document.addEventListener('DOMContentLoaded', () => {
            const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
            const btnCloseMobileMenu = document.getElementById('btn-close-mobile-menu');
            const sidebarMenu = document.getElementById('sidebar-menu');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');

            function toggleMenu() {
                if (window.innerWidth < 768) {
                    // Mobile behavior (transform)
                    sidebarMenu.classList.toggle('-translate-x-full');
                    sidebarBackdrop.classList.toggle('hidden');
                } else {
                    // Desktop behavior (negative margin for smooth collapse)
                    sidebarMenu.classList.toggle('md:-ml-64');
                }
            }

            if (btnToggleSidebar) btnToggleSidebar.addEventListener('click', toggleMenu);
            if (btnCloseMobileMenu) btnCloseMobileMenu.addEventListener('click', toggleMenu);
            if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', toggleMenu);

            // Handle resize events to prevent stuck sidebars
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    if (!sidebarBackdrop.classList.contains('hidden')) {
                        sidebarBackdrop.classList.add('hidden');
                    }
                    // Don't auto-remove md:-ml-64 so user preference is kept on desktop
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="/assets/js/bendahara.js"></script>
    <script>
        function confirmDelete(formElement) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-lg',
                    cancelButton: 'rounded-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    formElement.submit();
                }
            })
        }

        <?php if (\App\Core\Session::has('swal_success') || \App\Core\Session::has('swal_error') || isset($_SESSION['_flash']['swal_success']) || isset($_SESSION['_flash']['swal_error'])): ?>
            document.addEventListener("DOMContentLoaded", function() {
                <?php if ($msg = \App\Core\Session::getFlash('swal_success')): ?>
                    Swal.fire({
                        title: 'Berhasil!',
                        html: <?= json_encode($msg) ?>,
                        icon: 'success',
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'rounded-lg'
                        }
                    });
                <?php endif; ?>

                <?php if ($msg = \App\Core\Session::getFlash('swal_error')): ?>
                    Swal.fire({
                        title: 'Gagal!',
                        html: <?= json_encode($msg) ?>,
                        icon: 'error',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'rounded-lg'
                        }
                    });
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
    <?= $scripts ?? '' ?>
</body>

</html>