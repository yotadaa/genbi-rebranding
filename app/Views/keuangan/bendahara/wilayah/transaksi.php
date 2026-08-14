<?php
/** @var array $dummyData */
/** @var callable $e */
$categories = ['BPI', 'Tim IT', 'Tim Media Wilayah'];
?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Transaksi Wilayah</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data pemasukan dan pengeluaran kas GenBI Provinsi Jambi.</p>
        </div>
        <button type="button" id="btn-add-trx" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Transaksi
        </button>
    </div>

    <!-- Hidden Data -->
    <div id="trx-data" data-transactions='<?= json_encode($dummyData) ?>' class="hidden"></div>
    <div id="trx-categories" data-categories='<?= json_encode($categories) ?>' class="hidden"></div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-2" id="filter-container">
            <button class="category-btn px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-md shadow-blue-200" data-cat="Semua">Semua</button>
            <?php foreach ($categories as $cat): ?>
                <button class="category-btn px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100" data-cat="<?= $cat ?>"><?= $cat ?></button>
            <?php endforeach; ?>
        </div>
        <div class="w-full md:w-64 relative">
            <input type="text" id="search-input" placeholder="Cari keterangan..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                        <th class="px-6 py-4 font-semibold">Divisi</th>
                        <th class="px-6 py-4 font-semibold text-right">Nominal</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200" id="table-body">
                    <!-- Rows rendered by JS -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50/50">
            <span class="text-sm text-slate-500" id="page-info">Halaman 1 dari 1</span>
            <div class="flex items-center gap-2">
                <button id="btn-prev" class="p-2 rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button id="btn-next" class="p-2 rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>
