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

$errorClass = 'border-red-500 focus:ring-red-500 focus:border-red-500';
$normalClass = 'border-slate-200 focus:ring-blue-500 focus:border-blue-500';

$actionUrl = $isEdit 
    ? '/keuangan/bendahara/wilayah/kegiatan/edit/' . ($kegiatan['id'] ?? '') 
    : '/keuangan/bendahara/wilayah/kegiatan/tambah';
?>
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="/keuangan/bendahara/wilayah/kegiatan" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight"><?= $isEdit ? 'Edit Kegiatan Wilayah' : 'Tambah Kegiatan Baru' ?></h1>
        <p class="text-sm text-slate-500 mt-1">Isi formulir di bawah ini dengan lengkap dan benar.</p>
    </div>

    <!-- Error Alert -->
    <?php if (isset($errors['tingkat'])): ?>
    <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="text-sm font-medium"><?= htmlspecialchars($errors['tingkat']) ?></p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="<?= htmlspecialchars($actionUrl) ?>" method="POST" class="p-6 sm:p-8 space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Services\CsrfService::token()) ?>">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Nama Kegiatan -->
                <div class="sm:col-span-2">
                    <label for="nama_kegiatan" class="block text-sm font-medium text-slate-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="<?= $val('nama_kegiatan') ?>" class="w-full px-4 py-2 bg-slate-50 border <?= $hasError('nama_kegiatan') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all placeholder-slate-400" placeholder="Contoh: Rapat Kerja Wilayah">
                    <?php if ($hasError('nama_kegiatan')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= htmlspecialchars($errors['nama_kegiatan']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label for="divisi" class="block text-sm font-medium text-slate-700 mb-1">Penyelenggara / Kategori</label>
                    <input type="text" id="divisi" name="divisi" value="<?= $val('divisi') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl outline-none transition-all placeholder-slate-400" placeholder="Contoh: Pendidikan / Komsat UNJA">
                </div>

                <!-- Tingkat (Disabled / Restricted by Backend) -->
                <div>
                    <label for="tingkat" class="block text-sm font-medium text-slate-700 mb-1">Tingkat Kepengurusan <span class="text-red-500">*</span></label>
                    <select id="tingkat" name="tingkat" class="w-full px-4 py-2 bg-slate-50 border <?= $hasError('tingkat') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all">
                        <option value="wilayah" <?= $val('tingkat') === 'wilayah' ? 'selected' : '' ?>>Wilayah</option>
                        <option value="komsat unja" <?= $val('tingkat') === 'komsat unja' ? 'selected' : '' ?>>Komsat UNJA</option>
                        <option value="komsat uin" <?= $val('tingkat') === 'komsat uin' ? 'selected' : '' ?>>Komsat UIN</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Bendahara Wilayah hanya dapat memproses kegiatan tingkat Wilayah.</p>
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= $val('tanggal_mulai') ?>" class="w-full px-4 py-2 bg-slate-50 border <?= $hasError('tanggal_mulai') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-slate-700">
                    <?php if ($hasError('tanggal_mulai')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= htmlspecialchars($errors['tanggal_mulai']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai (Opsional)</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= $val('tanggal_selesai') ?>" class="w-full px-4 py-2 bg-slate-50 border <?= $hasError('tanggal_selesai') ? $errorClass : $normalClass ?> rounded-xl outline-none transition-all text-slate-700">
                    <?php if ($hasError('tanggal_selesai')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= htmlspecialchars($errors['tanggal_selesai']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Keterangan -->
                <div class="sm:col-span-2">
                    <label for="keterangan_kegiatan" class="block text-sm font-medium text-slate-700 mb-1">Keterangan / Deskripsi</label>
                    <textarea id="keterangan_kegiatan" name="keterangan_kegiatan" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl outline-none transition-all placeholder-slate-400" placeholder="Tambahkan catatan atau deskripsi kegiatan..."><?= $val('keterangan_kegiatan') ?></textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="/keuangan/bendahara/wilayah/kegiatan" class="px-5 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <?= $isEdit ? 'Simpan Perubahan' : 'Tambahkan Kegiatan' ?>
                </button>
            </div>
        </form>
    </div>
</div>
