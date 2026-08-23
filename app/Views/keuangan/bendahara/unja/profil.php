<?php

/**
 * @var array $user
 * @var array $profil
 */

use App\Core\Session;

$errors = Session::getFlash('errors') ?? [];
$old = Session::getFlash('old') ?? [];

// Helper untuk mengisi value
$val = function ($field, $isUserTable = false) use ($old, $profil, $user) {
    if (isset($old[$field])) {
        return htmlspecialchars((string)$old[$field]);
    }
    if ($isUserTable) {
        return htmlspecialchars((string)($user[$field] ?? ''));
    }
    return htmlspecialchars((string)($profil[$field] ?? ''));
};

$hasError = function ($field) use ($errors) {
    return isset($errors[$field]);
};

$errorClass = 'border-rose-500 focus:border-rose-500 focus:ring-rose-500 bg-rose-50/50';
$normalClass = 'border-slate-200/60 focus:bg-white focus:border-orange-500 focus:ring-orange-500 bg-slate-50/50';
?>

<div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-3">PENGATURAN</div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight">Profil Bendahara.</h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Lengkapi data diri Anda untuk keperluan pelaporan dan pencatatan transaksi keuangan.</p>
        </div>
    </div>

    <!-- Error Alert Global -->
    <?php if ($globalError = Session::getFlash('error')): ?>
        <div class="bg-rose-50 text-rose-700 border border-rose-200/60 rounded-2xl p-5 flex items-start gap-4 mb-8">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shrink-0 border border-rose-100 shadow-sm text-rose-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-[14px] font-bold text-rose-800 mb-1">Pemberitahuan</h4>
                <p class="text-[13px] font-medium text-rose-600/90 leading-relaxed"><?= htmlspecialchars($globalError) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <div class="border-b border-slate-100/80 bg-white px-8 py-6">
            <h2 class="text-xl font-serif-title font-medium text-slate-800">Informasi Pribadi</h2>
            <p class="text-[13px] text-slate-500 mt-2">Informasi di bawah ini digunakan pada sistem laporan dan pencatatan transaksi.</p>
        </div>

        <form action="/keuangan/bendahara/unja/profil" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\App\Services\CsrfService::token()) ?>">

            <div class="p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <!-- Nama Lengkap -->
                    <div class="md:col-span-2">
                        <label for="nama_bendahara" class="block text-[13px] font-bold text-slate-700 mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_bendahara" name="nama_bendahara" value="<?= $val('nama_bendahara') ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('nama_bendahara') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: John Doe">
                        <?php if ($hasError('nama_bendahara')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['nama_bendahara']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-[13px] font-bold text-slate-700 mb-2">Email <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="<?= $val('email', true) ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('email') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: johndoe@example.com">
                        <?php if ($hasError('email')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tahun Periode Awal -->
                    <div>
                        <label for="tahun_periode_awal" class="block text-[13px] font-bold text-slate-700 mb-2">Tahun Periode Awal <span class="text-rose-500">*</span></label>
                        <input type="number" id="tahun_periode_awal" name="tahun_periode_awal" value="<?= $val('tahun_periode_awal') ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('tahun_periode_awal') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: 2023">
                        <?php if ($hasError('tahun_periode_awal')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['tahun_periode_awal']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tahun Periode Akhir -->
                    <div>
                        <label for="tahun_periode_akhir" class="block text-[13px] font-bold text-slate-700 mb-2">Tahun Periode Akhir <span class="text-rose-500">*</span></label>
                        <input type="number" id="tahun_periode_akhir" name="tahun_periode_akhir" value="<?= $val('tahun_periode_akhir') ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('tahun_periode_akhir') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: 2024">
                        <?php if ($hasError('tahun_periode_akhir')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['tahun_periode_akhir']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="jenis_kelamin" class="block text-[13px] font-bold text-slate-700 mb-2">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="w-full px-5 py-3.5 border <?= $hasError('jenis_kelamin') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all focus:ring-2 cursor-pointer">
                            <option value="" disabled <?= $val('jenis_kelamin') === '' ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?= $val('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $val('jenis_kelamin') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php if ($hasError('jenis_kelamin')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['jenis_kelamin']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Universitas -->
                    <div>
                        <label for="universitas" class="block text-[13px] font-bold text-slate-700 mb-2">Asal Universitas <span class="text-rose-500">*</span></label>
                        <select id="universitas" name="universitas" class="w-full px-5 py-3.5 border <?= $hasError('universitas') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all focus:ring-2 cursor-pointer">
                            <option value="" disabled <?= $val('universitas') === '' ? 'selected' : '' ?>>Pilih Universitas</option>
                            <option value="UNJA" <?= $val('universitas') === 'UNJA' ? 'selected' : '' ?>>Universitas Jambi (UNJA)</option>
                            <option value="UIN STS" <?= $val('universitas') === 'UIN STS' ? 'selected' : '' ?>>UIN Sulthan Thaha Saifuddin (UIN STS)</option>
                            <option value="Lainnya" <?= $val('universitas') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                        <?php if ($hasError('universitas')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['universitas']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Program Studi -->
                    <div>
                        <label for="program_studi" class="block text-[13px] font-bold text-slate-700 mb-2">Program Studi <span class="text-rose-500">*</span></label>
                        <input type="text" id="program_studi" name="program_studi" value="<?= $val('program_studi') ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('program_studi') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: Sistem Informasi">
                        <?php if ($hasError('program_studi')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['program_studi']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Semester Studi -->
                    <div>
                        <label for="semester_studi" class="block text-[13px] font-bold text-slate-700 mb-2">Semester Studi <span class="text-rose-500">*</span></label>
                        <input type="number" id="semester_studi" name="semester_studi" value="<?= $val('semester_studi') ?>"
                            class="w-full px-5 py-3.5 border <?= $hasError('semester_studi') ? $errorClass : $normalClass ?> rounded-xl text-[14px] outline-none transition-all placeholder-slate-400 focus:ring-2"
                            placeholder="Contoh: 5">
                        <?php if ($hasError('semester_studi')): ?>
                            <p class="mt-2 text-[12px] font-medium text-rose-500 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg><?= htmlspecialchars($errors['semester_studi']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tempat (Disabled) -->
                    <div class="md:col-span-2">
                        <label for="tempat" class="block text-[13px] font-bold text-slate-700 mb-2">Tingkat Penugasan</label>
                        <input type="text" id="tempat" value="Komsat UNJA" disabled
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200/60 text-slate-500 rounded-xl text-[14px] outline-none cursor-not-allowed opacity-80">
                        <p class="mt-2 text-[12px] font-medium text-slate-500">Nilai ini tidak dapat diubah karena terikat dengan hak akses Anda sebagai Bendahara Komsat UNJA.</p>
                    </div>
                </div>

                <div class="pt-8 mt-8 border-t border-slate-100 flex justify-end gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white text-[13px] font-bold rounded-xl shadow-lg shadow-orange-600/20 transition-all focus:ring-4 focus:ring-orange-600/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>