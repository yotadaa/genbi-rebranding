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
                Lengkapi metadata karya publikasi, sampul buku, dan berkas digital E-Book/PDF GenBI.
            </p>
        </div>
        <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50/80 border border-blue-100 text-blue-900 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <span>Modul Literasi Digital GenBI</span>
        </div>
    </header>

    <!-- Form Utama -->
    <form id="buku-form" class="admin-card mt-6 p-6 md:p-10 shadow-sm border border-neutral-200/80 rounded-2xl bg-white space-y-8" onsubmit="submitBukuForm(event)">

        <!-- BLOK 1: INFORMASI KARYA & KLASIFIKASI -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-neutral-200 text-neutral-900">
                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-900 text-white text-xs font-bold">1</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Informasi Karya & Klasifikasi</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Judul Buku -->
                <div class="md:col-span-6">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Judul Buku / Karya <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" value="<?= $e($item['judul'] ?? '') ?>" placeholder="Contoh: Majalah GenBI Jambi Edisi 2026" required class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 placeholder:text-neutral-400 transition-all" />
                </div>

                <!-- Kategori Buku -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Kategori Buku</label>
                    <select name="kategori" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-semibold text-blue-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all">
                        <?php
                        $opts = ['Publikasi', 'Majalah & Buletin', 'Modul & Panduan', 'Karya Tulis', 'Jurnal & Laporan', 'Karangan & Novel', 'Lainnya'];
                        $curr = $item['kategori'] ?? 'Publikasi';
                        foreach ($opts as $o):
                        ?>
                            <option value="<?= $e($o) ?>" <?= $curr === $o ? 'selected' : '' ?>><?= $e($o) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Publikasi -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Status Tayangan</label>
                    <select name="status" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-bold text-neutral-800 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all">
                        <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Tayang)</option>
                        <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Disembunyikan)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BLOK 2: DETAIL PENERBITAN & METADATA -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-900 text-white text-xs font-bold">2</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Detail Penerbitan & Spesifikasi Buku</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Penulis -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Penulis / Tim Penyusun</label>
                    <input type="text" name="penulis" value="<?= $e($item['penulis'] ?? 'GenBI Jambi') ?>" placeholder="Nama penulis atau tim" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>

                <!-- Penerbit -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Penerbit / Instansi</label>
                    <input type="text" name="penerbit" value="<?= $e($item['penerbit'] ?? 'Bank Indonesia') ?>" placeholder="Nama instansi/penerbit" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>

                <!-- Tahun Terbit -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Tahun Terbit</label>
                    <input type="number" name="tahun" value="<?= $e($item['tahun'] ?? date('Y')) ?>" min="1990" max="2100" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-bold text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>

                <!-- Jumlah Halaman -->
                <div class="lg:col-span-1">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Jum. Halaman</label>
                    <?php $hlm = (int) preg_replace('/\D/', '', (string) ($item['halaman'] ?? '0')); ?>
                    <input type="number" name="halaman" value="<?= $hlm > 0 ? $hlm : '' ?>" placeholder="Cth: 120" min="1" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-medium text-neutral-900 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all" />
                </div>

                <!-- Nomor ISBN -->
                <div class="sm:col-span-2 lg:col-span-5">
                    <label class="block text-sm font-bold text-neutral-800 tracking-wide">Nomor ISBN / Kode Identitas Karya (Opsional)</label>
                    <input type="text" name="isbn" value="<?= $e($item['isbn'] ?? '-') ?>" placeholder="Contoh: 978-602-8519-93-9 atau biarkan berisi -" class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white py-2.5 px-4 text-sm font-mono text-neutral-700 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all max-w-md" />
                </div>
            </div>
        </div>

        <!-- BLOK 3: SAMPUL BUKU (COVER) & BERKAS E-BOOK (PDF / LINK) -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-900 text-white text-xs font-bold">3</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Cover & Berkas Digital (PDF / Link E-Book)</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <!-- 1. Upload Cover Buku -->
                <div class="p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl shadow-sm hover:shadow transition-shadow flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3 mb-4">
                            <label class="block text-sm font-extrabold text-neutral-900 tracking-wide">Foto Cover Buku</label>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-900">Rasio 3:4</span>
                        </div>

                        <!-- Layout Cover & Info dengan Ukuran Statis (Anti-Aneh / Anti-Terpotong) -->
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                            <!-- Kotak Preview dengan dimensi statis yang pasti -->
                            <div style="width: 160px; min-width: 160px; height: 220px;" class="bg-white border-2 border-dashed border-neutral-300 rounded-xl overflow-hidden shadow-inner flex flex-col items-center justify-center p-2.5 relative flex-shrink-0">
                                <!-- SVG Placeholder jika foto belum ada -->
                                <div id="cover-placeholder" class="flex flex-col items-center justify-center text-center p-2 <?= !empty($item['cover']) ? 'hidden' : '' ?>">
                                    <svg class="w-10 h-10 mb-2 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span class="text-[11px] font-bold text-neutral-400">Belum Ada Cover</span>
                                </div>
                                <!-- Preview Gambar (object-contain menjaga rasio asli) -->
                                <img id="cover-preview" src="<?= $e($item['cover'] ?? '') ?>" alt="Cover Preview" class="w-full h-full object-contain rounded-lg drop-shadow-sm <?= empty($item['cover']) ? 'hidden' : '' ?>" />
                            </div>

                            <!-- Area Tombol & Instruksi -->
                            <div class="flex-1 w-full space-y-3.5 text-center sm:text-left">
                                <input type="hidden" name="cover" id="cover-url-input" value="<?= $e($item['cover'] ?? '') ?>" />

                                <div>
                                    <label class="inline-flex items-center justify-center gap-2 cursor-pointer px-4 py-2.5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded-xl shadow-md transition-all transform active:scale-95 w-full sm:w-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        <span>Pilih Foto Cover</span>
                                        <input type="file" id="cover-file-input" accept="image/*" onchange="handleCoverSelect(this)" class="hidden" />
                                    </label>
                                </div>

                                <div class="text-xs font-medium text-neutral-600 space-y-1 leading-relaxed bg-white/80 p-3 rounded-xl border border-neutral-200/60 shadow-xs text-left">
                                    <p class="font-bold text-neutral-800 mb-1">📌 Ketentuan File :</p>
                                    <p>• Format : <span class="font-semibold text-neutral-800">JPG, PNG, WEBP</span></p>
                                    <p>• Ukuran Maksimal : <span class="font-semibold text-neutral-800">5 MB</span></p>
                                    <p>• Rasio Disarankan : <span class="font-semibold text-neutral-800">3 : 4 (Potret)</span></p>
                                </div>

                                <div class="pt-1 border-t border-neutral-200/60">
                                    <p id="upload-status" class="text-xs font-semibold text-neutral-500 leading-relaxed text-left">
                                        <span class="text-blue-600 italic font-medium">💡 Akan diunggah saat Anda klik Simpan di bawah.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Upload PDF / Tautan Cloud E-Book -->
                <div class="p-6 bg-slate-50/70 border border-slate-200/80 rounded-2xl shadow-sm hover:shadow transition-shadow flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-neutral-200 pb-3">
                            <label class="block text-sm font-extrabold text-neutral-900 tracking-wide">Berkas Dokumen E-Book / PDF</label>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900">PDF / Cloud</span>
                        </div>

                        <p class="text-xs text-neutral-600 leading-relaxed bg-white/80 p-3.5 rounded-xl border border-neutral-200/60 shadow-xs">
                            Pilih file PDF dari perangkat Anda, <strong>ATAU</strong> sematkan tautan dokumen eksternal seperti <span class="text-blue-700 font-bold">Google Drive / OneDrive</span>.
                        </p>

                        <!-- Kotak Input Link / Path -->
                        <div class="space-y-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">Tautan / Alamat File :</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="pdf-url-input" name="file_path" value="<?= $e($item['file_path'] ?? '') ?>" placeholder="https://drive.google.com/... atau pilih dari tombol di bawah" class="form-input w-full pl-10 pr-4 py-2.5 rounded-xl border border-neutral-300 bg-white text-sm font-medium text-neutral-800 shadow-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all" />
                                </div>
                            </div>

                            <!-- Tombol Pilih PDF & Buka Preview -->
                            <div class="flex flex-wrap items-center gap-2.5 pt-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl shadow-md transition-all transform active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <span>Pilih File PDF (Maks 25 MB)</span>
                                    <input type="file" id="pdf-file-input" accept=".pdf,application/pdf" onchange="handlePdfSelect(this)" class="hidden" />
                                </label>

                                <?php if (!empty($item['file_path'])): ?>
                                    <a href="<?= $e($item['file_path']) ?>" target="_blank" id="btn-open-pdf" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-bold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 rounded-xl shadow-sm transition-all">
                                        <span>Buka File Saat Ini ↗</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 mt-4 border-t border-neutral-200/60">
                        <p id="pdf-upload-status" class="text-xs font-semibold text-neutral-500 leading-relaxed">
                            <span class="text-emerald-700 italic font-medium">💡 File PDF akan diunggah otomatis saat Anda klik Simpan.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOK 4: SINOPSIS KARYA -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-200 text-neutral-900 mt-6">
                <span class="flex items-center justify-center w-4 h-4 rounded-full bg-blue-900 text-white text-xs font-bold">4</span>
                <h3 class="text-base font-extrabold tracking-wide uppercase">Sinopsis / Ringkasan Buku <span class="text-red-500">*</span></h3>
            </div>

            <textarea name="sinopsis" rows="6" placeholder="Tuliskan ringkasan menarik, abstraksi, atau latar belakang penulisan buku/modul ini agar pembaca tertarik membaca..." required class="form-input w-full mt-2 rounded-xl border border-neutral-300 bg-white p-4 text-sm font-normal text-neutral-800 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100 transition-all leading-relaxed"><?= $e($item['sinopsis'] ?? '') ?></textarea>
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

