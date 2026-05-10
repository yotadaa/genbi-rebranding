<?php
/** @var callable $e */
/** @var callable $url */
$site = $site ?? $siteSettings?->site() ?? [];
$logo = $site['logo'] ?? 'https://genbijambi.com/public/uploads/logo.png';
$siteName = $site['name'] ?? 'GenBI Provinsi Jambi';
$tagline = $site['tagline'] ?? 'Bersama GenBI, Energi untuk Negeri';
$email = $site['email'] ?? 'genbijambibi@gmail.com';
$phone = $site['phone'] ?? '089627896750';
$address = $site['address'] ?? 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361';
$navItems = [
    ['label' => 'Home', 'key' => 'home'],
    ['label' => 'About', 'key' => 'about'],
    ['label' => 'Team', 'key' => 'team'],
    ['label' => 'Prestasi', 'key' => 'prestasi'],
    ['label' => 'News', 'key' => 'news'],
    ['label' => 'Contact', 'key' => 'contact'],
];
?>
<section class="border-t border-neutral-900/10 bg-blue-950 text-white">
  <div class="site-container grid gap-10 py-14 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
    <div>
      <div class="flex items-center gap-3">
        <span class="logo-shell logo-shell-light"><img src="<?= $e($logo) ?>" alt="<?= $e($siteName) ?>" class="h-10 w-auto" /></span>
        <div>
          <p class="font-semibold"><?= $e($siteName) ?></p>
          <p class="text-sm text-blue-100/80"><?= $e($tagline) ?></p>
        </div>
      </div>
      <p class="mt-5 max-w-md text-sm leading-7 text-blue-100/80">Website publik untuk profil komunitas, kegiatan, prestasi, berita, anggota, dan kontak resmi GenBI Jambi.</p>
    </div>
    <div>
      <h3 class="text-sm font-semibold text-white">Navigasi</h3>
      <div class="mt-4 grid gap-2 text-sm text-blue-100/80">
        <?php foreach ($navItems as $item): ?>
          <a data-transition href="<?= $url($item['key']) ?>" class="w-fit hover:text-white"><?= $e($item['label']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <h3 class="text-sm font-semibold text-white">Kontak</h3>
      <div class="mt-4 grid gap-2 text-sm leading-6 text-blue-100/80">
        <a href="mailto:<?= $e($email) ?>" class="hover:text-white"><?= $e($email) ?></a>
        <a href="tel:<?= $e($phone) ?>" class="hover:text-white"><?= $e($phone) ?></a>
        <p><?= $e($address) ?></p>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10 py-5 text-center text-xs text-blue-100/70">Copyright © <?= date('Y') ?>, GenBI Provinsi Jambi.</div>
</section>
<button id="back-to-top" class="back-to-top" aria-label="Back to top">↑</button>
