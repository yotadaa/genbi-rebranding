<?php
$categories = [
    'PSDM (Pengembangan Sumber Daya Manusia)',
    'Divisi Kewirausahaan',
    'Divisi Lingkungan Hidup',
    'Divisi Pendidikan dan Kebudayaan',
    'Divisi Pengabdian Masyarakat',
    'Divisi Publikasi dan Sosial'
];

$dummyData = [
    ['id' => 1, 'date' => '2026-08-01', 'desc' => 'Dana Kegiatan GenBI Mengajar', 'category' => 'Divisi Pendidikan dan Kebudayaan', 'type' => 'out', 'amount' => 450000],
    ['id' => 2, 'date' => '2026-08-05', 'desc' => 'Uang Kas Masuk', 'category' => 'PSDM (Pengembangan Sumber Daya Manusia)', 'type' => 'in', 'amount' => 1000000],
    ['id' => 3, 'date' => '2026-08-07', 'desc' => 'Pembelian Bibit Pohon', 'category' => 'Divisi Lingkungan Hidup', 'type' => 'out', 'amount' => 350000],
    ['id' => 4, 'date' => '2026-08-09', 'desc' => 'Sponsorship Kewirausahaan', 'category' => 'Divisi Kewirausahaan', 'type' => 'in', 'amount' => 500000],
    ['id' => 5, 'date' => '2026-08-11', 'desc' => 'Bantuan Bencana Alam', 'category' => 'Divisi Pengabdian Masyarakat', 'type' => 'out', 'amount' => 750000],
    ['id' => 6, 'date' => '2026-08-12', 'desc' => 'Cetak Banner & Pamflet', 'category' => 'Divisi Publikasi dan Sosial', 'type' => 'out', 'amount' => 200000],
    ['id' => 7, 'date' => '2026-08-14', 'desc' => 'Hasil Penjualan Merchandise', 'category' => 'Divisi Kewirausahaan', 'type' => 'in', 'amount' => 300000],
];
?>

<div class="site-container py-8" data-dashboard="true" data-transactions='<?= htmlspecialchars(json_encode($dummyData), ENT_QUOTES, 'UTF-8') ?>'>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Keuangan KOMSAT UNJA</h1>
        <p class="text-slate-500 mt-2">Dashboard pantauan arus kas dan pengeluaran per divisi untuk Universitas Jambi.</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-8 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <button class="category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100" data-cat="Semua">Semua Divisi</button>
            <?php foreach ($categories as $cat): ?>
                <button class="category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100" data-cat="<?= $cat ?>"><?= $cat ?></button>
            <?php endforeach; ?>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Pemasukan</p>
                    <h3 class="text-2xl font-bold text-slate-900" id="total-in">Rp 0</h3>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Pengeluaran</p>
                    <h3 class="text-2xl font-bold text-slate-900" id="total-out">Rp 0</h3>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Saldo Saat Ini</p>
                    <h3 class="text-2xl font-bold text-slate-900" id="total-balance">Rp 0</h3>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Grafik Arus Kas (Bulan Ini)</h3>
        <div class="relative h-72 w-full">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h3>
            <div class="relative w-full md:w-72">
                        <input type="text" id="search-input" placeholder="Cari transaksi..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-900 font-semibold">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200" id="table-body">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
            <div class="text-sm text-slate-600">
                Halaman <span class="font-medium text-slate-900" id="page-indicator">1 dari 1</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-1.5 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Sebelumnya
                </button>
                <button id="next-page" class="px-3 py-1.5 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>

</div>

</div>
