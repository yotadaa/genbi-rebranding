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
  ['label' => 'Buku', 'key' => 'buku', 'href' => '/buku'],
  ['label' => 'Kegiatan', 'key' => 'kegiatan', 'href' => '/event', 'children' => [
    ['label' => 'Agenda', 'key' => 'event', 'href' => '/event'],
    ['label' => 'Program Utama', 'key' => 'feature', 'href' => '/feature'],
  ]],
  ['label' => 'Berita', 'key' => 'news'],
  ['label' => 'Kontak', 'key' => 'contact'],
];
$activeKey = $activeNav ?? '';
if (empty($activeKey)) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $segments = explode('/', trim(str_replace('/index.php', '', (string)$uriPath), '/'));
    if (in_array('team', $segments)) $activeKey = 'team';
    elseif (in_array('news', $segments)) $activeKey = 'news';
    elseif (in_array('about', $segments)) $activeKey = 'about';
    elseif (in_array('prestasi', $segments)) $activeKey = 'prestasi';
    elseif (in_array('buku', $segments)) $activeKey = 'buku';
    elseif (in_array('event', $segments) || in_array('feature', $segments)) $activeKey = 'kegiatan';
    elseif (in_array('contact', $segments)) $activeKey = 'contact';
    else $activeKey = 'home';
}
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
        <!-- Facebook -->
        <a href="https://facebook.com/genbijambi" class="social-mini" aria-label="Facebook">
          <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
          </svg>
        </a>

        <!-- Instagram -->
        <a href="https://www.instagram.com/genbi_jambi/" class="social-mini" aria-label="Instagram">
          <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z" />
          </svg>
        </a>

        <!-- YouTube -->
        <a href="https://youtube.com/@genbijambi" class="social-mini" aria-label="YouTube">
          <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
          </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/6289627896750" class="social-mini" aria-label="WhatsApp">
          <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 1.766 6.26L0 24l5.908-1.765A11.96 11.96 0 0 0 11.944 24c6.627 0 12-5.373 12-12s-5.373-12-12-12zM12 22a9.95 9.95 0 0 1-5.07-1.39l-.36-.22-3.77 1.12 1.01-3.66-.23-.37A9.97 9.97 0 0 1 2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zm5.48-7.58c-.3-.15-1.78-.88-2.06-.98-.28-.1-.49-.15-.7.15-.2.3-.8.98-.98 1.18-.18.2-.36.22-.66.07-.3-.15-1.28-.47-2.43-1.5-.9-.8-1.51-1.78-1.69-2.08-.18-.3-.02-.46.13-.61.13-.13.3-.34.45-.5.15-.15.2-.26.3-.43.1-.17.05-.32-.02-.47-.07-.15-.65-1.57-.9-2.16-.24-.58-.49-.5-.67-.51-.17 0-.37-.01-.57-.01-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.58-.09 1.78-.73 2.03-1.44.25-.71.25-1.32.18-1.44-.07-.12-.27-.2-.57-.35z" />
          </svg>
        </a>
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
      <div class="hidden items-center gap-3 lg:flex shrink-0">
        <a data-transition href="/admin/dashboard" class="btn btn-secondary">Pratinjau Admin</a>
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
      <a data-transition href="/admin/dashboard" class="mobile-link">Pratinjau Admin<span class="mobile-link-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
          </svg></span></a>
    </nav>
    <div class="mt-8 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-950">
      <strong><?= $e($siteName) ?></strong><br /><?= $e($site['tagline'] ?? 'Bersama GenBI, Energi untuk Negeri') ?>
    </div>
  </div>
</div>