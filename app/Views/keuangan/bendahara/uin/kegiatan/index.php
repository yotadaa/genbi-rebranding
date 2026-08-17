<?php
/** @var array $kegiatan */
use App\Core\Session;

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>
<div class="max-w-7xl mx-auto pb-12">
    
    <!-- Alerts -->
    <?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-2xl p-5 mb-8 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="text-[13px] font-medium"><?= htmlspecialchars($success) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 transition-colors p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-rose-50 text-rose-700 border border-rose-200 rounded-2xl p-5 mb-8 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <p class="text-[13px] font-medium"><?= htmlspecialchars($error) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 transition-colors p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-3">MANAJEMEN KEGIATAN</div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight">Daftar Kegiatan.</h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Kelola data kegiatan keuangan tingkat Komsat UIN GenBI Provinsi Jambi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/keuangan/bendahara/uin/kegiatan/tambah" class="group inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-[#f97316] text-white rounded-xl text-[13px] font-semibold hover:bg-[#ea580c] hover:shadow-lg hover:shadow-[#f97316]/30 transition-all shadow-md">
                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kegiatan
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 md:px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Nama Kegiatan</th>
                        <th class="px-6 md:px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Penyelenggara</th>
                        <th class="px-6 md:px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Tanggal</th>
                        <th class="px-6 md:px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    <?php if (empty($kegiatan)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-12 h-12 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-[13px] font-medium text-slate-500">Belum ada data kegiatan yang ditambahkan.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($kegiatan as $row): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 md:px-8 py-5">
                                <p class="text-[14px] font-bold text-slate-900 group-hover:text-[#f97316] transition-colors"><?= htmlspecialchars($row['nama_kegiatan']) ?></p>
                                <?php if($row['keterangan_kegiatan']): ?>
                                <p class="text-[12px] text-slate-500 mt-1.5 line-clamp-2 max-w-sm" title="<?= htmlspecialchars($row['keterangan_kegiatan']) ?>"><?= htmlspecialchars($row['keterangan_kegiatan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 md:px-8 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-semibold bg-sky-50 text-sky-600 border border-sky-100/50">
                                    <?= htmlspecialchars($row['divisi'] ?: 'Umum') ?>
                                </span>
                            </td>
                            <td class="px-6 md:px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center text-[13px] font-medium text-slate-700">
                                    <?php if($row['tanggal_mulai']): ?>
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?>
                                        <?php if($row['tanggal_selesai'] && $row['tanggal_selesai'] !== $row['tanggal_mulai']): ?>
                                            <span class="text-slate-400 mx-2">&rarr;</span>
                                            <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Belum diatur</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 md:px-8 py-5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="/keuangan/bendahara/uin/kegiatan/edit/<?= $row['id'] ?>" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="/keuangan/bendahara/uin/kegiatan/hapus/<?= $row['id'] ?>" method="POST" class="inline-block m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Services\CsrfService::token()) ?>">
                                        <button type="button" onclick="confirmDelete(this.closest('form'))" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all" title="Hapus Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
