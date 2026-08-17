<?php

/** @var array $dummyData */
/** @var callable $e */
$categories = ['BPI Komsat UIN', 'Kewirausahaan', 'Lingkungan Hidup', 'Pendidikan dan Kebudayaan', 'Pengabdian Masyarakat', 'Pengembangan Sumber Daya Manusia', 'Publikasi dan Sosial'];
?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-10 md:mb-14 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">MANAJEMEN KAS</div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight">Transaksi Komsat UIN.</h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Kelola seluruh catatan arus kas GenBI Komsat UIN. Tambah, perbarui, atau pantau detail setiap transaksi.</p>
        </div>
        <a href="/keuangan/bendahara/uin/transaksi/tambah" id="btn-add-trx" class="inline-flex items-center justify-center px-6 py-3 bg-[#0ea5e9] text-white rounded-xl text-[13px] font-semibold hover:bg-[#0284c7] hover:shadow-lg hover:shadow-[#0ea5e9]/30 transition-all shadow-md group mt-2">
            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Buat Transaksi
        </a>
    </div>

    <!-- Hidden Data -->
    <div id="trx-data" data-transactions='<?= json_encode($dummyData) ?>' class="hidden"></div>
    <div id="trx-categories" data-categories='<?= json_encode($categories) ?>' class="hidden"></div>

    <!-- Filter & Search -->
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 mb-8 flex flex-col md:flex-row gap-6 items-center justify-between">
        <div class="flex flex-wrap gap-2 w-full md:w-auto" id="filter-container">
            <button class="category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all bg-[#0ea5e9] text-white shadow-md" data-cat="Semua">Semua Alokasi</button>
            <?php foreach ($categories as $cat): ?>
                <button class="category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100" data-cat="<?= $cat ?>"><?= $cat ?></button>
            <?php endforeach; ?>
        </div>
        <div class="w-full md:w-80 relative">
            <input type="text" id="search-input" placeholder="Cari keterangan transaksi..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200/60 rounded-xl text-[13px] focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400">
            <svg class="w-4 h-4 text-slate-400 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <div class="overflow-x-auto p-2">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100/80">
                        <th class="px-6 pb-4 pt-6 font-bold">Tanggal</th>
                        <th class="px-6 pb-4 pt-6 font-bold">Keterangan</th>
                        <th class="px-6 pb-4 pt-6 font-bold">Alokasi Dana</th>
                        <th class="px-6 pb-4 pt-6 font-bold text-right">Nominal</th>
                        <th class="px-6 pb-4 pt-6 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-[13px]" id="table-body">
                    <!-- Rows rendered by JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-8 py-5 border-t border-slate-100/80 flex items-center justify-between bg-white">
            <span class="text-[13px] font-medium text-slate-500" id="page-info">Halaman 1 dari 1</span>
            <div class="flex items-center gap-2">
                <button id="btn-prev" class="p-2 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-slate-50 border border-transparent hover:border-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="btn-next" class="p-2 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-slate-50 border border-transparent hover:border-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>