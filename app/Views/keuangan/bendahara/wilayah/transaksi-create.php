<?php

/** @var callable $e */
/** @var array $kegiatanList */
$old = \App\Core\Session::getFlash('old') ?: [];
$error_fields = \App\Core\Session::getFlash('error_fields') ?: [];
$hasError = function ($field) use ($error_fields) {
    return in_array($field, $error_fields) ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/50' : 'border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
};
?>
<div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Transaksi Wilayah</h1>
            <p class="text-sm text-slate-500 mt-1">Isi formulir di bawah untuk mencatat transaksi baru.</p>
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
        <form action="/keuangan/bendahara/wilayah/transaksi/store" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $e(\App\Services\CsrfService::token()) ?>">

            <div class="p-6 md:p-8 space-y-6">
                <!-- Dropdown Kegiatan -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="kegiatan_id" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('kegiatan_id') ?>">
                        <option value="" disabled selected>-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $kegiatan): ?>
                            <option value="<?= $kegiatan['id'] ?>" <?= (isset($old['kegiatan_id']) && $old['kegiatan_id'] == $kegiatan['id']) ? 'selected' : '' ?>>
                                <?= $e($kegiatan['nama_kegiatan']) ?> (Penyelenggara: <?= $e($kegiatan['divisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <select name="tipe_transaksi" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('tipe_transaksi') ?>">
                            <option value="pengeluaran" <?= (isset($old['tipe_transaksi']) && $old['tipe_transaksi'] === 'pengeluaran') ? 'selected' : '' ?>>Pengeluaran</option>
                            <option value="pemasukan" <?= (isset($old['tipe_transaksi']) && $old['tipe_transaksi'] === 'pemasukan') ? 'selected' : '' ?>>Pemasukan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_transaksi" required value="<?= $e($old['tanggal_transaksi'] ?? date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('tanggal_transaksi') ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="text" id="nominal_display" required value="<?= $e($old['nominal'] ?? '') ?>" placeholder="Misal: 500.000" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('nominal') ?>">
                    <input type="hidden" name="nominal" id="nominal_actual" value="<?= $e($old['nominal'] ?? '') ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Keterangan Transaksi <span class="text-rose-500">*</span></label>
                    <textarea name="keterangan_transaksi" rows="3" required placeholder="Detail pengeluaran atau pemasukan..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all resize-none <?= $hasError('keterangan_transaksi') ?>"><?= $e($old['keterangan_transaksi'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Alokasi Dana <span class="text-rose-500">*</span></label>
                        <input type="text" name="alokasi_dana" list="alokasi_list" required value="<?= $e($old['alokasi_dana'] ?? '') ?>" placeholder="Pilih atau ketik kustom..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('alokasi_dana') ?>">
                        <datalist id="alokasi_list">
                            <option value="BPI Wilayah">
                            <option value="Tim IT dan Website">
                            <option value="Tim Media Wilayah">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Sumber Dana</label>
                        <input type="text" name="sumber_dana" value="<?= $e($old['sumber_dana'] ?? '') ?>" placeholder="Misal: Kas GenBI, Sponsorship, dll" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('sumber_dana') ?>">
                    </div>
                </div>

                <!-- Bukti Transaksi -->
                <div class="border-t border-slate-100 pt-6 mt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-4">Bukti Transaksi</label>

                    <div class="flex items-center gap-4 mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="input_mode" value="file" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500" <?= (isset($old['input_mode']) && $old['input_mode'] === 'link') ? '' : 'checked' ?>>
                            <span class="ml-2 text-sm text-slate-700 font-medium">Upload File</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="input_mode" value="link" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500" <?= (isset($old['input_mode']) && $old['input_mode'] === 'link') ? 'checked' : '' ?>>
                            <span class="ml-2 text-sm text-slate-700 font-medium">Link Google Drive</span>
                        </label>
                    </div>

                    <div id="file-input-group" class="<?= (isset($old['input_mode']) && $old['input_mode'] === 'link') ? 'hidden' : '' ?>">
                        <input type="file" name="bukti_file" accept=".pdf,image/*" class="w-full px-4 py-3 bg-slate-50 border rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer <?= $hasError('bukti_file') ?>">
                        <p class="text-xs text-slate-500 mt-2">Format yang didukung: JPG, PNG, PDF. Maksimal 5MB (Gambar) atau 10MB (PDF).</p>
                    </div>

                    <div id="link-input-group" class="<?= (isset($old['input_mode']) && $old['input_mode'] === 'link') ? '' : 'hidden' ?>">
                        <input type="url" name="bukti_link" value="<?= $e($old['bukti_link'] ?? '') ?>" placeholder="https://drive.google.com/..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none transition-all <?= $hasError('bukti_link') ?>">
                        <p class="text-xs text-slate-500 mt-2">Pastikan akses link Google Drive sudah diatur ke "Siapa saja yang memiliki link".</p>
                    </div>
                </div>

            </div>

            <div class="px-6 md:px-8 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
                    Simpan Transaksi
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
                } else {
                    fileGroup.classList.add('hidden');
                    linkGroup.classList.remove('hidden');
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
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
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