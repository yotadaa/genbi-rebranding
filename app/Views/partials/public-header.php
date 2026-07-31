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
$phone = $site['phone'] ?? '089627896750';
$navItems = [
  ['label' => 'Beranda', 'key' => 'home'],
  ['label' => 'Tentang', 'key' => 'about'],
  ['label' => 'Tim', 'key' => 'team'],
  ['label' => 'Prestasi', 'key' => 'prestasi'],
  ['label' => 'Kegiatan', 'key' => 'kegiatan', 'href' => '/event', 'children' => [
    ['label' => 'Agenda', 'key' => 'event', 'href' => '/event'],
    ['label' => 'Program Utama', 'key' => 'feature', 'href' => '/feature'],
  ]],
  ['label' => 'Berita', 'key' => 'news'],
  ['label' => 'Kontak', 'key' => 'contact'],
];
$activeKey = $activeNav ?? '';
?>
<div id="site-header-shell" class="site-header-shell">
  <div class="top-strip hidden md:block">
    <div class="site-container flex h-9 items-center justify-between text-[13px] text-white/90">
      <div class="flex items-center gap-4">
        <a href="mailto:<?= $e($email) ?>" class="inline-flex items-center gap-2 hover:text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.24a2.25 2.25 0 0 1-1.07 1.92l-7.5 4.62a2.25 2.25 0 0 1-2.36 0l-7.5-4.62a2.25 2.25 0 0 1-1.07-1.92v-.24" />
          </svg><?= $e($email) ?></a>
        <span class="h-4 w-px bg-white/30"></span>
        <a href="tel:<?= $e($phone) ?>" class="inline-flex items-center gap-2 hover:text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.36-.97-.86-1.1l-4.42-1.1a1.13 1.13 0 0 0-1.17.42l-.97 1.3a1.13 1.13 0 0 1-1.21.39 12.04 12.04 0 0 1-7.15-7.15 1.13 1.13 0 0 1 .39-1.21l1.3-.97c.36-.27.52-.73.42-1.17L6.98 3.61a1.13 1.13 0 0 0-1.1-.86H4.5A2.25 2.25 0 0 0 2.25 5v1.75Z" />
          </svg><?= $e($phone) ?></a>
      </div>
      <nav class="flex items-center gap-3" aria-label="Social links">
        <a href="https://facebook.com/genbijambi" class="social-mini" aria-label="Facebook">Fb</a>
        <a href="https://instagram.com/genbijambi" class="social-mini" aria-label="Instagram">Ig</a>
        <a href="https://youtube.com/@genbijambi" class="social-mini" aria-label="YouTube">Yt</a>
        <a href="https://wa.me/6289627896750" class="social-mini" aria-label="WhatsApp">Wa</a>
      </nav>
    </div>
  </div>
  <header class="site-main-header border-b border-neutral-900/10 bg-[rgba(251,250,247,0.92)] backdrop-blur-xl">
    <div class="site-container flex h-20 items-center justify-between">
      <a data-transition href="<?= $url('home') ?>" class="flex items-center gap-3" aria-label="Go to home">
        <span class="logo-shell"><img src="<?= $e($logo) ?>" alt="<?= $e($siteName) ?>" class="h-9 w-auto" /></span>
        <span class="leading-tight">
          <span class="block text-[15px] font-semibold tracking-tight text-neutral-950">GenBI</span>
          <span class="block text-xs font-medium text-blue-800">Provinsi Jambi</span>
        </span>
      </a>
      <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
        <?php foreach ($navItems as $item): ?>
          <?php
          $children = is_array($item['children'] ?? null) ? $item['children'] : [];
          $isActive = $item['key'] === $activeKey || array_reduce($children, static fn(bool $carry, array $child): bool => $carry || ($child['key'] ?? '') === $activeKey, false);
          ?>
          <?php if ($children): ?>
            <div class="nav-dropdown <?= $isActive ? 'is-active' : '' ?>">
              <a data-transition href="<?= $e($item['href'] ?? $url($item['key'])) ?>" class="nav-link nav-dropdown-trigger <?= $isActive ? 'nav-link-active' : '' ?>" aria-haspopup="true" aria-expanded="false">
                <?= $e($item['label']) ?>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </a>
              <div class="nav-dropdown-menu" role="menu">
                <?php foreach ($children as $child): ?>
                  <a data-transition href="<?= $e($child['href'] ?? $url($child['key'] ?? '')) ?>" role="menuitem" class="<?= ($child['key'] ?? '') === $activeKey ? 'is-active' : '' ?>"><?= $e($child['label'] ?? '') ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <a data-transition href="<?= $url($item['key']) ?>" class="nav-link <?= $isActive ? 'nav-link-active' : '' ?>"><?= $e($item['label']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hidden items-center gap-3 lg:flex">
        <a data-transition href="<?= $url('contact') ?>" class="btn btn-primary">Hubungi Kami <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg></a>
      </div>
      <button id="open-menu" class="btn-icon lg:hidden" aria-label="Open menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg></button>
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
        <?php
        $children = is_array($item['children'] ?? null) ? $item['children'] : [];
        $isActive = $item['key'] === $activeKey || array_reduce($children, static fn(bool $carry, array $child): bool => $carry || ($child['key'] ?? '') === $activeKey, false);
        ?>
        <?php if ($children): ?>
          <div class="mobile-link-group">
            <a data-transition href="<?= $e($item['href'] ?? $url($item['key'])) ?>" class="mobile-link <?= $isActive ? 'mobile-link-active' : '' ?>"><?= $e($item['label']) ?><span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg></span></a>
            <div class="mobile-sub-links">
              <?php foreach ($children as $child): ?>
                <a data-transition href="<?= $e($child['href'] ?? $url($child['key'] ?? '')) ?>" class="mobile-sub-link <?= ($child['key'] ?? '') === $activeKey ? 'is-active' : '' ?>"><?= $e($child['label'] ?? '') ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <a data-transition href="<?= $url($item['key']) ?>" class="mobile-link <?= $isActive ? 'mobile-link-active' : '' ?>"><?= $e($item['label']) ?><span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
              </svg></span></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
      <strong><?= $e($siteName) ?></strong><br /><?= $e($site['tagline'] ?? 'Bersama GenBI, Energi untuk Negeri') ?>
    </div>
  </div>
</div>