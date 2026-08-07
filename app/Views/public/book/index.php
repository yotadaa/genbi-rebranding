<?php

$books = $books ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$startItem = ($page - 1) * 12 + 1;
$endItem = min($page * 12, $total);

// Daftar opsi kategori buku (sinkron dengan opsi di Admin)
$filterCategories = ['Fantasy', 'Cerita Anak', 'Komik', 'Fabel', 'Komedi', 'Lainnya'];

// Warna background default acak agar selalu hidup seandainya foto cover sengaja dinonaktifkan
$defaultGradients = [
    'from-blue-900 via-indigo-950 to-blue-800',
    'from-amber-700 via-amber-900 to-yellow-950',
    'from-emerald-800 via-teal-950 to-slate-900',
    'from-slate-900 via-blue-950 to-indigo-900',
    'from-blue-950 via-slate-900 to-neutral-900',
];
?>
<style>
    /* ==================================================
    A. GAYA ANIMASI BUKU MEMBUKA & LEMBARAN KERTAS 3D
       ================================================== */
    .book-3d-perspective {
        perspective: 1200px;
    }

    /* Container utama buku dengan sistem koordinat 3D */
    .book-wrapper-3d {
        position: relative;
        width: 100%;
        height: 100%;
        transform-style: preserve-3d;
        /* Saat kursor menjauh (menutup), gunakan ease-out tanpa overshoot/pantulan mundur ke belakang! */
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Saat kursor mendekat: Seluruh buku miring 3D ke posisi siap dibaca */
    .book-card:hover .book-wrapper-3d {
        transform: rotateY(-24deg) rotateX(7deg) translateY(-8px) scale(1.02);
        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* 1. SAMPUL DEPAN (Front Cover) yang bergerak membuka */
    .book-front-cover {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 3px 12px 12px 3px;
        transform-origin: left center;
        /* Tambahkan translateZ(6px) agar ada jarak 3D lebih aman dari lembaran putih di bawahnya (anti Z-fighting) */
        transform: translateZ(6px);
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        /* Transisi saat kembali (menutup): sangat halus tanpa overshoot ke minus/belakang */
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.5s ease;
        z-index: 30;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    /* Saat Hover: Sampul depan berayun membuka ke sebelah kiri! */
    .book-card:hover .book-front-cover {
        transform: rotateY(-22deg) translateZ(6px);
        box-shadow: 15px 15px 30px -5px rgba(15, 23, 42, 0.5);
        /* Saat membuka (hover), berikan efek ayunan/bounce yang estetis */
        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.6s ease;
    }

    /* 2. LEMBARAN KERTAS 1 (Page Layer Atas) */
    .book-page-1 {
        position: absolute;
        top: 4px;
        bottom: 4px;
        left: 3px;
        right: -4px;
        background: linear-gradient(to right, #e2e8f0, #ffffff 20%, #f8fafc 85%, #cbd5e1);
        border-radius: 2px 8px 8px 2px;
        z-index: 20;
        transform-origin: left center;
        transform: translateZ(2px);
        /* Saat menutup (unhover), delay 0s agar lembaran langsung bersembunyi rapi di bawah sampul! */
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) 0s;
        border-right: 2px solid #94a3b8;
    }

    /* 3. LEMBARAN KERTAS 2 (Page Layer Tengah - Tumpukan bergaris kertas) */
    .book-page-2 {
        position: absolute;
        top: 7px;
        bottom: 7px;
        left: 3px;
        right: -8px;
        border-radius: 2px 8px 8px 2px;
        z-index: 15;
        transform-origin: left center;
        transform: translateZ(1px);
        /* Saat menutup, delay 0s agar tidak tersisa di luar sampul */
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) 0s;
        border-right: 2px solid #cbd5e1;
        /* Tekstur ratusan lembaran kertas asli */
        background: repeating-linear-gradient(90deg,
                #ffffff,
                #ffffff 2px,
                #e2e8f0 2px,
                #e2e8f0 4px);
    }

    /* 4. SAMPUL BELAKANG (Back Cover) & Pondasi Bayangan */
    .book-back-cover {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: -12px;
        border-radius: 3px 10px 10px 3px;
        z-index: 10;
        transform: translateZ(0px);
        box-shadow: 5px 15px 30px rgba(0, 0, 0, 0.25);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Efek MEREKAH: Saat dihover, lembaran-lembaran kertas mengintip keluar satu per satu */
    .book-card:hover .book-page-1 {
        transform: rotateY(-12deg) translateZ(2px);
        right: -9px;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.15);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.05s;
    }

    .book-card:hover .book-page-2 {
        transform: rotateY(-6deg) translateZ(1px);
        right: -18px;
        box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.2);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s;
    }

    .book-card:hover .book-back-cover {
        right: -25px;
        box-shadow: 20px 30px 50px -5px rgba(30, 58, 138, 0.55);
        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Efek Kilatan Cahaya (Sheen) pada Sampul Depan */
    .glossy-sheen {
        background: linear-gradient(105deg,
                transparent 30%,
                rgba(255, 255, 255, 0.45) 45%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 54%);
        transform: translateX(-150%);
        transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .book-card:hover .glossy-sheen {
        transform: translateX(150%);
    }

    /* ==================================================
    B. GAYA PAGINATION ANTI-KONFLIK TERBAIK
    ================================================== */
    #buku-pagination .page-link {
        padding: 0 1.25rem;
        height: 42px;
        min-width: 42px;
        border: 1px solid #93c5fd;
        background-color: #ffffff;
        color: #2563eb;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    #buku-pagination .page-link:hover {
        background-color: #1e3a8a !important;
        color: #ffffff !important;
        border-color: #1e3a8a !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3) !important;
    }

    #buku-pagination .page-active {
        height: 42px;
        min-width: 42px;
        padding: 0 0.5rem;
        border: 2px solid #1e3a8a;
        background-color: #dbeafe;
        color: #0f172a;
        font-weight: 900;
        font-size: 0.9rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.15);
    }

    #buku-pagination .page-disabled {
        padding: 0 1.25rem;
        height: 42px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

<!-- 1. Hero Banner Eksklusif -->
<section class="public-inner-hero py-16 md:py-24 bg-gradient-to-b from-blue-950 to-blue-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(#ffffff11_1px,transparent_1px)] [background-size:16px_16px] opacity-30 pointer-events-none"></div>
    <div class="article-container fade-up text-center md:text-left relative z-10">
        <p class="eyebrow inline-block px-3.5 py-1 bg-blue-800/60 rounded-full text-blue-200 text-xs font-semibold uppercase tracking-widest border border-blue-700/50 backdrop-blur-sm">E-Library &amp; Publikasi Resmi</p>
        <h1 class="page-title mt-5 text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight">Katalog Buku GenBI Jambi.</h1>
        <p class="lead mt-6 max-w-2xl text-blue-100/90 text-sm md:text-lg leading-relaxed">
            Wadah literasi digital, informasi kebanksentralan, majalah inspiratif, dan pedoman beasiswa resmi Bank Indonesia Kantor Perwakilan Provinsi Jambi.
        </p>
    </div>
</section>

<!-- 2. Konten Utama Katalog & Grid Buku -->
<section class="bg-cream py-12 md:py-20 min-h-[700px]">
    <div class="site-container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">

        <!-- Filter Kategori Interaktif & Live Search Bar -->
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6 mb-10 pb-6 border-b border-neutral-300/60">
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 w-full lg:w-auto" id="buku-filter-tabs">
                <button type="button" data-filter="Semua" class="filter-btn is-active px-5 py-2 text-xs font-bold uppercase tracking-wider rounded-full bg-blue-900 text-white shadow-sm transition-all duration-200">
                    Semua (<?= $total ?>)
                </button>
                <?php foreach ($filterCategories as $kategoriOpsi): ?>
                    <button type="button" data-filter="<?= htmlspecialchars($kategoriOpsi) ?>" class="filter-btn px-5 py-2 text-xs font-semibold uppercase tracking-wider rounded-full bg-white text-neutral-700 hover:bg-neutral-200 border border-neutral-300 transition-all duration-200">
                        <?= htmlspecialchars($kategoriOpsi) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Bar Pencarian Live -->
            <div class="relative w-full sm:w-80">
                <input type="text" id="buku-search-input" placeholder="Cari judul buku atau penulis..." class="w-full pl-11 pr-4 py-2.5 text-sm bg-white border border-neutral-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-800 focus:border-transparent transition-shadow shadow-sm">
                <svg class="w-4 h-4 text-neutral-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Informasi Jumlah Data Tayang -->
        <div class="flex items-center justify-between mb-6 text-xs sm:text-sm text-neutral-600 font-medium">
            <span id="buku-counter-text">
                <?php if ($total > 0): ?>
                    Menampilkan <strong class="text-neutral-900"><?= $startItem ?>–<?= $endItem ?></strong> dari <strong class="text-neutral-900"><?= $total ?></strong> buku terdaftar.
                <?php else: ?>
                    Belum ada data buku yang tersedia saat ini.
                <?php endif; ?>
            </span>
            <span class="text-neutral-800 hidden sm:inline-block">Katalog Digital GenBI</span>
        </div>

        <!-- ==========================================
         3. SKELETON LOADER (Animasi Loading UI)
         ========================================== -->
        <div id="buku-skeleton-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
            <?php for ($s = 0; $s < 6; $s++): ?>
                <div class="bg-white p-6 rounded-2xl border border-neutral-200 shadow-sm animate-pulse flex flex-col justify-between">
                    <div class="w-52 sm:w-56 md:w-60 aspect-[3/4] mx-auto mb-6 bg-neutral-200 rounded-r-lg rounded-l-[3px] shadow-inner relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/40 to-transparent animate-[shimmer_2s_infinite]"></div>
                    </div>
                    <div class="space-y-3">
                        <div class="h-3 w-1/3 bg-blue-100 rounded-full"></div>
                        <div class="h-5 w-11/12 bg-neutral-200 rounded"></div>
                        <div class="h-5 w-3/4 bg-neutral-200 rounded"></div>
                        <div class="h-3 w-1/2 bg-neutral-200 rounded mt-2"></div>
                        <div class="pt-4 border-t border-neutral-100 flex items-center gap-3 mt-4">
                            <div class="h-9 flex-1 bg-blue-50 rounded-lg"></div>
                            <div class="h-9 w-10 bg-neutral-100 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- ==========================================
        4. GRID KATALOG BUKU ASLI (DENGAN EFEK MEMBUKA & LEMBARAN KERTAS)
        ========================================== -->
        <div id="buku-real-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14 hidden transition-opacity duration-500 opacity-0">
            <?php if (!empty($books)): ?>
                <?php foreach ($books as $index => $book): ?>
                    <?php $bgGradient = $defaultGradients[$index % count($defaultGradients)]; ?>

                    <div class="book-card group flex flex-col items-center sm:items-start text-center sm:text-left bg-white p-6 rounded-2xl border border-neutral-200/80 hover:border-blue-300 shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1" data-kategori="<?= htmlspecialchars($book['kategori']) ?>" data-judul="<?= strtolower(htmlspecialchars($book['judul'] . ' ' . $book['penulis'])) ?>">

                        <!-- Ruang Perspektif 3D -->
                        <div class="book-3d-perspective w-52 sm:w-56 md:w-60 aspect-[3/4] mx-auto mb-6">

                            <!-- Pembungkus Buku 3D -->
                            <div class="book-wrapper-3d">

                                <!-- A. SAMPUL BELAKANG & LEMBARAN KERTAS DI BAWAH SAMPUL -->
                                <div class="book-back-cover bg-gradient-to-br <?= $bgGradient ?>"></div>
                                <div class="book-page-2"></div>
                                <div class="book-page-1"></div>

                                <!-- B. SAMPUL DEPAN (FRONT COVER YANG MEMBUKA) -->
                                <div class="book-front-cover overflow-hidden flex flex-col justify-between p-5 text-white bg-gradient-to-br <?= $bgGradient ?>">

                                    <!-- Kilauan Cahaya (Sheen) -->
                                    <div class="glossy-sheen absolute inset-0 z-20 pointer-events-none"></div>

                                    <!-- Efek Punggung Buku & Jilidan Kiri -->
                                    <div class="absolute top-0 left-0 bottom-0 w-6 bg-gradient-to-r from-black/60 via-white/15 to-transparent z-10 pointer-events-none border-l-2 border-white/30"></div>
                                    <div class="absolute top-0 right-0 bottom-0 w-2 bg-gradient-to-l from-black/40 to-transparent z-10 pointer-events-none"></div>
                                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-b from-white/20 to-transparent z-10 pointer-events-none"></div>

                                    <?php if (!empty($book['cover'])): ?>
                                        <!-- Cover Gambar dari Database / Unsplash -->
                                        <img src="<?= htmlspecialchars($book['cover']) ?>" alt="Cover <?= htmlspecialchars($book['judul']) ?>" style="-webkit-backface-visibility: hidden; backface-visibility: hidden;" class="absolute inset-0 w-full h-full object-cover z-0 transition-transform duration-700 group-hover:scale-105" loading="lazy">
                                    <?php else: ?>
                                        <!-- Desain Cover CSS Default Eksklusif -->
                                        <div class="relative z-0 flex items-center justify-between text-[10px] font-bold tracking-widest text-blue-200/80 uppercase border-b border-white/15 pb-2">
                                            <span>GENBI JAMBI</span>
                                            <span><?= htmlspecialchars((string)$book['tahun']) ?></span>
                                        </div>
                                        <div class="relative z-0 my-auto text-left">
                                            <span class="inline-block px-2 py-0.5 mb-2 bg-amber-400 text-neutral-950 text-[10px] font-extrabold tracking-wider uppercase rounded shadow-sm">
                                                <?= htmlspecialchars($book['kategori']) ?>
                                            </span>
                                            <h3 class="font-serif text-lg font-bold leading-snug tracking-tight text-white drop-shadow">
                                                <?= htmlspecialchars($book['judul']) ?>
                                            </h3>
                                            <p class="mt-2 text-[11px] text-neutral-200 line-clamp-3 italic leading-relaxed">
                                                "<?= htmlspecialchars($book['sinopsis']) ?>"
                                            </p>
                                        </div>
                                        <div class="relative z-0 pt-2 border-t border-white/15 text-[11px] font-medium text-blue-200/90 text-left flex items-center justify-between">
                                            <span class="truncate"><?= htmlspecialchars($book['penulis']) ?></span>
                                            <span class="text-amber-300 text-[10px] font-bold">BI</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>

                        <!-- Detail Keterangan Buku -->
                        <div class="w-full flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-center sm:justify-start gap-2 text-xs font-bold text-blue-800">
                                    <span class="bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100"><?= htmlspecialchars($book['kategori']) ?></span>
                                    <span class="text-neutral-300">•</span>
                                    <span class="text-neutral-500 font-semibold"><?= htmlspecialchars((string)$book['tahun']) ?></span>
                                </div>

                                <h3 class="mt-2.5 text-base sm:text-lg font-bold text-neutral-950 group-hover:text-blue-900 transition-colors line-clamp-2 leading-snug">
                                    <a href="#" class="hover:underline">
                                        <?= htmlspecialchars($book['judul']) ?>
                                    </a>
                                </h3>

                                <p class="mt-2 text-xs text-neutral-500 flex flex-wrap items-center justify-center sm:justify-start gap-y-1 gap-x-2.5 font-medium">
                                    <span class="inline-flex items-center gap-1 text-neutral-600 truncate max-w-[180px]">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <?= htmlspecialchars($book['penulis']) ?>
                                    </span>
                                    <span class="text-neutral-300">|</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <?= htmlspecialchars($book['halaman']) ?>
                                    </span>
                                </p>
                            </div>

                            <!-- Tombol Aksi (Baca & Unduh) -->
                            <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between gap-2.5 w-full">
                                <button type="button" class="btn-baca-online flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-blue-900 bg-blue-50 hover:bg-blue-900 hover:text-white rounded-xl transition-all duration-200 shadow-sm cursor-pointer"
                                    data-file="<?= htmlspecialchars((string) ($book['file_path'] ?? '')) ?>"
                                    data-flipbook="<?= htmlspecialchars((string) ($book['path_flipbook'] ?? '')) ?>"
                                    data-judul="<?= htmlspecialchars((string) ($book['judul'] ?? 'Buku Tanpa Judul')) ?>"
                                    data-penulis="<?= htmlspecialchars((string) ($book['penulis'] ?? 'GenBI Jambi')) ?>"
                                    data-download="<?= htmlspecialchars((string) ($book['file_path'] ?? '')) ?>">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Baca Online</span>
                                </button>

                                <?php if (!empty($book['file_path'])): ?>
                                    <a href="<?= htmlspecialchars((string) $book['file_path']) ?>" download target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-neutral-700 bg-neutral-100 hover:bg-emerald-600 hover:text-white rounded-xl transition-all duration-200 shadow-sm gap-1.5" title="Unduh File Buku">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>Unduh</span>
                                    </a>
                                <?php else: ?>
                                    <button type="button" onclick="alert('File PDF belum tersedia untuk diunduh. Silakan hubungi pengelola GenBI.'); return false;" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-neutral-400 bg-neutral-100 hover:bg-neutral-200 rounded-xl transition-all duration-200 shadow-sm gap-1.5 cursor-not-allowed" title="Unduhan belum tersedia">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>Unduh</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-16 text-center text-neutral-500 bg-white rounded-2xl border border-neutral-200 p-8">
                    <svg class="w-12 h-12 text-neutral-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-base font-semibold text-neutral-700">Belum ada dokumen yang dipublikasikan</p>
                    <p class="text-xs text-neutral-400 mt-1">Silakan kembali lagi nanti untuk mendapatkan publikasi terbaru GenBI Jambi.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pesan jika hasil pencarian tidak ada -->
        <div id="buku-empty-search" class="hidden py-16 text-center bg-white rounded-2xl border border-neutral-200 p-8 my-8">
            <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-base font-bold text-neutral-800">Buku Tidak Ditemukan</p>
            <p class="text-xs text-neutral-500 mt-1" id="empty-search-msg">Tidak ada judul buku atau penulis yang cocok dengan pencarian Anda.</p>
        </div>

        <!-- 5. Navigasi Halaman (Pagination) -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-14 flex flex-wrap items-center justify-center gap-2" id="buku-pagination" aria-label="Pagination katalog buku">
                <!-- Tombol Sebelum -->
                <?php if ($page > 1): ?>
                    <a class="page-link" href="/buku?<?= htmlspecialchars(\App\Core\Paginator::buildQuery($page - 1)) ?>">
                        ← Sebelumnya
                    </a>
                <?php else: ?>
                    <span class="page-disabled">
                        ← Sebelumnya
                    </span>
                <?php endif; ?>
                <!-- Deretan Angka Halaman -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="page-active" aria-current="page">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a class="page-link" href="/buku?<?= htmlspecialchars(\App\Core\Paginator::buildQuery($i)) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                <!-- Tombol Berikutnya -->
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="/buku?<?= htmlspecialchars(\App\Core\Paginator::buildQuery($page + 1)) ?>">
                        Berikutnya →
                    </a>
                <?php else: ?>
                    <span class="page-disabled">
                        Berikutnya →
                    </span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    </div>
</section>

<!-- ==========================================
6. MODAL FLIPBOOK MAGAZINE READER (E-READER)
========================================== -->
<div id="flipbook-reader-modal" class="fixed inset-0 z-[99999] bg-slate-900/95 backdrop-blur-md hidden flex flex-col justify-between overflow-hidden opacity-0 transition-opacity duration-300">

    <!-- A. TOP BAR: Judul & Tombol Tutup -->
    <header class="h-16 bg-slate-950/80 px-4 sm:px-6 flex items-center justify-between border-b border-slate-800 shrink-0 z-20">
        <div class="flex items-center gap-3 overflow-hidden pr-4">
            <span class="p-2 bg-blue-600 text-white rounded-lg shadow-md shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </span>
            <div class="truncate">
                <h3 id="reader-title-text" class="text-sm sm:text-base font-bold text-white truncate">Memuat Buku...</h3>
                <p id="reader-author-text" class="text-xs text-slate-400 truncate">GenBI Jambi</p>
            </div>
        </div>
        <button type="button" id="btn-close-reader" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-rose-600 text-slate-200 hover:text-white font-semibold text-xs transition-all duration-200 flex items-center gap-2 shadow shrink-0 cursor-pointer">
            <span>Tutup Viewer</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </header>

    <!-- B. STAGE VIEWER: Tempat Lembaran Buku Diberdayakan (Canvas) -->
    <div id="reader-stage" class="flex-1 relative flex items-center justify-center overflow-auto p-4 sm:p-8 select-none">

        <!-- Loading Indicator -->
        <div id="reader-loading" class="flex flex-col items-center justify-center text-center p-6 bg-slate-900/90 rounded-2xl border border-slate-700 shadow-2xl z-30">
            <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-blue-500 mb-3 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-semibold text-white">Memuat Dokumen PDF...</p>
            <p class="text-xs text-slate-400 mt-1">Harap tunggu sejenak sementara lembaran buku disiapkan.</p>
        </div>

        <!-- Tombol Navigasi Kiri (Prev) -->
        <button type="button" id="btn-page-prev" class="fixed left-3 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-black/60 hover:bg-blue-600 text-white flex items-center justify-center shadow-xl transition-all border border-white/15 backdrop-blur-sm disabled:opacity-30 disabled:pointer-events-none cursor-pointer">
            <svg class="w-6 h-6 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Tombol Navigasi Kanan (Next) -->
        <button type="button" id="btn-page-next" class="fixed right-3 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-black/60 hover:bg-blue-600 text-white flex items-center justify-center shadow-xl transition-all border border-white/15 backdrop-blur-sm disabled:opacity-30 disabled:pointer-events-none cursor-pointer">
            <svg class="w-6 h-6 -mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Kontainer Lembaran Buku (Spread) -->
        <div id="book-spread" class="relative flex items-center justify-center shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] rounded-lg transition-transform duration-200 origin-center my-auto max-w-full">

            <!-- Halaman Kiri (Pada desktop/mode 2 halaman) -->
            <div id="page-wrapper-left" class="relative bg-white rounded-l-md overflow-hidden shadow-sm">
                <canvas id="canvas-left" class="block w-full h-auto max-w-[45vw] sm:max-w-[42vw] max-h-[78vh] object-contain"></canvas>
            </div>

            <!-- Punggung Buku Tengah / Book Spine Shadow (Efek Buku Terbuka Nyata) -->
            <div id="book-spine" class="w-[12px] self-stretch bg-gradient-to-r from-black/25 via-white/40 to-black/25 relative z-10 -mx-1 hidden md:block border-l border-r border-black/10 shadow-inner"></div>

            <!-- Halaman Kanan (Halaman ke-2 saat di desktop) -->
            <div id="page-wrapper-right" class="relative bg-white rounded-r-md overflow-hidden shadow-sm hidden md:block">
                <canvas id="canvas-right" class="block w-full h-auto max-w-[45vw] sm:max-w-[42vw] max-h-[78vh] object-contain"></canvas>
            </div>

        </div>

    </div>

    <!-- C. BOTTOM FLOATING TOOLBAR: Pill Bar ala Unja Digital Magazine -->
    <footer class="h-20 shrink-0 flex items-center justify-center px-4 z-20 pointer-events-none pb-4">
        <div class="pointer-events-auto bg-white/95 backdrop-blur-md text-neutral-800 px-5 py-2 rounded-full shadow-[0_10px_35px_rgba(0,0,0,0.5)] border border-white/40 flex items-center gap-2 sm:gap-4 transition-all">

            <!-- Indikator & Input Halaman -->
            <div class="flex items-center gap-1 sm:gap-1.5 text-xs sm:text-sm font-bold px-2 py-1 bg-slate-100 rounded-full border border-slate-200">
                <span class="text-neutral-500 text-[11px] hidden sm:inline">Hal.</span>
                <input type="number" id="input-page-jump" value="1" min="1" class="w-12 text-center font-bold bg-white text-blue-900 rounded py-0.5 px-1 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-xs sm:text-sm">
                <span class="text-neutral-400">/</span>
                <span id="text-total-pages" class="text-neutral-700">0</span>
            </div>

            <div class="h-5 w-px bg-neutral-300"></div>

            <!-- Zoom Out -->
            <button type="button" id="btn-zoom-out" title="Perkecil (-)" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center text-neutral-700 font-bold transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                </svg>
            </button>

            <!-- Reset Zoom -->
            <button type="button" id="btn-zoom-reset" title="Ukuran Pas" class="px-2.5 py-1 text-[11px] font-bold bg-blue-50 text-blue-800 rounded-full hover:bg-blue-100 transition-colors hidden sm:block cursor-pointer">
                Fit
            </button>

            <!-- Zoom In -->
            <button type="button" id="btn-zoom-in" title="Perbesar (+)" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center text-neutral-700 font-bold transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
            </button>

            <div class="h-5 w-px bg-neutral-300"></div>

            <!-- Fullscreen Toggle -->
            <button type="button" id="btn-fullscreen" title="Layar Penuh (Fullscreen)" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center text-neutral-700 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>

            <!-- Unduh File Langsung dari Reader -->
            <a id="btn-reader-download" href="#" target="_blank" download title="Unduh Dokumen PDF" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-full flex items-center gap-1.5 shadow transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden md:inline">Unduh</span>
            </a>

        </div>
    </footer>

</div>

<!-- Load Library Mozilla PDF.js dari CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<!-- ==========================================
     7. SCRIPT INTERAKTIF (SKELETON, FILTER & FLIPBOOK)
     ========================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skeletonGrid = document.getElementById('buku-skeleton-grid');
        const realGrid = document.getElementById('buku-real-grid');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const bookCards = document.querySelectorAll('.book-card');
        const searchInput = document.getElementById('buku-search-input');
        const emptySearch = document.getElementById('buku-empty-search');
        const counterText = document.getElementById('buku-counter-text');
        const emptyMsg = document.getElementById('empty-search-msg');

        let currentFilter = 'Semua';
        let currentSearch = '';

        // 1. EFEK SKELETON AWAL
        setTimeout(() => {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (realGrid) {
                realGrid.classList.remove('hidden');
                setTimeout(() => {
                    realGrid.classList.remove('opacity-0');
                    realGrid.classList.add('opacity-100');
                }, 30);
            }
        }, 600);

        // 2. FUNGSI FILTER & SEARCH REALTIME
        function applyFilters() {
            let visibleCount = 0;

            bookCards.forEach(card => {
                const kategori = card.getAttribute('data-kategori');
                const judul = card.getAttribute('data-judul');

                const matchCategory = (currentFilter === 'Semua') || (kategori === currentFilter);
                const matchSearch = (currentSearch === '') || (judul && judul.includes(currentSearch));

                if (matchCategory && matchSearch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && bookCards.length > 0) {
                if (emptySearch) emptySearch.classList.remove('hidden');
                if (emptyMsg) {
                    if (currentSearch !== '') {
                        emptyMsg.textContent = `Tidak ada judul buku atau penulis dengan kata kunci "${searchInput ? searchInput.value : ''}" pada kategori "${currentFilter}".`;
                    } else {
                        emptyMsg.textContent = `Belum ada dokumen untuk kategori "${currentFilter}".`;
                    }
                }
            } else {
                if (emptySearch) emptySearch.classList.add('hidden');
            }

            if (counterText) {
                counterText.innerHTML = `Menampilkan <strong class="text-neutral-900">${visibleCount}</strong> buku untuk kategori <strong class="text-blue-900">${currentFilter}</strong>.`;
            }
        }

        // 3. EVENT LISTENER TOMBOL KATEGORI
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => {
                    b.className = 'filter-btn px-5 py-2 text-xs font-semibold uppercase tracking-wider rounded-full bg-white text-neutral-700 hover:bg-neutral-200 border border-neutral-300 transition-all duration-200';
                });
                this.className = 'filter-btn is-active px-5 py-2 text-xs font-bold uppercase tracking-wider rounded-full bg-blue-900 text-white shadow-sm transition-all duration-200';

                currentFilter = this.getAttribute('data-filter');

                if (realGrid) realGrid.style.opacity = '0';
                if (skeletonGrid) skeletonGrid.style.display = 'grid';
                if (emptySearch) emptySearch.classList.add('hidden');

                setTimeout(() => {
                    applyFilters();
                    if (skeletonGrid) skeletonGrid.style.display = 'none';
                    if (realGrid) realGrid.style.opacity = '1';
                }, 350);
            });
        });

        // 4. EVENT LISTENER PENCARIAN
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                currentSearch = e.target.value.toLowerCase().trim();
                applyFilters();
            });
        }

        // ==========================================================
        // 5. SISTEM FLIPBOOK E-READER INTERAKTIF (PDF.JS ENGINE)
        // ==========================================================
        const readerModal = document.getElementById('flipbook-reader-modal');
        const btnCloseReader = document.getElementById('btn-close-reader');
        const readerTitle = document.getElementById('reader-title-text');
        const readerAuthor = document.getElementById('reader-author-text');
        const readerLoading = document.getElementById('reader-loading');
        const bookSpread = document.getElementById('book-spread');
        const bookSpine = document.getElementById('book-spine');
        const canvasLeft = document.getElementById('canvas-left');
        const canvasRight = document.getElementById('canvas-right');
        const pageWrapperRight = document.getElementById('page-wrapper-right');
        const btnPrev = document.getElementById('btn-page-prev');
        const btnNext = document.getElementById('btn-page-next');
        const inputPageJump = document.getElementById('input-page-jump');
        const textTotalPages = document.getElementById('text-total-pages');
        const btnZoomIn = document.getElementById('btn-zoom-in');
        const btnZoomOut = document.getElementById('btn-zoom-out');
        const btnZoomReset = document.getElementById('btn-zoom-reset');
        const btnFullscreen = document.getElementById('btn-fullscreen');
        const btnReaderDownload = document.getElementById('btn-reader-download');

        let pdfDoc = null;
        let currentPage = 1;
        let totalPages = 0;
        let zoomScale = 1.0;
        let isRendering = false;

        // Inisialisasi PDF.js Worker dari CDN
        const pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;
        if (pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        }

        // Event Tombol Baca Online -> Langsung mengarahkan ke Link Flipbook tanpa membaca file lokal server
        const btnReadOnlineList = document.querySelectorAll('.btn-baca-online');
        btnReadOnlineList.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const flipbookUrl = this.getAttribute('data-flipbook');
                const filePath = this.getAttribute('data-file');

                // Prioritas utama ke link flipbook, jika kosong gunakan tautan file_path jika ada
                const targetUrl = (flipbookUrl && flipbookUrl !== '' && flipbookUrl !== '#') ? flipbookUrl : ((filePath && filePath !== '' && filePath !== '#') ? filePath : null);

                if (!targetUrl) {
                    alert('Mohon maaf, tautan baca online (flipbook) untuk publikasi ini belum tersedia. Silakan hubungi admin GenBI Jambi.');
                    return;
                }

                // Langsung arahkan ke link flipbook di tab baru
                window.open(targetUrl, '_blank');
            });
        });

        function closeReaderModal() {
            if (!readerModal) return;
            readerModal.classList.remove('opacity-100');
            readerModal.classList.add('opacity-0');
            setTimeout(() => {
                readerModal.classList.add('hidden');
                document.body.style.overflow = '';
                pdfDoc = null;
                if (bookSpread) bookSpread.style.transform = 'scale(1)';
                zoomScale = 1.0;
            }, 300);
        }

        if (btnCloseReader) {
            btnCloseReader.addEventListener('click', closeReaderModal);
        }

        function loadPdfDocument(url) {
            const pdfjs = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;
            if (!pdfjs) {
                alert('Library PDF Reader (PDF.js) sedang dimuat atau gangguan koneksi. Silakan coba sesaat lagi.');
                return;
            }

            if (readerLoading) readerLoading.style.display = 'flex';
            if (bookSpread) bookSpread.style.opacity = '0';

            const loadingTask = pdfjs.getDocument(url);
            loadingTask.promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                totalPages = pdfDoc.numPages;
                if (textTotalPages) textTotalPages.textContent = totalPages;
                if (inputPageJump) inputPageJump.max = totalPages;
                currentPage = 1;

                renderCurrentSpread();
            }).catch(function(error) {
                console.error('Error saat membaca PDF:', error);
                if (readerLoading) readerLoading.style.display = 'none';
                alert('Gagal membuka dokumen PDF: ' + (error.message || 'File tidak teridentifikasi di server.'));
                closeReaderModal();
            });
        }

        function isDualPageMode() {
            return window.innerWidth >= 768 && totalPages > 1;
        }

        async function renderCurrentSpread() {
            if (!pdfDoc || isRendering) return;
            isRendering = true;

            if (readerLoading) readerLoading.style.display = 'flex';
            if (bookSpread) {
                bookSpread.style.opacity = '0.4';
                bookSpread.style.transform = `scale(${zoomScale})`;
            }

            const dual = isDualPageMode();
            const pageNumLeft = currentPage;
            const pageNumRight = currentPage + 1;

            if (inputPageJump) inputPageJump.value = pageNumLeft;
            updateNavButtons(dual);

            try {
                await renderPageToCanvas(pageNumLeft, canvasLeft);

                if (dual && pageNumRight <= totalPages) {
                    if (pageWrapperRight) pageWrapperRight.style.display = 'block';
                    if (bookSpine) bookSpine.style.display = 'block';
                    await renderPageToCanvas(pageNumRight, canvasRight);
                } else {
                    if (pageWrapperRight) pageWrapperRight.style.display = 'none';
                    if (bookSpine) bookSpine.style.display = 'none';
                }
            } catch (err) {
                console.error('Error render halaman canvas:', err);
            } finally {
                isRendering = false;
                if (readerLoading) readerLoading.style.display = 'none';
                if (bookSpread) bookSpread.style.opacity = '1';
            }
        }

        function renderPageToCanvas(num, canvasObj) {
            return new Promise((resolve, reject) => {
                if (!canvasObj || !pdfDoc) return reject('Canvas atau Dokumen tidak valid.');
                pdfDoc.getPage(num).then(function(page) {
                    const dpr = window.devicePixelRatio || 1;
                    const renderScale = Math.max(1.5, dpr);
                    const viewport = page.getViewport({
                        scale: renderScale
                    });
                    const context = canvasObj.getContext('2d');

                    canvasObj.height = viewport.height;
                    canvasObj.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(resolve).catch(reject);
                }).catch(reject);
            });
        }

        function updateNavButtons(dual) {
            if (btnPrev) btnPrev.disabled = (currentPage <= 1);
            if (btnNext) btnNext.disabled = (currentPage + (dual ? 1 : 0) >= totalPages);
        }

        if (btnNext) {
            btnNext.addEventListener('click', function() {
                const step = isDualPageMode() ? 2 : 1;
                if (currentPage + (isDualPageMode() ? 1 : 0) < totalPages) {
                    currentPage += step;
                    if (currentPage > totalPages) currentPage = totalPages;
                    renderCurrentSpread();
                }
            });
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', function() {
                const step = isDualPageMode() ? 2 : 1;
                if (currentPage > 1) {
                    currentPage -= step;
                    if (currentPage < 1) currentPage = 1;
                    renderCurrentSpread();
                }
            });
        }

        if (inputPageJump) {
            inputPageJump.addEventListener('change', function() {
                let val = parseInt(this.value, 10);
                if (isNaN(val) || val < 1) val = 1;
                if (val > totalPages) val = totalPages;

                if (isDualPageMode() && val > 1 && val % 2 === 0) {
                    val -= 1;
                }
                currentPage = val;
                this.value = currentPage;
                renderCurrentSpread();
            });
        }

        function applyZoom() {
            if (!bookSpread) return;
            bookSpread.style.transform = `scale(${zoomScale})`;
            bookSpread.style.cursor = (zoomScale > 1.0) ? 'grab' : 'default';
        }

        if (btnZoomIn) {
            btnZoomIn.addEventListener('click', function() {
                if (zoomScale < 2.5) {
                    zoomScale = parseFloat((zoomScale + 0.25).toFixed(2));
                    applyZoom();
                }
            });
        }

        if (btnZoomOut) {
            btnZoomOut.addEventListener('click', function() {
                if (zoomScale > 0.5) {
                    zoomScale = parseFloat((zoomScale - 0.25).toFixed(2));
                    applyZoom();
                }
            });
        }

        if (btnZoomReset) {
            btnZoomReset.addEventListener('click', function() {
                zoomScale = 1.0;
                applyZoom();
            });
        }

        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', function() {
                const elem = document.getElementById('flipbook-reader-modal');
                if (!elem) return;
                if (!document.fullscreenElement) {
                    if (elem.requestFullscreen) elem.requestFullscreen();
                    else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                    else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
                } else {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    else if (document.msExitFullscreen) document.msExitFullscreen();
                }
            });
        }

        document.addEventListener('keydown', function(evt) {
            if (!readerModal || readerModal.classList.contains('hidden')) return;
            if (evt.key === 'ArrowRight' && btnNext && !btnNext.disabled) {
                btnNext.click();
            } else if (evt.key === 'ArrowLeft' && btnPrev && !btnPrev.disabled) {
                btnPrev.click();
            } else if (evt.key === 'Escape') {
                closeReaderModal();
            }
        });

        let resizeTimer = null;
        window.addEventListener('resize', function() {
            if (!readerModal || readerModal.classList.contains('hidden') || !pdfDoc) return;
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                renderCurrentSpread();
            }, 400);
        });
    });
</script>