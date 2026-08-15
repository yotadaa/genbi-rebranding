<?php
/** @var array $kegiatan */
use App\Core\Session;

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>
<div class="max-w-7xl mx-auto">
    
    <!-- Alerts -->
    <?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="text-sm font-medium"><?= htmlspecialchars($success) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <p class="text-sm font-medium"><?= htmlspecialchars($error) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Kegiatan Wilayah</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data kegiatan keuangan untuk tingkat wilayah.</p>
        </div>
        <a href="/keuangan/bendahara/wilayah/kegiatan/tambah" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kegiatan
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Nama Kegiatan</th>
                        <th class="px-6 py-4 font-semibold">Penyelenggara</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($kegiatan)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-sm">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            Belum ada data kegiatan.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($kegiatan as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($row['nama_kegiatan']) ?></p>
                                <?php if($row['keterangan_kegiatan']): ?>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1" title="<?= htmlspecialchars($row['keterangan_kegiatan']) ?>"><?= htmlspecialchars($row['keterangan_kegiatan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    <?= htmlspecialchars($row['divisi'] ?: 'Umum') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php if($row['tanggal_mulai']): ?>
                                    <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?>
                                    <?php if($row['tanggal_selesai'] && $row['tanggal_selesai'] !== $row['tanggal_mulai']): ?>
                                        <span class="text-slate-400 mx-1">-</span>
                                        <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="/keuangan/bendahara/wilayah/kegiatan/edit/<?= $row['id'] ?>" class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="/keuangan/bendahara/wilayah/kegiatan/hapus/<?= $row['id'] ?>" method="POST" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Services\CsrfService::token()) ?>">
                                        <button type="button" onclick="confirmDelete(this.closest('form'))" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