<!-- Skrip Pratinjau Lokal & Submit Form yang Menggabungkan Upload + SweetAlert2 -->
<script>
    // 1. Pratinjau Lokal Foto Cover (Menggunakan FileReader agar 100% anti gagal di semua browser)
    function handleCoverSelect(input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var reader = new FileReader();

        reader.onload = function(e) {
            var prev = document.getElementById('cover-preview');
            var placeholder = document.getElementById('cover-placeholder');
            var status = document.getElementById('upload-status');

            if (placeholder) placeholder.classList.add('hidden');
            prev.src = e.target.result; // Data URL Base64 (100% didukung browser & anti brokend-image)
            prev.classList.remove('hidden');

            status.innerHTML = '✅ <span class="text-emerald-700 font-bold">Foto terpilih: ' + file.name + '</span><br><span class="text-[11px] text-blue-700 font-medium italic">💡 Akan diunggah otomatis saat Anda klik tombol Simpan di bawah.</span>';
        };

        reader.readAsDataURL(file);
    }

    // 2. Pemilihan Lokal File PDF (Tanpa upload ke server sebelum klik Simpan)
    function handlePdfSelect(input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        var status = document.getElementById('pdf-upload-status');

        status.innerHTML = '✅ <span class="text-emerald-700 font-bold">File PDF terpilih: ' + file.name + ' (' + sizeMb + ' MB)</span><br><span class="text-[11px] text-emerald-700 font-medium italic">💡 File PDF akan diunggah otomatis saat Anda klik tombol Simpan di bawah.</span>';
    }

    // Helper: Fungsi asynchronous untuk upload file ke server via fetch
    async function uploadFileToServer(file, fieldName, statusEl, progressMsg, token) {
        if (statusEl) {
            statusEl.innerHTML = '⏳ <span class="text-amber-600 font-bold animate-pulse">' + progressMsg + '</span>';
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
            throw new Error('Gagal mengunggah file ' + file.name + ' (HTTP ' + res.status + ')');
        }

        var json = await res.json();
        if (!json.data || !json.data.url) {
            throw new Error(json.error || 'Gagal mengunggah file ' + file.name);
        }

        if (statusEl) {
            statusEl.innerHTML = '✅ <span class="text-emerald-700 font-bold">File ' + file.name + ' berhasil diunggah!</span>';
        }

        return json.data.url;
    }

    // 3. Skrip Submit Form (Eksekusi Upload File Dahulu -> Simpan Data -> Tampilkan SweetAlert2)
    async function submitBukuForm(event) {
        event.preventDefault();

        var form = event.target;
        var btn = document.getElementById('submit-btn');
        var originalText = btn.innerHTML;

        // Validasi Peringatan Awal (Warning SweetAlert)
        if (!form.judul.value.trim() || !form.sinopsis.value.trim()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Mohon isi Judul Buku dan Sinopsis terlebih dahulu sebelum menyimpan data.',
                    confirmButtonColor: '#1e3a8a',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('⚠️ Mohon isi Judul Buku dan Sinopsis terlebih dahulu!');
            }
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '⏳ Sedang Memproses Karya...';

        var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
        var url = isEdit ? '/admin/buku/<?= (int) ($item['id'] ?? 0) ?>/update' : '/admin/buku';

        try {
            // A. Unggah Foto Cover ke server jika ada file foto baru dipilih
            var coverInput = document.getElementById('cover-file-input');
            if (coverInput && coverInput.files && coverInput.files[0]) {
                btn.innerHTML = '⏳ Mengunggah Foto Sampul...';
                var coverStatus = document.getElementById('upload-status');
                var coverUrl = await uploadFileToServer(coverInput.files[0], 'cover', coverStatus, 'Mengunggah foto sampul ke server...', token);
                document.getElementById('cover-url-input').value = coverUrl;
            }

            // B. Unggah Berkas PDF ke server jika ada file PDF baru dipilih
            var pdfInput = document.getElementById('pdf-file-input');
            if (pdfInput && pdfInput.files && pdfInput.files[0]) {
                btn.innerHTML = '⏳ Mengunggah Berkas PDF...';
                var pdfStatus = document.getElementById('pdf-upload-status');
                var pdfUrl = await uploadFileToServer(pdfInput.files[0], 'pdf', pdfStatus, 'Mengunggah file PDF (' + (pdfInput.files[0].size / (1024 * 1024)).toFixed(2) + ' MB) ke server...', token);
                document.getElementById('pdf-url-input').value = pdfUrl;
            }

            // C. Kirim Data Buku (JSON) ke Database
            btn.innerHTML = '⏳ Menyimpan Data ke Database...';
            var data = {
                judul: form.judul.value,
                kategori: form.kategori.value,
                status: form.status.value,
                penulis: form.penulis.value,
                penerbit: form.penerbit.value,
                tahun: form.tahun.value,
                halaman: form.halaman.value,
                isbn: form.isbn.value,
                cover: document.getElementById('cover-url-input').value,
                file_path: form.file_path.value,
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
                    throw new Error(errJson.error || res.statusText);
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
                // Tampilkan SweetAlert2 Berhasil -> Muncul beberapa detik & Push redirect ke katalog buku
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? 'Perubahan Berhasil Disimpan!' : 'Buku Berhasil Diterbitkan!',
                        text: isEdit ? 'Data buku telah sukses diperbarui di dalam sistem.' : 'Karya baru telah ditambahkan ke dalam Katalog Buku GenBI!',
                        timer: 2800,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Ke Katalog Sekarang ➔',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = '/admin/buku';
                    });
                } else {
                    alert(isEdit ? '✅ Perubahan berhasil disimpan!' : '🎉 Buku berhasil diterbitkan!');
                    window.location.href = '/admin/buku';
                }
            } else {
                var errorMsg = jsonRes.error || 'Periksa kembali inputan Anda.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: errorMsg,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    alert('❌ Gagal menyimpan: ' + errorMsg);
                }
            }

        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = originalText;
            console.error('Detail Kesalahan:', error);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan Sistem',
                    text: error.message || 'Silakan cek koneksi jaringan atau hubungi administrator.',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Tutup & Coba Lagi'
                });
            } else {
                alert('❌ Terjadi kendala: ' + error.message);
            }
        }
    }
</script>