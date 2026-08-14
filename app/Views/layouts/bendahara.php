<?php
/** @var callable $e */
$siteSettings = $siteSettings ?? null;
$sitePayload = is_object($siteSettings) && method_exists($siteSettings, 'clientPayload') ? $siteSettings->clientPayload() : ($site ?? []);
$themeKey = is_object($siteSettings) && method_exists($siteSettings, 'themeKey') ? $siteSettings->themeKey('public') : 'genbi';
$inlineThemeCss = is_object($siteSettings) && method_exists($siteSettings, 'themeInlineCss') ? $siteSettings->themeInlineCss('public') : '';
$settingsJson = json_encode($sitePayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$content = $content ?? '';
$activeMenu = $activeMenu ?? 'dashboard';
?>
<!doctype html>
<html lang="id" data-theme="<?= $e($themeKey) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $e(\App\Services\CsrfService::token()) ?>">
  <?= $meta ?? '<title>' . $e($title ?? 'Bendahara GenBI') . ' - Keuangan</title>' ?>
  <?php if ($inlineThemeCss !== ''): ?><style><?= $inlineThemeCss ?></style><?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
  <link rel="stylesheet" href="/assets/css/tailwind.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?= $jsonld ?? '' ?>
  <?php if (!empty($sitePayload['favicon'])): ?><link rel="icon" href="<?= $e((string) $sitePayload['favicon']) ?>"><?php endif; ?>
</head>
<body class="page-ready <?= $e($bodyClass ?? '') ?> bg-slate-50 text-slate-800 font-sans antialiased" data-ssr="true">
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-blue-700 focus:rounded focus:shadow-lg">Langsung ke konten</a>
  
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden md:hidden"></div>
    
    <aside id="sidebar-menu" class="fixed md:static inset-y-0 left-0 w-64 bg-white border-r border-slate-200 flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <span class="text-xl font-bold text-blue-700 tracking-tight">GenBI Keuangan</span>
        </div>
        
        <div class="p-4 flex flex-col gap-1 flex-1 overflow-y-auto">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4 px-2">Menu Utama</div>
            <a href="/keuangan/bendahara/wilayah/dashboard" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $activeMenu === 'dashboard' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'dashboard' ? 'text-blue-700' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            
            <a href="/keuangan/bendahara/wilayah/transaksi" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $activeMenu === 'transaksi' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'transaksi' ? 'text-blue-700' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Transaksi
            </a>

            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 px-2">Pengaturan</div>
            <a href="/keuangan/bendahara/wilayah/profil" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $activeMenu === 'profil' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                <svg class="w-5 h-5 mr-3 <?= $activeMenu === 'profil' ? 'text-blue-700' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Profil
            </a>
        </div>
        
        <div class="p-4 border-t border-slate-100">
            <form action="/keuangan/akun/logout" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">
                <button type="submit" class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
        <!-- Top Navbar (Mobile menu trigger & Profile quick view) -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0 md:hidden">
            <div class="flex items-center">
                <button type="button" id="btn-mobile-menu" class="text-slate-500 hover:text-slate-700 focus:outline-none p-2 mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <span class="text-lg font-bold text-blue-700">GenBI Keuangan</span>
            </div>
            <form action="/keuangan/akun/logout" method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">
                <button type="submit" class="text-red-600 hover:text-red-800 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </header>

        <!-- Page Content -->
        <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <?= $content ?>
        </main>
        
        <footer class="py-4 text-center text-slate-500 text-xs border-t border-slate-200 mt-auto bg-white shrink-0">
            Copyright &copy; Bendahara GenBI Jambi
        </footer>
    </div>
  </div>

  <div id="modal-root"></div>
  <script>
    window.GenBISiteSettings = <?= $settingsJson ?>;
  </script>
  <script defer src="/assets/js/bendahara.js"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
