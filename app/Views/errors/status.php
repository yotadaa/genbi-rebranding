<?php
/** @var callable $e */
$status = (int) ($status ?? 500);
$title = (string) ($title ?? 'Terjadi kesalahan');
$message = (string) ($message ?? 'Maaf, sistem sedang mengalami gangguan.');
$suggestions = match ($status) {
    404 => ['Periksa kembali alamat URL.', 'Gunakan navigasi utama untuk menemukan halaman publik.', 'Buka halaman berita, agenda, prestasi, atau kontak.'],
    403 => ['Pastikan Anda sudah login jika halaman bersifat internal.', 'Kembali ke halaman publik jika tidak memiliki akses admin.'],
    503 => ['Tunggu beberapa saat lalu muat ulang halaman.', 'Jika mendesak, hubungi kontak resmi GenBI Jambi.'],
    default => ['Muat ulang halaman.', 'Kembali ke beranda.', 'Laporkan jika masalah terus terjadi.'],
};
?>
<section class="error-page-section home-section-surface" style="padding-top: clamp(9.5rem, 18vh, 12rem); padding-bottom: clamp(5rem, 10vh, 7rem);">
  <div class="site-container">
    <div class="rounded-[2rem] border border-neutral-900/10 bg-white p-8 shadow-[0_24px_70px_rgb(23_23_23/0.07)] md:p-12">
      <p class="eyebrow">Error <?= $e((string) $status) ?></p>
      <h1 class="serif mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-neutral-950 md:text-6xl"><?= $e($title) ?></h1>
      <p class="mt-5 max-w-2xl text-base leading-8 text-neutral-600 md:text-lg"><?= $e($message) ?></p>
      <ul class="mt-7 grid gap-3 text-sm leading-6 text-neutral-600 md:grid-cols-3">
        <?php foreach ($suggestions as $suggestion): ?>
          <li class="rounded-2xl bg-blue-50 p-4 text-blue-950"><?= $e($suggestion) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
        <a href="/news" class="btn btn-secondary">Baca Berita</a>
        <a href="/contact" class="btn btn-secondary">Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>
