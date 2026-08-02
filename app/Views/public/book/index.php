<?php
// Data dummy katalog buku untuk preview desain sebelum database dihubungkan
$dummyBooks = [
    [
        'id' => 1,
        'judul' => 'Pedoman Beasiswa Bank Indonesia 2026',
        'kategori' => 'Pedoman BI',
        'penulis' => 'Bank Indonesia Jambi',
        'tahun' => '2026',
        'halaman' => '48 Halaman',
        'file_size' => '2.4 MB',
        'cover_bg' => 'from-blue-900 via-indigo-950 to-blue-800',
        'tagline' => 'Panduan Lengkap Syarat & Tata Kelola Beasiswa',
        'cover_image' => '' // Kalau sudah ada foto cover: 'assets/img/buku/cover-1.jpg'
    ],
    [
        'id' => 2,
        'judul' => 'Majalah Suara GenBI Edisi #14: Energi untuk Negeri',
        'kategori' => 'Majalah',
        'penulis' => 'Tim Redaksi GenBI Jambi',
        'tahun' => '2025',
        'halaman' => '64 Halaman',
        'file_size' => '12.8 MB',
        'cover_bg' => 'from-amber-700 via-amber-900 to-yellow-950',
        'tagline' => 'Wadah Informasi, Inspirasi & Kreativitas Mahasiswa',
        'cover_image' => ''
    ],
    [
        'id' => 3,
        'judul' => 'Buletin Kebanksentralan & Literasi QRIS',
        'kategori' => 'Buletin',
        'penulis' => 'Divisi Riset & Edukasi',
        'tahun' => '2025',
        'halaman' => '24 Halaman',
        'file_size' => '4.1 MB',
        'cover_bg' => 'from-emerald-800 via-teal-950 to-slate-900',
        'tagline' => 'Mendorong Akselarasi Transaksi Digital UMKM Jambi',
        'cover_image' => ''
    ],
    [
        'id' => 4,
        'judul' => 'Buku Saku Kepemimpinan & Manajemen Komunitas',
        'kategori' => 'Materi Edukasi',
        'penulis' => 'Deputi Pengembangan Sumber Daya Manusia',
        'tahun' => '2025',
        'halaman' => '36 Halaman',
        'file_size' => '3.5 MB',
        'cover_bg' => 'from-slate-900 via-blue-950 to-indigo-900',
        'tagline' => 'Membentuk Karakter Pemimpin Muda Bermarwah',
        'cover_image' => ''
    ],
    [
        'id' => 5,
        'judul' => 'Laporan Pengabdian Masyarakat GenBI Mengajar 2025',
        'kategori' => 'Publikasi',
        'penulis' => 'Divisi Pengabdian Masyarakat',
        'tahun' => '2025',
        'halaman' => '52 Halaman',
        'file_size' => '8.0 MB',
        'cover_bg' => 'from-blue-950 via-slate-900 to-neutral-900',
        'tagline' => 'Aksi Nyata Membangun Negeri dari Pelosok Desa',
        'cover_image' => ''
    ],
    [
        'id' => 6,
        'judul' => 'GenBI Career Guide: Sukses Meniti Dunia Kerja',
        'kategori' => 'Buku Saku',
        'penulis' => 'Divisi Kewirausahaan & Karir',
        'tahun' => '2024',
        'halaman' => '40 Halaman',
        'file_size' => '5.2 MB',
        'cover_bg' => 'from-sky-800 via-blue-900 to-indigo-950',
        'tagline' => 'Tips Wawancara, CV & Persiapan Pascakampus',
        'cover_image' => ''
    ],
];
?>

<!-- Hero Banner (Sesuai Gaya GenBI Jambi) -->
<section class="public-inner-hero py-16 md:py-24 bg-gradient-to-b from-blue-950 to-blue-900 text-white">
    <div class="article-container fade-up text-center md:text-left">
        <p class="eyebrow inline-block px-3 py-1 bg-blue-800/60 rounded-full text-blue-200 text-xs font-semibold uppercase tracking-widest border border-blue-700/50">E-Library &amp; Publikasi</p>
        <h1 class="page-title mt-5 text-3xl md:text-5xl font-extrabold tracking-tight">Katalog Buku GenBI Jambi.</h1>
        <p class="lead mt-6 max-w-2xl text-blue-100/90 text-base md:text-lg leading-relaxed">
            Wadah literasi digital, informasi kebanksentralan, majalah inspiratif, dan karya ilmiah dari anggota GenBI Provinsi Jambi.
        </p>
    </div>
</section>

