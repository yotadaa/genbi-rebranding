<?php

/** @var callable $e */
$item = $item ?? null;
$isEdit = !empty($item && ($item['id'] ?? 0) > 0);
?>
<!-- Menggunakan max-w-7xl agar sejajar/konsisten dengan margin di Halaman Daftar Buku -->
<section class="mx-auto max-w-7xl">
    <!-- Header Navigation & Title -->
    <header class="cms-header slide-in flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <a href="/admin/buku" class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-500 hover:text-blue-600 mb-3 transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Katalog Buku</span>
            </a>
            <h1 class="section-title text-2xl md:text-3xl font-extrabold text-neutral-900 tracking-tight">
                <?= $isEdit ? 'Edit Data & Karya Buku' : 'Tambah Buku Baru' ?>
            </h1>
            <p class="mt-1 text-sm text-neutral-600">
                <?= $isEdit ? 'Perbaiki informasi karya, ganti foto sampul, atau perbaharui berkas dokumen E-Book GenBI.' : 'Lengkapi metadata karya publikasi, sampul buku, dan berkas digital E-Book/PDF GenBI.' ?>
            </p>
        </div>
        <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50/80 border border-blue-100 text-blue-900 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full <?= $isEdit ? 'bg-amber-500' : 'bg-blue-600' ?>"></span>
            <span><?= $isEdit ? 'Mode Pembaharuan Data (Update)' : 'Modul Literasi Digital GenBI' ?></span>
        </div>
    </header>

    <!-- BANNER INFORMASI KHUSUS MODE UPDATE / EDIT -->
    <?php if ($isEdit): ?>
        <div class="mt-6 p-5 bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 rounded-2xl shadow-md text-white flex flex-col md:flex-row md:items-center justify-between gap-4 border border-blue-700/50">
            <div class="flex items-start md:items-center gap-4">
                <div class="p-3 bg-white/10 backdrop-blur-md rounded-xl text-blue-200 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md bg-amber-400 text-neutral-900 font-extrabold text-[11px] uppercase tracking-wider shadow-xs">Mode Update</span>
                        <span class="text-xs font-bold text-blue-200">ID Buku: #<?= (int) ($item['id'] ?? 0) ?></span>
                    </div>
                    <h2 class="text-base md:text-lg font-extrabold tracking-tight text-white mt-1">Mengedit Karya: <?= $e($item['judul'] ?? '') ?></h2>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs font-medium text-blue-100/90">
                        <span><strong class="text-blue-300 font-semibold">Dilihat Pengunjung:</strong> <?= number_format((int) ($item['view_count'] ?? 0)) ?>x</span>
                        <span class="text-blue-300/60">•</span>
                        <span><strong class="text-blue-300 font-semibold">Ukuran Dokumen:</strong> <?= $e($item['file_size_formatted'] ?? '0 KB') ?></span>
                        <span class="text-blue-300/60">•</span>
                        <span>
                            <strong class="text-blue-300 font-semibold">Status:</strong>
                            <span class="font-bold <?= ($item['status'] ?? '') === 'published' ? 'text-emerald-300' : 'text-amber-300' ?>"><?= strtoupper($e($item['status'] ?? 'DRAFT')) ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <?php if (!empty($item['slug'])): ?>
                <div class="flex-shrink-0">
                    <a href="/buku/<?= $e($item['slug']) ?>" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white text-blue-900 hover:bg-blue-50 font-extrabold text-xs rounded-xl shadow transition-all transform active:scale-95">
                        <span>Pratinjau Web Publik</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Form Utama -->
    <form id="buku-form" novalidate class="admin-card mt-6 p-6 md:p-10 shadow-sm border border-neutral-200/80 rounded-2xl bg-white space-y-8" onsubmit="submitBukuForm(event)">

        <!-- BLOK 1: INFORMASI KARYA & KLASIFIKASI -->
        <div>
            <div class="flex items-center justify-between pb-3 mb-6 border-b border-neutral-200 text-neutral-900">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-900 text-white text-xs font-bold">1</span>
                    <h3 class="text-base font-extrabold tracking-wide uppercase">Informasi Karya & Klasifikasi</h3>
                </div>
                <?php if ($isEdit): ?>
                    <span class="text-xs font-semibold text-blue-600">Menampilkan data asli dari database</span>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Judul Buku -->
                <div class="lg:col-span-6">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Judul Buku / Karya <span class="text-red-500">*</span></label>
                    <div>
                        <input type="text" id="judul-input" name="judul" value="<?= $e($item['judul'] ?? '') ?>" placeholder="Contoh: Majalah GenBI Jambi Edisi 2026" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 placeholder:text-neutral-400 transition-all" required />
                    </div>
                </div>

                <!-- Slug / Tautan Web -->
                <div class="lg:col-span-6">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-bold text-neutral-800 tracking-wide">Slug / Tautan Web <span class="text-neutral-400 text-xs font-normal">(Auto / Kustom)</span></label>
                        <?php if ($isEdit && !empty($item['slug'])): ?>
                            <span class="text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded border border-emerald-200">Tersedia di Database</span>
                        <?php endif; ?>
                    </div>
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400 font-mono text-xs sm:text-sm select-none">
                            /buku/
                        </div>
                        <input type="text" id="slug-input" name="slug" value="<?= $e($item['slug'] ?? '') ?>" placeholder="majalah-genbi-jambi-edisi-2026" class="form-input w-full pl-16 pr-4 py-2.5 rounded-xl border border-neutral-300 bg-neutral-50 text-sm font-mono text-blue-900 shadow-sm focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-100 placeholder:text-neutral-400 transition-all" />
                    </div>
                    <p class="mt-1.5 text-[11px] text-neutral-500 italic">Tautan unik web publik. <?= $isEdit ? 'Jika diubah, pastikan tidak mengandung spasi atau simbol.' : 'Jika dibiarkan kosong, sistem akan membuat dari judul secara otomatis.' ?></p>
                </div>

                <!-- Kategori Buku -->
                <div class="lg:col-span-6">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Kategori Buku</label>
                    <select name="kategori" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-semibold text-blue-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all">
                        <?php
                        $opts = ['Fantasy', 'Cerita Anak', 'Komik', 'Fabel', 'Komedi', 'Lainnya'];
                        $curr = $item['kategori'] ?? 'Fantasy';
                        foreach ($opts as $o):
                        ?>
                            <option value="<?= $e($o) ?>" <?= $curr === $o ? 'selected' : '' ?>><?= $e($o) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Publikasi -->
                <div class="lg:col-span-6">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Status Tayangan</label>
                    <select name="status" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-bold text-neutral-800 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Tayangkan di Web Publik)</option>
                        <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Disembunyikan dari Pengunjung)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BLOK 2: DETAIL PENERBITAN & METADATA -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-900 text-white text-xs font-bold">2</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Detail Penerbitan & Spesifikasi Buku</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Penulis -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Penulis / Tim Penyusun <span class="text-red-500">*</span></label>
                    <div>
                        <input type="text" id="penulis-input" name="penulis" value="<?= $e($item['penulis'] ?? 'GenBI Jambi') ?>" placeholder="Nama penulis atau tim" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" required />
                    </div>
                </div>

                <!-- Penerbit -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Penerbit / Instansi <span class="text-red-500">*</span></label>
                    <div>
                        <input type="text" id="penerbit-input" name="penerbit" value="<?= $e($item['penerbit'] ?? 'Bank Indonesia') ?>" placeholder="Nama instansi/penerbit" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" required />
                    </div>
                </div>

                <!-- Tahun Terbit -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Tahun Terbit <span class="text-red-500">*</span></label>
                    <div>
                        <input type="number" id="tahun-input" name="tahun" value="<?= $e($item['tahun'] ?? date('Y')) ?>" placeholder="2026" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-bold text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" required />
                    </div>
                </div>

                <!-- Jumlah Halaman -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Jumlah Halaman <span class="text-red-500">*</span></label>
                    <?php
                    $hlm = !empty($item['page_count']) ? (int) $item['page_count'] : ((int) preg_replace('/\D/', '', (string) ($item['halaman'] ?? '0')));
                    ?>
                    <div>
                        <input type="number" id="halaman-input" name="halaman" value="<?= $hlm > 0 ? $hlm : '' ?>" placeholder="Cth: 120" min="1" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" required />
                    </div>
                </div>

                <!-- Nomor ISBN -->
                <div class="sm:col-span-2 lg:col-span-5">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Nomor ISBN / Kode Identitas Karya (Opsional)</label>
                    <div>
                        <input type="text" name="isbn" value="<?= $e($item['isbn'] ?? '-') ?>" placeholder="Contoh: 978-602-8519-93-9 atau biarkan berisi -" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-mono text-neutral-700 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all max-w-md" />
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOK 3: SAMPUL BUKU (COVER) & BERKAS E-BOOK (PDF / LINK) -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-900 text-white text-xs font-bold">3</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Cover & Berkas Digital (PDF)</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <!-- 1. Upload Cover Buku -->
                <div class="p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl shadow-sm hover:shadow transition-shadow flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3 mb-4">
                            <label class="block text-sm font-extrabold text-neutral-900 tracking-wide">Foto Cover Buku <span class="text-red-500">*</span></label>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= !empty($item['cover']) ? 'bg-emerald-100 text-emerald-900' : 'bg-blue-100 text-blue-900' ?>"><?= !empty($item['cover']) ? 'Tersedia di Server' : 'Rasio 3:4 (Maks 5MB)' ?></span>
                        </div>

                        <!-- Layout Cover & Info dengan Ukuran Statis -->
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                            <!-- Kotak Preview dengan dimensi statis yang pasti -->
                            <div style="width: 160px; min-width: 160px; height: 220px;" class="bg-white border-2 border-dashed border-neutral-300 rounded-xl overflow-hidden shadow-inner flex flex-col items-center justify-center p-2.5 relative flex-shrink-0">
                                <!-- Placeholder jika foto belum ada -->
                                <div id="cover-placeholder" class="flex flex-col items-center justify-center text-center p-2 <?= !empty($item['cover']) ? 'hidden' : '' ?>">
                                    <svg class="w-8 h-8 mb-2 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-[11px] font-bold text-neutral-400">Belum Ada Cover</span>
                                </div>
                                <!-- Preview Gambar -->
                                <img id="cover-preview" src="<?= $e($item['cover'] ?? '') ?>" alt="Cover Preview" class="w-full h-full object-contain rounded-lg drop-shadow-sm <?= empty($item['cover']) ? 'hidden' : '' ?>" />
                            </div>

                            <!-- Area Tombol & Instruksi -->
                            <div class="flex-1 w-full space-y-3.5 text-center sm:text-left">
                                <input type="hidden" name="cover" id="cover-url-input" value="<?= $e($item['cover'] ?? '') ?>" />

                                <div>
                                    <label class="inline-flex items-center justify-center gap-2 cursor-pointer px-4 py-2.5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded-xl shadow-md transition-all transform active:scale-95 w-full sm:w-auto">
                                        <span><?= !empty($item['cover']) ? 'Ganti Foto Cover' : 'Pilih Foto Cover (Wajib)' ?></span>
                                        <input type="file" id="cover-file-input" accept="image/*" onchange="handleCoverSelect(this)" class="hidden" />
                                    </label>
                                </div>

                                <!-- Menampilkan Read Data Alamat Cover Saat Ini (Jika Edit) -->
                                <?php if ($isEdit && !empty($item['cover'])): ?>
                                    <div class="p-2.5 bg-white border border-neutral-200/80 rounded-xl text-left flex items-center justify-between gap-2 shadow-xs">
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider">File di Server:</p>
                                            <p class="text-xs text-blue-700 truncate font-mono font-medium"><?= $e(basename($item['cover'])) ?></p>
                                        </div>
                                        <a href="<?= $e($item['cover']) ?>" target="_blank" class="px-2.5 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-800 rounded-lg text-xs font-bold transition-colors">Buka</a>
                                    </div>
                                <?php endif; ?>

                                <div class="text-xs font-medium text-neutral-600 space-y-1 leading-relaxed bg-white/80 p-3 rounded-xl border border-neutral-200/60 shadow-xs text-left">
                                    <p class="font-bold text-neutral-800 mb-1">Ketentuan File (Wajib):</p>
                                    <p>• Format: <span class="font-semibold text-neutral-800">JPG, PNG, JPEG</span></p>
                                    <p>• Ukuran Maksimal: <span class="font-semibold text-neutral-800">5 MB</span></p>
                                    <p>• Rasio Disarankan: <span class="font-semibold text-neutral-800">3 : 4 (Potret)</span></p>
                                </div>

                                <div class="pt-1 border-t border-neutral-200/60">
                                    <p id="upload-status" class="text-xs font-semibold text-neutral-500 leading-relaxed text-left">
                                        <span class="text-blue-600 font-medium">Catatan: <?= !empty($item['cover']) ? 'Jika tidak memilih foto baru, foto lama dipertahankan.' : 'Akan diunggah saat Anda klik Simpan di bawah.' ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Upload PDF & Link Flipbook -->
                <div class="p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl shadow-sm hover:shadow transition-shadow flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <label class="block text-sm font-extrabold text-neutral-900 tracking-wide">Berkas Dokumen PDF <span class="text-red-500">*</span></label>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= !empty($item['file_path']) ? 'bg-emerald-100 text-emerald-900' : 'bg-neutral-200 text-neutral-800' ?>"><?= !empty($item['file_path']) ? 'File Aktif' : 'PDF (Maks 25MB)' ?></span>
                        </div>

                        <!-- Read Data Status Dokumen Aktif (Khusus Mode Update) -->
                        <?php if ($isEdit && !empty($item['file_path'])): ?>
                            <div class="p-3.5 bg-emerald-50/80 border border-emerald-300/80 rounded-xl text-left space-y-2 shadow-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-emerald-900">Dokumen Aktif di Database:</span>
                                    <span class="text-[11px] font-bold bg-white px-2 py-0.5 rounded-md text-emerald-800 border border-emerald-200"><?= $e($item['file_size_formatted'] ?? '0 KB') ?></span>
                                </div>
                                <div class="flex items-center justify-between gap-3 bg-white p-2.5 rounded-lg border border-emerald-200">
                                    <p class="text-xs text-neutral-700 font-mono truncate max-w-[200px] sm:max-w-xs font-medium"><?= $e($item['file_path']) ?></p>
                                    <a href="<?= $e($item['file_path']) ?>" target="_blank" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-xs shadow-sm whitespace-nowrap transition-colors">
                                        Unduh / Buka
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- PENTING: Input hidden untuk menampung URL PDF hasil upload murni, mencegah error Cannot set properties of null -->
                        <input type="hidden" id="pdf-url-input" name="file_path" value="<?= $e($item['file_path'] ?? '') ?>" />

                        <!-- Kotak Input Link / Path -->
                        <div class="space-y-3.5 pt-1">
                            <!-- Input Tambahan: Tautan File Flipbook -->
                            <div class="p-3 bg-blue-50/50 border border-blue-200/60 rounded-xl space-y-1.5">
                                <label class="block text-[11px] font-bold text-blue-900 uppercase tracking-wider">Tautan File Flipbook (Wajib): <span class="text-red-500">*</span></label>
                                <div>
                                    <input type="text" id="flipbook-url-input" name="path_flipbook" value="<?= $e($item['path_flipbook'] ?? '') ?>" placeholder="https://heyzine.com/flip-book/... atau tautan e-book interaktif" class="form-input w-full py-2 px-3.5 rounded-lg border border-neutral-300 bg-white text-sm font-medium text-neutral-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all" required />
                                </div>
                                <p class="text-[11px] text-neutral-600 font-medium italic">Wajib disematkan agar pengunjung dapat langsung membaca secara online (Heyzine, FlipHTML5, dll).</p>
                            </div>

                            <!-- Tombol Pilih PDF & Buka Preview -->
                            <div class="flex flex-wrap items-center gap-2.5 pt-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl shadow-md transition-all transform active:scale-95 w-full sm:w-auto justify-center">
                                    <span><?= !empty($item['file_path']) ? 'Ganti File PDF' : 'Pilih File PDF (Wajib Maks 25 MB)' ?></span>
                                    <input type="file" id="pdf-file-input" accept=".pdf,application/pdf" onchange="handlePdfSelect(this)" class="hidden" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 mt-4 border-t border-neutral-200/60">
                        <p id="pdf-upload-status" class="text-xs font-semibold text-neutral-500 leading-relaxed">
                            <span class="text-emerald-700 font-medium">Catatan: <?= !empty($item['file_path']) ? 'Jika tidak memilih file baru, dokumen lama tetap aman dipertahankan.' : 'File PDF wajib diunggah & diproses otomatis saat klik Simpan.' ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOK 4: SINOPSIS KARYA -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-900 text-white text-xs font-bold">4</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Sinopsis / Ringkasan Buku <span class="text-red-500">*</span></h3>
            </div>

            <div>
                <textarea id="sinopsis-input" name="sinopsis" rows="6" placeholder="Tuliskan ringkasan menarik, abstraksi, atau latar belakang penulisan buku/modul ini agar pembaca tertarik membaca..." class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white p-4 text-sm font-normal text-neutral-800 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all leading-relaxed" required><?= $e($item['sinopsis'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- FOOTER: TOMBOL AKSI -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t-2 border-neutral-100">
            <a href="/admin/buku" class="w-full sm:w-auto text-center px-6 py-2.5 rounded-xl text-sm font-bold text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 transition-colors">
                Batal & Kembali
            </a>
            <button type="submit" id="submit-btn" class="w-full sm:w-auto btn btn-primary px-8 py-3 rounded-xl shadow-lg font-bold text-sm tracking-wide transition-all transform active:scale-95">
                <?= $isEdit ? 'Simpan Perubahan Buku' : 'Terbitkan & Simpan Buku' ?>
            </button>
        </div>
    </form>
