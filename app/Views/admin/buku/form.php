<?php

/** @var callable $e */
$item = $item ?? null;
$isEdit = !empty($item && ($item['id'] ?? 0) > 0);
?>
<section class="mx-auto max-w-4xl">
    <!-- Header Form -->
    <header class="cms-header slide-in">
        <div>
            <a href="/admin/buku" class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-500 hover:text-blue-600 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Buku
            </a>
            <h1 class="section-title mt-1"><?= $isEdit ? 'Edit Buku & Koleksi' : 'Tambah Buku Baru' ?></h1>
            <p class="mt-2 text-sm text-neutral-600">Lengkapi metadata buku, publikasi, serta tautan file pendukung.</p>
        </div>
    </header>

    <!-- Card Form -->
    <form id="buku-form" class="admin-card mt-6 p-6 md:p-8 shadow-sm space-y-6" onsubmit="submitBukuForm(event)">
        <!-- Judul & Slug -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-neutral-800">Judul Buku / Karya <span class="text-red-500">*</span></label>
                <input type="text" name="judul" value="<?= $e($item['judul'] ?? '') ?>" placeholder="Contoh: Majalah GenBI Jambi Edisi 2026" required class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2.5 px-4 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100" />
            </div>
            <div>
                <label class="block text-sm font-bold text-neutral-800">Kategori Buku</label>
                <select name="kategori" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2.5 px-4 text-sm focus:border-blue-600">
                    <?php
                    $opts = ['Publikasi', 'Majalah & Buletin', 'Modul & Panduan', 'Karya Tulis', 'Jurnal & Laporan'];
                    $curr = $item['kategori'] ?? 'Publikasi';
                    foreach ($opts as $o):
                    ?>
                        <option value="<?= $e($o) ?>" <?= $curr === $o ? 'selected' : '' ?>><?= $e($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-neutral-800">Status Publikasi</label>
                <select name="status" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2.5 px-4 text-sm focus:border-blue-600">
                    <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Tayang di Web)</option>
                    <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Sembunyikan Sementara)</option>
                </select>
            </div>
        </div>

        <hr class="border-neutral-200" />

        <!-- Penulis, Penerbit, Tahun, Halaman -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
                <label class="block text-sm font-bold text-neutral-800">Penulis / Tim Penyusun</label>
                <input type="text" name="penulis" value="<?= $e($item['penulis'] ?? 'GenBI Jambi') ?>" placeholder="Nama penulis atau tim" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2 px-3.5 text-sm focus:border-blue-600" />
            </div>
            <div>
                <label class="block text-sm font-bold text-neutral-800">Penerbit</label>
                <input type="text" name="penerbit" value="<?= $e($item['penerbit'] ?? 'Bank Indonesia') ?>" placeholder="Nama instansi/penerbit" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2 px-3.5 text-sm focus:border-blue-600" />
            </div>
            <div>
                <label class="block text-sm font-bold text-neutral-800">Tahun Terbit</label>
                <input type="number" name="tahun" value="<?= $e($item['tahun'] ?? date('Y')) ?>" min="1990" max="2100" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2 px-3.5 text-sm focus:border-blue-600" />
            </div>
            <div>
                <label class="block text-sm font-bold text-neutral-800">Jumlah Halaman</label>
                <?php
                // Mengambil angka asli halaman dari string (misal dari "120 Halaman" menjadi 120)
                $hlm = (int) preg_replace('/\D/', '', (string) ($item['halaman'] ?? '0'));
                ?>
                <input type="number" name="halaman" value="<?= $hlm > 0 ? $hlm : '' ?>" placeholder="Contoh: 124" min="1" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2 px-3.5 text-sm focus:border-blue-600" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-neutral-800">Nomor ISBN (Opsional)</label>
                <input type="text" name="isbn" value="<?= $e($item['isbn'] ?? '-') ?>" placeholder="Contoh: 978-602-8519-93-9 atau -" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2 px-3.5 text-sm focus:border-blue-600" />
            </div>
        </div>

        <hr class="border-neutral-200" />

        <!-- Cover & URL File -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Upload Cover -->
            <div>
                <label class="block text-sm font-bold text-neutral-800">Foto Cover Buku</label>
                <div class="mt-2 flex items-start gap-4">
                    <div class="w-24 h-32 bg-neutral-100 border border-neutral-300 rounded-xl overflow-hidden shadow-sm flex items-center justify-center flex-shrink-0">
                        <img id="cover-preview" src="<?= $e($item['cover'] ?? '/assets/images/default-book.png') ?>" alt="Cover Preview" class="w-full h-full object-cover <?= empty($item['cover']) ? 'opacity-40' : '' ?>" />
                    </div>
                    <div class="flex-1">
                        <input type="hidden" name="cover" id="cover-url-input" value="<?= $e($item['cover'] ?? '') ?>" />
                        <label class="btn btn-secondary text-xs inline-flex items-center gap-2 cursor-pointer px-3 py-2 border rounded-lg bg-neutral-50 hover:bg-neutral-100 font-semibold">
                            <span>Unggah Foto</span>
                            <input type="file" id="cover-file-input" accept="image/*" onchange="uploadCoverImage(this)" class="hidden" />
                        </label>
                        <p id="upload-status" class="text-[11px] text-neutral-500 mt-2 leading-relaxed">Format JPG/PNG/WEBP. Maksimal 5MB. Rasio terbaik 3:4 (Potret).</p>
                    </div>
                </div>
            </div>

            <!-- Link E-book/PDF -->
            <div>
                <label class="block text-sm font-bold text-neutral-800">Tautan File E-Book / Dokumen PDF</label>
                <input type="text" name="file_path" value="<?= $e($item['file_path'] ?? '') ?>" placeholder="https://drive.google.com/... atau /uploads/buku/doc.pdf" class="form-input w-full mt-2 rounded-xl border-neutral-300 py-2.5 px-4 text-sm focus:border-blue-600" />
                <p class="text-xs text-neutral-500 mt-2 leading-relaxed">Anda dapat mencantumkan tautan Google Drive / Cloud, atau link langsung ke dokumen.</p>
            </div>
        </div>

        <!-- Sinopsis -->
        <div>
            <label class="block text-sm font-bold text-neutral-800">Sinopsis / Ringkasan Buku <span class="text-red-500">*</span></label>
            <textarea name="sinopsis" rows="4" placeholder="Tuliskan ringkasan menarik tentang isi buku ini..." required class="form-input w-full mt-2 rounded-xl border-neutral-300 p-3 text-sm focus:border-blue-600"><?= $e($item['sinopsis'] ?? '') ?></textarea>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
            <a href="/admin/buku" class="px-5 py-2.5 text-sm font-semibold text-neutral-600 hover:text-neutral-900 transition-colors">Batal</a>
            <button type="submit" id="submit-btn" class="btn btn-primary px-6 py-2.5 shadow-md font-semibold text-sm">
                <?= $isEdit ? 'Simpan Perubahan' : 'Terbit & Simpan Buku' ?>
            </button>
        </div>
    </form>