<!-- Konten Utama Katalog & Grid Buku -->
<section class="bg-cream py-12 md:py-20 min-h-[600px]">
    <div class="site-container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">

        <!-- Filter Kategori & Bar Pencarian -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-12 pb-6 border-b border-neutral-300/60">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <button type="button" class="px-5 py-2 text-xs font-bold uppercase tracking-wider rounded-full bg-blue-900 text-white shadow-sm transition hover:bg-blue-800">Semua (6)</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold uppercase tracking-wider rounded-full bg-white text-neutral-700 hover:bg-neutral-200 border border-neutral-300 transition">Pedoman BI</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold uppercase tracking-wider rounded-full bg-white text-neutral-700 hover:bg-neutral-200 border border-neutral-300 transition">Majalah</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold uppercase tracking-wider rounded-full bg-white text-neutral-700 hover:bg-neutral-200 border border-neutral-300 transition">Buletin &amp; Materi</button>
            </div>

            <div class="relative w-full md:w-72">
                <input type="text" placeholder="Cari judul buku atau penulis..." class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-neutral-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-800 focus:border-transparent transition">
                <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Grid Katalog Buku 3D -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14">
            <?php foreach ($dummyBooks as $book): ?>
                <div class="group flex flex-col items-center sm:items-start text-center sm:text-left bg-white p-6 rounded-2xl border border-neutral-200/80 shadow-sm hover:shadow-xl transition-all duration-300">

                    <!-- Wrapper Buku dengan Efek 3D Book Spine & Shadow -->
                    <div class="relative w-52 sm:w-56 md:w-60 aspect-[3/4] mx-auto mb-6 rounded-r-lg rounded-l-[3px] shadow-[10px_15px_30px_rgba(0,0,0,0.2)] group-hover:shadow-[15px_25px_40px_rgba(30,58,138,0.3)] transform transition-all duration-500 group-hover:-translate-y-2.5 overflow-hidden flex flex-col justify-between p-5 text-white bg-gradient-to-br <?= $book['cover_bg'] ?>">

                        <!-- Efek Lipatan Punggung Buku 3D (Spine Overlay) -->
                        <div class="absolute top-0 left-0 bottom-0 w-6 bg-gradient-to-r from-black/50 via-white/15 to-transparent z-10 pointer-events-none border-l-2 border-white/20"></div>
                        <div class="absolute top-0 right-0 bottom-0 w-1.5 bg-gradient-to-l from-black/30 to-transparent z-10 pointer-events-none"></div>

                        <?php if (!empty($book['cover_image'])): ?>
                            <!-- Gambar Cover jika tersedia -->
                            <img src="<?= $book['cover_image'] ?>" alt="Cover <?= htmlspecialchars($book['judul']) ?>" class="absolute inset-0 w-full h-full object-cover z-0">
                        <?php else: ?>
                            <!-- Desain Cover Default Eksklusif (Jika Gambar Belum Ada) -->
                            <div class="relative z-0 flex items-center justify-between text-[10px] font-bold tracking-widest text-blue-200/80 uppercase border-b border-white/15 pb-2">
                                <span>GENBI JAMBI</span>
                                <span><?= $book['tahun'] ?></span>
                            </div>

                            <div class="relative z-0 my-auto text-left">
                                <span class="inline-block px-2 py-0.5 mb-2 bg-amber-400 text-neutral-950 text-[10px] font-extrabold tracking-wider uppercase rounded shadow-sm">
                                    <?= $book['kategori'] ?>
                                </span>
                                <h3 class="font-serif text-lg font-bold leading-snug tracking-tight text-white drop-shadow">
                                    <?= htmlspecialchars($book['judul']) ?>
                                </h3>
                                <p class="mt-2 text-[11px] text-neutral-200 line-clamp-3 italic leading-relaxed">
                                    "<?= $book['tagline'] ?>"
                                </p>
                            </div>

                            <div class="relative z-0 pt-2 border-t border-white/15 text-[11px] font-medium text-blue-200/90 text-left flex items-center justify-between">
                                <span class="truncate"><?= $book['penulis'] ?></span>
                                <span class="text-amber-300 text-[10px] font-bold">BI</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan Di Bawah Cover -->
                    <div class="w-full flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-center sm:justify-start gap-2 text-xs font-bold text-blue-800">
                                <span><?= $book['kategori'] ?></span>
                                <span>•</span>
                                <span><?= $book['tahun'] ?></span>
                            </div>

                            <h3 class="mt-2 text-lg font-bold text-neutral-950 group-hover:text-blue-900 transition-colors line-clamp-2">
                                <a href="#" class="hover:underline">
                                    <?= htmlspecialchars($book['judul']) ?>
                                </a>
                            </h3>

                            <p class="mt-1.5 text-xs text-neutral-500 flex items-center justify-center sm:justify-start gap-3">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <?= $book['penulis'] ?>
                                </span>
                                <span>|</span>
                                <span><?= $book['halaman'] ?></span>
                            </p>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between gap-3 w-full">
                            <!-- Tombol Baca Online -->
                            <a href="#" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-blue-900 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                <svg class="w-4 h-4 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Baca Online</span>
                            </a>

                            <!-- Tombol Unduh -->
                            <a href="#" class="inline-flex items-center justify-center p-2 text-neutral-600 bg-neutral-100 hover:bg-neutral-200 hover:text-blue-900 rounded-lg transition" title="Unduh PDF (<?= $book['file_size'] ?>)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>