</section>

<!-- Include SweetAlert2 Library untuk Notifikasi Modern -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Skrip Pratinjau Lokal, Generator Slug & Submit Form -->
<script>
    // 0. Auto-Generate Slug dari Judul Buku
    (function() {
        var judulInput = document.getElementById('judul-input');
        var slugInput = document.getElementById('slug-input');
        var isEditMode = <?= $isEdit ? 'true' : 'false' ?>;
        var customSlug = isEditMode; // Jika mode edit, abaikan override otomatis kecuali user mengosongkannya

        if (judulInput && slugInput) {
            slugInput.addEventListener('input', function() {
                customSlug = (this.value.trim() !== '');
            });

            judulInput.addEventListener('input', function() {
                if (!customSlug || slugInput.value.trim() === '') {
                    var txt = this.value.toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '') // Hapus karakter spesial non-alfanumerik
                        .replace(/\s+/g, '-') // Ganti spasi dengan minus (-)
                        .replace(/-+/g, '-'); // Hindari duplikasi minus
                    slugInput.value = txt;
                }
            });
        }
    })();

    // Helper: Cari elemen input berdasar name atau id agar aman dan tidak pernah null
    function getFormEl(fieldName) {
        var form = document.getElementById('buku-form');
        if (form && form[fieldName]) return form[fieldName];
        return document.querySelector('[name="' + fieldName + '"]') || document.getElementById(fieldName + '-input') || document.getElementById(fieldName);
    }

    // Helper: Reset status validasi visual
    function clearValidationErrors() {
        var errorFields = ['judul', 'sinopsis', 'tahun', 'penulis', 'penerbit', 'halaman', 'path_flipbook'];
        errorFields.forEach(function(fieldName) {
            var el = getFormEl(fieldName);
            if (el && el.classList) {
                el.classList.remove('border-red-500', 'ring-2', 'ring-red-400', 'bg-red-50/10');
            }
        });
    }

    // Helper: Tandai input tidak valid (warna merah + scroll)
    function highlightErrorField(fieldName, shouldScroll = false) {
        var el = getFormEl(fieldName);
        if (el && el.classList) {
            el.classList.add('border-red-500', 'ring-2', 'ring-red-400', 'bg-red-50/10');
            if (shouldScroll) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                setTimeout(() => el.focus(), 500);
            }
        }
    }

    // 1. Pratinjau Lokal Foto Cover & Validasi Ukuran (Maks 5 MB)
    function handleCoverSelect(input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var maxCoverSize = 5 * 1024 * 1024; // 5 MB

        if (file.size > maxCoverSize) {
            var sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran Foto Terlalu Besar!',
                    text: 'Ukuran foto cover yang dipilih adalah ' + sizeMb + ' MB. Melebihi batas maksimal 5 MB! Silakan kompres atau pilih foto dengan ukuran lebih kecil untuk melindungi kapasitas server.',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Pilih Ulang Foto'
                });
            } else {
                alert('Ukuran foto cover melebihi batas maksimal 5 MB!');
            }
            input.value = '';
            var status = document.getElementById('upload-status');
            if (status) status.innerHTML = '<span class="text-red-600 font-bold">Pemilihan dibatalkan: Ukuran foto melampaui 5 MB.</span>';
            return;
        }

        var reader = new FileReader();

        reader.onload = function(e) {
            var prev = document.getElementById('cover-preview');
            var placeholder = document.getElementById('cover-placeholder');
            var status = document.getElementById('upload-status');

            if (placeholder) placeholder.classList.add('hidden');
            prev.src = e.target.result;
            prev.classList.remove('hidden');

            status.innerHTML = '<span class="text-emerald-700 font-bold">Foto terpilih: ' + file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)</span><br><span class="text-[11px] text-slate-500 font-medium italic">Akan diunggah otomatis saat Anda menyimpan data.</span>';
        };

        reader.readAsDataURL(file);
    }

    // 2. Pemilihan Lokal File PDF & Validasi Ukuran (Maks 25 MB)
    function handlePdfSelect(input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        var maxPdfSize = 25 * 1024 * 1024; // 25 MB
        var status = document.getElementById('pdf-upload-status');

        if (file.size > maxPdfSize) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran PDF Melampaui Batas!',
                    text: 'Ukuran file dokumen PDF yang dipilih mencapai ' + sizeMb + ' MB, melebihi batas maksimal server (25 MB). Silakan perkecil (compress) ukuran file PDF Anda terlebih dahulu.',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('Ukuran file PDF melebihi batas maksimal 25 MB!');
            }
            input.value = '';
            if (status) status.innerHTML = '<span class="text-red-600 font-bold">Pemilihan dibatalkan: Ukuran PDF melampaui 25 MB.</span>';
            return;
        }

        if (status) {
            status.innerHTML = '<span class="text-emerald-700 font-bold">File PDF terpilih: ' + file.name + ' (' + sizeMb + ' MB)</span><br><span class="text-[11px] text-slate-500 font-medium italic">Akan diunggah otomatis saat Anda menyimpan data.</span>';
        }
    }

    // Helper: Fungsi asynchronous untuk upload file ke server via fetch
    async function uploadFileToServer(file, fieldName, statusEl, progressMsg, token) {
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-amber-600 font-bold animate-pulse">' + progressMsg + '</span>';
        }

        var formData = new FormData();
        formData.append(fieldName, file);

        var res = await fetch('/admin/buku/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            body: formData
        });

        if (!res.ok) {
            var errText = await res.text();
            throw new Error('Gagal mengunggah berkas ' + file.name + ' (HTTP ' + res.status + ')');
        }

        var json = await res.json();
        if (!json.data || !json.data.url) {
            throw new Error(json.error || 'Gagal mengunggah berkas ' + file.name);
        }

        if (statusEl) {
            statusEl.innerHTML = '<span class="text-emerald-700 font-bold">Berkas ' + file.name + ' berhasil disiapkan.</span>';
        }

        return json.data.url;
    }

    // 3. Skrip Submit Form & Validasi Keamanan Data
    async function submitBukuForm(event) {
        event.preventDefault();

        var form = event.target;
        var btn = document.getElementById('submit-btn');
        var originalText = btn.innerHTML;

        // Reset semua highlight validasi sebelumnya
        clearValidationErrors();

        // Ambil nilai semua input utama
        var judulVal = (form.judul ? form.judul.value : '').trim();
        var sinopsisVal = (form.sinopsis ? form.sinopsis.value : '').trim();
        var tahunVal = (form.tahun ? form.tahun.value : '').trim();
        var penulisVal = (form.penulis ? form.penulis.value : '').trim();
        var penerbitVal = (form.penerbit ? form.penerbit.value : '').trim();
        var halamanVal = (form.halaman ? form.halaman.value : '').trim();
        var pdfUrlVal = (document.getElementById('pdf-url-input') ? document.getElementById('pdf-url-input').value : '').trim();
        var flipbookUrlVal = (document.getElementById('flipbook-url-input') ? document.getElementById('flipbook-url-input').value : '').trim();
        var coverUrlVal = (document.getElementById('cover-url-input') ? document.getElementById('cover-url-input').value : '').trim();

        var coverInput = document.getElementById('cover-file-input');
        var pdfInput = document.getElementById('pdf-file-input');
        var coverFile = (coverInput && coverInput.files && coverInput.files[0]) ? coverInput.files[0] : null;
        var pdfFile = (pdfInput && pdfInput.files && pdfInput.files[0]) ? pdfInput.files[0] : null;

        // 1. Cek jika SEMUA inputan benar-benar kosong total (khusus saat Tambah Buku)
        var isAllEmpty = !judulVal && !sinopsisVal && !pdfUrlVal && !flipbookUrlVal && !coverUrlVal && !coverFile && !pdfFile && (!halamanVal || halamanVal === '0');
        if (isAllEmpty) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulir Masih Kosong!',
                    text: 'Anda belum mengisi parameter apapun. Sistem menolak penyimpanan form kosong untuk menjaga kebersihan data dan stabilitas server. Harap isi data buku terlebih dahulu.',
                    confirmButtonColor: '#1e293b',
                    confirmButtonText: 'Lengkapi Formulir'
                });
            } else {
                alert('Formulir masih kosong total! Harap isi informasi buku sebelum menyimpan.');
            }
            highlightErrorField('judul', true);
            return;
        }

        // 2. Validasi Wajib Isi Semua Parameter (Melindungi Server & Keutuhan Data)
        var errors = [];
        var firstErrorField = null;

        if (!judulVal) {
            errors.push('<b>Judul Buku</b> tidak boleh kosong (wajib diisi).');
            highlightErrorField('judul', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'judul';
        } else if (judulVal.length < 3) {
            errors.push('<b>Judul Buku</b> terlalu pendek (minimal 3 karakter).');
            highlightErrorField('judul', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'judul';
        }

        if (!penulisVal) {
            errors.push('<b>Penulis / Tim Penyusun</b> tidak boleh kosong (wajib diisi).');
            highlightErrorField('penulis', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'penulis';
        }

        if (!penerbitVal) {
            errors.push('<b>Penerbit / Instansi</b> tidak boleh kosong (wajib diisi).');
            highlightErrorField('penerbit', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'penerbit';
        }

        if (!tahunVal || isNaN(tahunVal) || parseInt(tahunVal) < 1900 || parseInt(tahunVal) > 2100) {
            errors.push('<b>Tahun Terbit</b> wajib diisi dan logis (kisaran 1900–2100).');
            highlightErrorField('tahun', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'tahun';
        }

        if (!halamanVal || isNaN(halamanVal) || parseInt(halamanVal) <= 0) {
            errors.push('<b>Jumlah Halaman</b> wajib diisi minimal 1 halaman.');
            highlightErrorField('halaman', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'halaman';
        }

        if (!flipbookUrlVal) {
            errors.push('<b>Tautan File Flipbook</b> wajib diisi agar pengunjung dapat langsung membaca online.');
            highlightErrorField('path_flipbook', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'path_flipbook';
        }

        if (!sinopsisVal) {
            errors.push('<b>Sinopsis / Ringkasan Buku</b> wajib diisi.');
            highlightErrorField('sinopsis', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'sinopsis';
        } else if (sinopsisVal.length < 10) {
            errors.push('<b>Sinopsis</b> harus memberikan gambaran karya (minimal 10 karakter).');
            highlightErrorField('sinopsis', !firstErrorField);
            if (!firstErrorField) firstErrorField = 'sinopsis';
        }

        // Validasi Wajib Foto & PDF
        if (!coverFile && !coverUrlVal) {
            errors.push('<b>Foto Cover</b> wajib dipilih / diunggah (Maksimal 5 MB, format JPG/PNG/JPEG).');
        } else if (coverFile && coverFile.size > (5 * 1024 * 1024)) {
            errors.push('<b>Foto Cover</b> melebihi batas maksimal 5 MB (' + (coverFile.size / (1024 * 1024)).toFixed(2) + ' MB).');
        }

        if (!pdfFile && !pdfUrlVal) {
            errors.push('<b>File Dokumen PDF</b> wajib diunggah (Maksimal 25 MB).');
        } else if (pdfFile && pdfFile.size > (25 * 1024 * 1024)) {
            errors.push('<b>File Dokumen PDF</b> melebihi batas maksimal 25 MB (' + (pdfFile.size / (1024 * 1024)).toFixed(2) + ' MB).');
        }

        if (errors.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal!',
                    html: '<div class="text-left text-sm font-normal text-slate-700 mt-1">Sistem melindungi server dari input yang tidak memenuhi standar. Mohon perbaiki daftar kesalahan berikut:<ul class="list-disc pl-5 mt-2.5 space-y-1">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul><p class="mt-3 text-xs font-bold text-red-600">Silakan periksa kembali kolom masukan berwarna merah di atas.</p></div>',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Periksa & Perbaiki'
                });
            } else {
                alert('Validasi Gagal:\n' + errors.map(e => e.replace(/<[^>]*>?/gm, '')).join('\n'));
            }
            return;
        }

        // Mulai pemrosesan submit
        btn.disabled = true;
        btn.innerHTML = 'Sedang Memproses...';

        var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
        var url = isEdit ? '/admin/buku/<?= (int) ($item['id'] ?? 0) ?>/update' : '/admin/buku';

        try {
            // A. Unggah Foto Cover jika ada file foto baru
            if (coverInput && coverInput.files && coverInput.files[0]) {
                btn.innerHTML = 'Mengunggah Foto Sampul...';
                var coverStatus = document.getElementById('upload-status');
                var coverUrl = await uploadFileToServer(coverInput.files[0], 'cover', coverStatus, 'Mengunggah foto sampul...', token);
                var coverInputEl = document.getElementById('cover-url-input');
                if (coverInputEl) coverInputEl.value = coverUrl;
            }

            // B. Unggah Berkas PDF jika ada file PDF baru (Dengan jaman anti null-error)
            var pdfUrlInputEl = document.getElementById('pdf-url-input');
            if (pdfInput && pdfInput.files && pdfInput.files[0]) {
                btn.innerHTML = 'Mengunggah Berkas PDF...';
                var pdfStatus = document.getElementById('pdf-upload-status');
                var pdfUrl = await uploadFileToServer(pdfInput.files[0], 'pdf', pdfStatus, 'Mengunggah berkas PDF (' + (pdfInput.files[0].size / (1024 * 1024)).toFixed(2) + ' MB)...', token);
                if (pdfUrlInputEl) {
                    pdfUrlInputEl.value = pdfUrl;
                } else {
                    pdfUrlInputEl = document.createElement('input');
                    pdfUrlInputEl.type = 'hidden';
                    pdfUrlInputEl.id = 'pdf-url-input';
                    pdfUrlInputEl.name = 'file_path';
                    pdfUrlInputEl.value = pdfUrl;
                    form.appendChild(pdfUrlInputEl);
                }
            }

            // C. Kirim Data Buku (JSON) ke Database
            btn.innerHTML = 'Menyimpan ke Database...';
            var data = {
                judul: form.judul.value,
                slug: form.slug.value,
                kategori: form.kategori.value,
                status: form.status.value,
                penulis: form.penulis.value,
                penerbit: form.penerbit.value,
                tahun: form.tahun.value,
                halaman: form.halaman.value,
                isbn: form.isbn.value,
                cover: document.getElementById('cover-url-input') ? document.getElementById('cover-url-input').value : '',
                file_path: document.getElementById('pdf-url-input') ? document.getElementById('pdf-url-input').value : (form.file_path ? form.file_path.value : ''),
                path_flipbook: document.getElementById('flipbook-url-input') ? document.getElementById('flipbook-url-input').value : '',
                sinopsis: form.sinopsis.value
            };

            var res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            });

            if (!res.ok) {
                var errorText = await res.text();
                try {
                    var errJson = JSON.parse(errorText);
                    var errMsg = errJson.error || res.statusText;

                    // Pemetaan validasi dari Server-Side ke Client Input Merah
                    if (errMsg.toLowerCase().includes('judul')) highlightErrorField('judul', true);
                    if (errMsg.toLowerCase().includes('sinopsis')) highlightErrorField('sinopsis', true);
                    if (errMsg.toLowerCase().includes('tahun')) highlightErrorField('tahun', true);
                    if (errMsg.toLowerCase().includes('penulis')) highlightErrorField('penulis', true);
                    if (errMsg.toLowerCase().includes('penerbit')) highlightErrorField('penerbit', true);
                    if (errMsg.toLowerCase().includes('halaman')) highlightErrorField('halaman', true);
                    if (errMsg.toLowerCase().includes('flipbook')) highlightErrorField('path_flipbook', true);

                    throw new Error(errMsg);
                } catch (e) {
                    if (e.message && !errorText.startsWith('<') && !errorText.includes('<b>')) {
                        throw e;
                    }
                    console.error('SERVER RESPONSE ERROR:', errorText);
                    throw new Error('Server Error (' + res.status + '): ' + (errorText.replace(/<[^>]*>?/gm, ' ').trim().slice(0, 150) || res.statusText));
                }
            }

            var jsonRes = await res.json();
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (jsonRes.data && (jsonRes.data.id || jsonRes.data.updated)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? 'Perubahan Berhasil Disimpan' : 'Buku Berhasil Diterbitkan',
                        text: isEdit ? 'Data buku telah sukses diperbarui di database sistem.' : 'Karya baru telah ditambahkan ke dalam Katalog Buku.',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonColor: '#1e293b',
                        confirmButtonText: 'Kembali ke Katalog ➔',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = '/admin/buku';
                    });
                } else {
                    alert(isEdit ? 'Perubahan berhasil disimpan.' : 'Buku berhasil diterbitkan.');
                    window.location.href = '/admin/buku';
                }
            } else {
                var errorMsg = jsonRes.error || 'Periksa kembali input Anda.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: errorMsg,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    alert('Gagal menyimpan: ' + errorMsg);
                }
            }

        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = originalText;
            console.error('Detail Kesalahan:', error);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kendala Sistem atau Validasi',
                    text: error.message || 'Silakan cek koneksi jaringan atau hubungi administrator.',
                    confirmButtonColor: '#1e293b',
                    confirmButtonText: 'Tutup'
                });
            } else {
                alert('Terjadi kendala: ' + error.message);
            }
        }
    }
</script>
</section>