<?php

/** @var callable $e */
/** @var array $kegiatanList */
/** @var array $trx */
$isLink = !empty($trx['bukti_transaksi']) && filter_var($trx['bukti_transaksi'], FILTER_VALIDATE_URL);
$error_fields = \App\Core\Session::getFlash('error_fields') ?: [];
$hasError = function ($field) use ($error_fields) {
    return in_array($field, $error_fields) ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/50' : 'border-slate-200/60 focus:ring-2 focus:ring-slate-900 focus:border-slate-900';
};
?>
<div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-10 md:mb-14 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">PERBARUI DATA</div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight">Edit Transaksi.</h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Ubah rincian transaksi yang sudah tersimpan sebelumnya.</p>
        </div>
        <a href="/keuangan/bendahara/wilayah/transaksi" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-200/60 text-slate-700 rounded-xl text-[13px] font-semibold hover:bg-slate-50 hover:shadow-md transition-all shadow-sm mt-2">
            Kembali
        </a>
    </div>

    <!-- Error Messages -->
    <?php if ($msg = \App\Core\Session::getFlash('swal_error')): ?>
        <div class="mb-8 p-5 bg-rose-50/80 border border-rose-200 text-rose-700 rounded-2xl text-[13px] font-medium flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <form action="/keuangan/bendahara/wilayah/transaksi/update/<?= $trx['id'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">

            <div class="p-8 md:p-10 space-y-8">
                <!-- Jenis Pencatatan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 border border-slate-200/60 rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors group">
                        <input type="radio" name="jenis_entri" value="kegiatan" class="peer sr-only" <?= (!isset($trx['jenis_entri']) || $trx['jenis_entri'] === 'kegiatan') ? 'checked' : '' ?>>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:border-[6px] transition-all mr-4"></div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-900 mb-0.5">Terkait Kegiatan</div>
                            <div class="text-[11px] text-slate-500">Pencatatan untuk proker/kegiatan GenBI.</div>
                        </div>
                    </label>
                    <label class="relative flex items-center p-4 border border-slate-200/60 rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors group">
                        <input type="radio" name="jenis_entri" value="invoice" class="peer sr-only" <?= (isset($trx['jenis_entri']) && $trx['jenis_entri'] === 'invoice') ? 'checked' : '' ?>>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:border-[6px] transition-all mr-4"></div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-900 mb-0.5">Invoice / Operasional</div>
                            <div class="text-[11px] text-slate-500">Pembayaran layanan, AI, domain, dll.</div>
                        </div>
                    </label>
                </div>

                <!-- Dropdown Kegiatan -->
                <div id="kegiatan_wrapper">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Pilih Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="kegiatan_id" id="kegiatan_id" required class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('kegiatan_id') ?>">
                        <option value="" disabled>-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $kegiatan): ?>
                            <option value="<?= $kegiatan['id'] ?>" <?= ($trx['kegiatan_id'] == $kegiatan['id']) ? 'selected' : '' ?>>
                                <?= $e($kegiatan['nama_kegiatan']) ?> (Penyelenggara: <?= $e($kegiatan['divisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <select name="tipe_transaksi" required class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('tipe_transaksi') ?>">
                            <option value="pengeluaran" <?= ($trx['tipe_transaksi'] === 'pengeluaran') ? 'selected' : '' ?>>Pengeluaran</option>
                            <option value="pemasukan" <?= ($trx['tipe_transaksi'] === 'pemasukan') ? 'selected' : '' ?>>Pemasukan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_transaksi" required value="<?= $e($trx['tanggal_transaksi']) ?>" class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('tanggal_transaksi') ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="text" id="nominal_display" required value="<?= (float)$trx['nominal'] ?>" placeholder="Misal: 500.000" class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-base font-semibold text-slate-900 <?= $hasError('nominal') ?>">
                    <input type="hidden" name="nominal" id="nominal_actual" value="<?= (float)$trx['nominal'] ?>">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Keterangan Transaksi <span class="text-rose-500">*</span></label>
                    <textarea name="keterangan_transaksi" rows="3" required class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] resize-none <?= $hasError('keterangan_transaksi') ?>"><?= $e($trx['keterangan_transaksi']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Alokasi Dana <span class="text-rose-500">*</span></label>
                        <input type="text" name="alokasi_dana" list="alokasi_list" required value="<?= $e($trx['alokasi_dana'] ?? '') ?>" placeholder="Pilih atau ketik kustom..." class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('alokasi_dana') ?>">
                        <datalist id="alokasi_list">
                            <option value="BPI Wilayah">
                            <option value="Tim IT dan Website">
                            <option value="Tim Media Wilayah">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Sumber Dana</label>
                        <input type="text" name="sumber_dana" value="<?= $e($trx['sumber_dana'] ?? '') ?>" class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('sumber_dana') ?>">
                    </div>
                </div>

                <!-- Bukti Transaksi -->
                <div class="border-t border-slate-100/80 pt-8 mt-8">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Bukti Transaksi Baru (Opsional)</label>
                    <p class="text-[12px] text-slate-400 mb-5">Biarkan opsi ini jika Anda tidak ingin mengubah bukti transaksi yang lama.</p>

                    <div class="flex items-center gap-6 mb-5 flex-wrap">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" name="input_mode" value="keep" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900" checked>
                            <span class="ml-2.5 text-[13px] text-slate-700 font-semibold group-hover:text-slate-900 transition-colors">Jangan Ubah Bukti</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" name="input_mode" value="file" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900">
                            <span class="ml-2.5 text-[13px] text-slate-700 font-semibold group-hover:text-slate-900 transition-colors">Upload File Baru</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" name="input_mode" value="link" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900">
                            <span class="ml-2.5 text-[13px] text-slate-700 font-semibold group-hover:text-slate-900 transition-colors">Link Drive Baru</span>
                        </label>
                    </div>

                    <div id="file-input-group" class="hidden">
                        <input type="file" name="bukti_file" accept=".pdf,image/*" class="w-full px-4 py-3 bg-slate-50 border border-slate-200/60 rounded-xl text-[13px] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[12px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer <?= $hasError('bukti_file') ?>">
                        <p class="text-[11px] font-medium text-slate-400 mt-2">Format: JPG, PNG, PDF. Max: 5MB (Gambar), 10MB (PDF).</p>
                    </div>

                    <div id="link-input-group" class="hidden">
                        <input type="url" name="bukti_link" placeholder="https://drive.google.com/..." class="w-full px-5 py-3.5 bg-slate-50 border rounded-xl outline-none transition-all text-[13px] <?= $hasError('bukti_link') ?>">
                        <p class="text-[11px] font-medium text-slate-400 mt-2">Pastikan akses link Google Drive sudah diatur ke "Siapa saja yang memiliki link".</p>
                    </div>

                    <?php if ($trx['bukti_transaksi']): ?>
                        <div class="mt-6 p-5 bg-slate-50 border border-slate-200/60 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">BUKTI SAAT INI</p>
                                <?php if ($isLink): ?>
                                    <a href="<?= $e($trx['bukti_transaksi']) ?>" target="_blank" class="text-[13px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Buka Google Drive <span class="group-hover:translate-x-1 transition-transform inline-block">&rarr;</span>
                                    </a>
                                <?php else: ?>
                                    <a href="/uploads/keuangan/<?= $e($trx['bukti_transaksi']) ?>" target="_blank" class="text-[13px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Lihat File Tersimpan <span class="group-hover:translate-x-1 transition-transform inline-block">&rarr;</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="px-8 md:px-10 py-5 bg-slate-50/50 border-t border-slate-100/80 flex items-center justify-end">
                <button type="submit" class="px-8 py-3 bg-[#3b5998] text-white rounded-xl text-[13px] font-semibold hover:bg-[#2d4373] hover:shadow-lg hover:shadow-[#3b5998]/30 transition-all shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const radios = document.querySelectorAll('input[name="input_mode"]');
        const fileGroup = document.getElementById('file-input-group');
        const linkGroup = document.getElementById('link-input-group');

        radios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.value === 'file') {
                    fileGroup.classList.remove('hidden');
                    linkGroup.classList.add('hidden');
                } else if (e.target.value === 'link') {
                    fileGroup.classList.add('hidden');
                    linkGroup.classList.remove('hidden');
                } else {
                    fileGroup.classList.add('hidden');
                    linkGroup.classList.add('hidden');
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nominalDisplay = document.getElementById('nominal_display');
        const nominalActual = document.getElementById('nominal_actual');

        const formatRupiah = (angka) => {
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        };

        if (nominalDisplay.value) {
            nominalDisplay.value = formatRupiah(nominalDisplay.value);
        }

        nominalDisplay.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
            nominalActual.value = this.value.replace(/\./g, '');
        });

        const entriRadios = document.querySelectorAll('input[name="jenis_entri"]');
        const kegiatanWrapper = document.getElementById('kegiatan_wrapper');
        const kegiatanSelect = document.getElementById('kegiatan_id');

        const toggleKegiatan = () => {
            const selected = document.querySelector('input[name="jenis_entri"]:checked').value;
            if (selected === 'invoice') {
                kegiatanWrapper.style.display = 'none';
                kegiatanSelect.removeAttribute('required');
                kegiatanSelect.value = '';
            } else {
                kegiatanWrapper.style.display = 'block';
                kegiatanSelect.setAttribute('required', 'required');
            }
        };

        entriRadios.forEach(r => r.addEventListener('change', toggleKegiatan));
        toggleKegiatan(); // Run on load
    });
</script>