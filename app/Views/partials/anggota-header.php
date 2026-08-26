<?php

/** @var callable $e */
/** @var callable $url */
if (!isset($siteSettings)) {
  $siteSettings = null;
}

$site = $site ?? $siteSettings?->site() ?? [];
$logo = $site['logo'] ?? 'https://genbijambi.com/public/uploads/logo.png';
$siteName = $site['name'] ?? 'GenBI Provinsi Jambi';
$email = $site['email'] ?? 'genbijambibi@gmail.com';
$phone = $site['phone'] ?? '085669152702';
$navItems = [
  ['label' => 'Keuangan Wilayah', 'key' => 'wilayah', 'href' => '/keuangan/anggota/wilayah'],
  ['label' => 'Keuangan UNJA', 'key' => 'unja', 'href' => '/keuangan/anggota/unja'],
  ['label' => 'Keuangan UIN', 'key' => 'uin', 'href' => '/keuangan/anggota/uin'],
];
$activeKey = $activeNav ?? 'wilayah';
?>
<div id="site-header-shell" class="site-header-shell">
  <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
    <div class="site-container flex h-20 items-center justify-between">
      <a data-transition href="/keuangan/anggota/wilayah" class="flex items-center gap-3" aria-label="Go to home">
        <span class="logo-shell"><img src="<?= $e($logo) ?>" alt="<?= $e($siteName) ?>" class="h-9 w-auto" /></span>
        <span class="leading-tight">
          <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
          <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
        </span>
      </a>
      <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
        <?php foreach ($navItems as $item): ?>
          <?php $isActive = $item['key'] === $activeKey; ?>
          <a data-transition href="<?= $e($item['href']) ?>" class="nav-link <?= $isActive ? 'nav-link-active' : '' ?>"><?= $e($item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="hidden items-center gap-3 lg:flex shrink-0">
        <form action="/keuangan/akun/logout" method="POST" class="inline-block m-0">
          <input type="hidden" name="_csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">
          <button type="submit" class="btn btn-secondary flex items-center gap-2 text-red-600 hover:bg-red-50 hover:border-red-200">
            Logout
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
          </button>
        </form>
      </div>
      <div class="lg:hidden flex items-center">
        <button id="open-menu" class="btn-icon" aria-label="Open menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg></button>
      </div>
    </div>
  </header>
</div>
<div id="site-header-spacer" aria-hidden="true"></div>
<div id="mobile-panel" class="fixed inset-0 z-[70] hidden bg-neutral-950/35 backdrop-blur-sm lg:hidden">
  <div class="mobile-sheet">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="logo-shell"><img src="<?= $e($logo) ?>" alt="<?= $e($siteName) ?>" class="h-8 w-auto" /></span>
        <span class="font-semibold text-neutral-950">Menu</span>
      </div>
      <button id="close-menu" class="btn-icon" aria-label="Close menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg></button>
    </div>
    <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
      <?php foreach ($navItems as $item): ?>
        <?php $isActive = $item['key'] === $activeKey; ?>
        <a data-transition href="<?= $e($item['href']) ?>" class="mobile-link <?= $isActive ? 'mobile-link-active' : '' ?>"><?= $e($item['label']) ?><span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
            </svg></span></a>
      <?php endforeach; ?>
      <form action="/keuangan/akun/logout" method="POST" class="mt-2 block">
        <input type="hidden" name="_csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">
        <button type="submit" class="mobile-link text-red-600 w-full text-left">Logout<span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg></span></button>
      </form>
    </nav>
    <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
      <strong><?= $e($siteName) ?></strong><br /><?= $e($site['tagline'] ?? 'Bersama GenBI, Energi untuk Negeri') ?>
    </div>
  </div>
</div>