</section>

<!-- Script Khusus Form -->
<script>
    function uploadCoverImage(input) {
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var formData = new FormData();
        formData.append('cover', file);
        var token = document.querySelector('meta[name=csrf-token]')?.content || '';

        var status = document.getElementById('upload-status');
        status.textContent = '⏳ Mengunggah foto ke server...';
        status.className = 'text-[11px] text-amber-600 mt-2 font-semibold';

        fetch('/admin/buku/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                body: formData
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(res) {
                if (res.data && res.data.url) {
                    document.getElementById('cover-url-input').value = res.data.url;
                    var prev = document.getElementById('cover-preview');
                    prev.src = res.data.url;
                    prev.classList.remove('opacity-40');
                    status.textContent = '✅ Cover berhasil diunggah!';
                    status.className = 'text-[11px] text-emerald-600 mt-2 font-semibold';
                } else {
                    status.textContent = '❌ Gagal upload: ' + (res.error || 'Terjadi kesalahan');
                    status.className = 'text-[11px] text-red-600 mt-2 font-semibold';
                }
            })
            .catch(function() {
                status.textContent = '❌ Terjadi gangguan jaringan saat mengunggah foto.';
                status.className = 'text-[11px] text-red-600 mt-2 font-semibold';
            });
    }

    function submitBukuForm(event) {
        event.preventDefault();

        var btn = document.getElementById('submit-btn');
        btn.disabled = true;
        var originalText = btn.textContent;
        btn.textContent = 'Menyimpan...';

        var form = event.target;
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

        var token = document.querySelector('meta[name=csrf-token]')?.content || '';
        var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
        var url = isEdit ? '/admin/buku/<?= (int) ($item['id'] ?? 0) ?>/update' : '/admin/buku';

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(res) {
                btn.disabled = false;
                btn.textContent = originalText;

                if (res.data && (res.data.id || res.data.updated)) {
                    var toast = document.getElementById('admin-toast');
                    if (toast) {
                        toast.textContent = isEdit ? 'Perubahan buku berhasil disimpan!' : 'Buku baru berhasil ditambahkan!';
                        toast.classList.add('is-show');
                        setTimeout(function() {
                            toast.classList.remove('is-show');
                        }, 3000);
                    }
                    // Arahkan kembali ke katalog
                    window.location.href = '/admin/buku';
                } else {
                    alert('Gagal menyimpan: ' + (res.error || 'Periksa kembali inputan Anda.'));
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = originalText;
                alert('Terjadi kesalahan jaringan sistem. Silakan coba kembali.');
            });
    }
</script>