<?php

/** @var callable $e */
/** @var array $kegiatanList */
/** @var array $trx */
$isLink = !empty($trx['bukti_transaksi']) && filter_var($trx['bukti_transaksi'], FILTER_VALIDATE_URL);
$error_fields = \App\Core\Session::getFlash('error_fields') ?: [];
$hasError = function ($field) use ($error_fields) {
    return in_array($field, $error_fields) ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/50' : 'border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
};
?>
<div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Transaksi Wilayah</h1>
            <p class="text-sm text-slate-500 mt-1">Ubah data transaksi yang sudah tersimpan.</p>
        </div>
        <a href="/keuangan/bendahara/wilayah/transaksi" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
            Batal
        </a>
    </div>

    <!-- Error Messages -->
    <?php if ($msg = \App\Core\Session::getFlash('swal_error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-medium">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="/keuangan/bendahara/wilayah/transaksi/update/<?= $trx['id'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">

            <div class="p-6 md:p-8 space-y-6">
                <!-- Dropdown Kegiatan -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="kegiatan_id" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('kegiatan_id') ?>">
                        <option value="" disabled>-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $kegiatan): ?>
                            <option value="<?= $kegiatan['id'] ?>" <?= ($trx['kegiatan_id'] == $kegiatan['id']) ? 'selected' : '' ?>>
                                <?= $e($kegiatan['nama_kegiatan']) ?> (Penyelenggara: <?= $e($kegiatan['divisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <select name="tipe_transaksi" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('tipe_transaksi') ?>">
                            <option value="pengeluaran" <?= ($trx['tipe_transaksi'] === 'pengeluaran') ? 'selected' : '' ?>>Pengeluaran</option>
                            <option value="pemasukan" <?= ($trx['tipe_transaksi'] === 'pemasukan') ? 'selected' : '' ?>>Pemasukan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_transaksi" required value="<?= $e($trx['tanggal_transaksi']) ?>" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('tanggal_transaksi') ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="text" id="nominal_display" required value="<?= (float)$trx['nominal'] ?>" placeholder="Misal: 500.000" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('nominal') ?>">
                    <input type="hidden" name="nominal" id="nominal_actual" value="<?= (float)$trx['nominal'] ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Keterangan Transaksi <span class="text-rose-500">*</span></label>
                    <textarea name="keterangan_transaksi" rows="3" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all resize-none <?= $hasError('keterangan_transaksi') ?>"><?= $e($trx['keterangan_transaksi']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Alokasi Dana <span class="text-rose-500">*</span></label>
                        <input type="text" name="alokasi_dana" list="alokasi_list" required value="<?= $e($trx['alokasi_dana'] ?? '') ?>" placeholder="Pilih atau ketik kustom..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('alokasi_dana') ?>">
                        <datalist id="alokasi_list">
                            <option value="BPI Wilayah">
                            <option value="Tim IT dan Website">
                            <option value="Tim Media Wilayah">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Sumber Dana</label>
                        <input type="text" name="sumber_dana" value="<?= $e($trx['sumber_dana'] ?? '') ?>" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('sumber_dana') ?>">
                    </div>
                </div>

                <!-- Bukti Transaksi -->
                <div class="border-t border-slate-100 pt-6 mt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Bukti Transaksi Baru (Opsional)</label>
                    <p class="text-xs text-slate-500 mb-4">Biarkan opsi ini jika Anda tidak ingin mengubah bukti transaksi yang lama.</p>

                    <div class="flex items-center gap-4 mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="input_mode" value="keep" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-slate-700 font-medium">Jangan Ubah Bukti</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="input_mode" value="file" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-slate-700 font-medium">Upload File Baru</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="input_mode" value="link" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-slate-700 font-medium">Link Google Drive Baru</span>
                        </label>
                    </div>

                    <div id="file-input-group" class="hidden">
                        <input type="file" name="bukti_file" accept=".pdf,image/*" class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer <?= $hasError('bukti_file') ?>">
                        <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG, PDF. Max: 5MB (Gambar), 10MB (PDF).</p>
                    </div>

                    <div id="link-input-group" class="hidden">
                        <input type="url" name="bukti_link" placeholder="https://drive.google.com/..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('bukti_link') ?>">
                        <p class="text-xs text-slate-500 mt-2">Pastikan akses link Google Drive sudah diatur ke "Siapa saja yang memiliki link".</p>
                    </div>

                    <?php if ($trx['bukti_transaksi']): ?>
                        <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Bukti Saat Ini</p>
                                <?php if ($isLink): ?>
                                    <a href="<?= $e($trx['bukti_transaksi']) ?>" target="_blank" class="text-sm font-medium text-blue-600 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Buka Google Drive
                                    </a>
                                <?php else: ?>
                                    <a href="/uploads/keuangan/<?= $e($trx['bukti_transaksi']) ?>" target="_blank" class="text-sm font-medium text-blue-600 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Lihat File Tersimpan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="px-6 md:px-8 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
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
    });
</script>