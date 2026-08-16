<?php
/**
 * @var bool $isEdit
 * @var array $kegiatan
 */
use App\Core\Session;

$errors = Session::getFlash('errors') ?? [];
$old = Session::getFlash('old') ?? [];

// Helper untuk mengisi value
$val = function($field) use ($old, $kegiatan) {
    return htmlspecialchars((string)($old[$field] ?? $kegiatan[$field] ?? ''));
};

$hasError = function($field) use ($errors) {
    return isset($errors[$field]);
};

$errorClass = 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/50';
$normalClass = 'border-slate-200/60 focus:ring-2 focus:ring-slate-900 focus:border-slate-900';

$actionUrl = $isEdit 
    ? '/keuangan/bendahara/unja/kegiatan/edit/' . ($kegiatan['id'] ?? '') 
    : '/keuangan/bendahara/unja/kegiatan/tambah';
?>
<div class="max-w-3xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-10 md:mb-14 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-4"><?= $isEdit ? 'PERBARUI DATA' : 'TAMBAH DATA' ?></div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight"><?= $isEdit ? 'Edit Kegiatan.' : 'Kegiatan Baru.' ?></h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Isi formulir di bawah ini dengan lengkap dan benar untuk Komsat UNJA.</p>
        </div>
        <a href="/keuangan/bendahara/unja/kegiatan" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-200/60 text-slate-700 rounded-xl text-[13px] font-semibold hover:bg-slate-50 hover:shadow-md transition-all shadow-sm mt-2">
            Kembali
        </a>
    </div>

    <!-- Error Alert -->
    <?php if (isset($errors['tingkat'])): ?>
    <div class="mb-8 p-5 bg-rose-50/80 border border-rose-200 text-rose-700 rounded-2xl text-[13px] font-medium flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p><?= htmlspecialchars($errors['tingkat']) ?></p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <form action="<?= htmlspecialchars($actionUrl) ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Services\CsrfService::token()) ?>">
            
            <div class="p-8 md:p-10 space-y-8">
                <!-- Nama Kegiatan -->
                <div class="sm:col-span-2">
                    <label for="nama_kegiatan" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Nama Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="<?= $val('nama_kegiatan') ?>" class="w-full px-5 py-3.5 bg-slate-50 border <?= $hasError('nama_kegiatan') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-[13px] placeholder-slate-400" placeholder="Contoh: Sosialisasi Beasiswa BI">
                    <?php if ($hasError('nama_kegiatan')): ?>
                        <p class="mt-2 text-[11px] font-medium text-rose-500"><?= htmlspecialchars($errors['nama_kegiatan']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Penyelenggara -->
                    <div>
                        <label for="divisi" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Penyelenggara / Divisi</label>
                        <input type="text" id="divisi" name="divisi" value="<?= $val('divisi') ?>" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200/60 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 rounded-xl outline-none transition-all text-[13px] placeholder-slate-400" placeholder="Contoh: Divisi Pendidikan">
                    </div>

                    <!-- Tingkat (Disabled / Restricted by Backend) -->
                    <div>
                        <label for="tingkat" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Tingkat Kepengurusan <span class="text-rose-500">*</span></label>
                        <select id="tingkat" name="tingkat" class="w-full px-5 py-3.5 bg-slate-50 border <?= $hasError('tingkat') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-[13px]">
                            <option value="wilayah" <?= $val('tingkat') === 'wilayah' ? 'selected' : '' ?>>Wilayah</option>
                            <option value="unja" <?= ($val('tingkat') === 'unja' || !$isEdit) ? 'selected' : '' ?>>Komsat UNJA</option>
                            <option value="uin" <?= $val('tingkat') === 'uin' ? 'selected' : '' ?>>Komsat UIN</option>
                        </select>
                        <p class="mt-2 text-[11px] font-medium text-slate-400">Anda hanya dapat menambahkan kegiatan untuk tingkat Komsat UNJA.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="tanggal_mulai" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= $val('tanggal_mulai') ?>" class="w-full px-5 py-3.5 bg-slate-50 border <?= $hasError('tanggal_mulai') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-[13px] text-slate-700">
                        <?php if ($hasError('tanggal_mulai')): ?>
                            <p class="mt-2 text-[11px] font-medium text-rose-500"><?= htmlspecialchars($errors['tanggal_mulai']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="tanggal_selesai" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Tanggal Selesai (Opsional)</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= $val('tanggal_selesai') ?>" class="w-full px-5 py-3.5 bg-slate-50 border <?= $hasError('tanggal_selesai') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-[13px] text-slate-700">
                        <?php if ($hasError('tanggal_selesai')): ?>
                            <p class="mt-2 text-[11px] font-medium text-rose-500"><?= htmlspecialchars($errors['tanggal_selesai']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="sm:col-span-2">
                    <label for="keterangan_kegiatan" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Keterangan / Deskripsi</label>
                    <textarea id="keterangan_kegiatan" name="keterangan_kegiatan" rows="4" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200/60 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 rounded-xl outline-none transition-all text-[13px] placeholder-slate-400 resize-none" placeholder="Tambahkan catatan atau deskripsi kegiatan..."><?= $val('keterangan_kegiatan') ?></textarea>
                </div>
            </div>

            <div class="px-8 md:px-10 py-5 bg-slate-50/50 border-t border-slate-100/80 flex items-center justify-end">
                <button type="submit" class="px-8 py-3 bg-[#f97316] text-white rounded-xl text-[13px] font-semibold hover:bg-[#ea580c] hover:shadow-lg hover:shadow-[#f97316]/30 transition-all shadow-md">
                    <?= $isEdit ? 'Simpan Perubahan' : 'Tambahkan Kegiatan' ?>
                </button>
            </div>
        </form>
    </div>
</div>
