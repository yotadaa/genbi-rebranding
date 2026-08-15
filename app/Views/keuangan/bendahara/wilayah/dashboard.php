<?php
/** @var array $dummyData */
/** @var callable $e */

// Calculate summary
$masuk = 0;
$keluar = 0;
foreach ($dummyData as $t) {
    if ($t['type'] === 'in') {
        $masuk += $t['amount'];
    } else {
        $keluar += $t['amount'];
    }
}
$saldo = $masuk - $keluar;

function formatRp($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
}
?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang, Bendahara Wilayah!</h1>
        <p class="text-sm text-slate-500 mt-1">Berikut adalah ringkasan keuangan GenBI Provinsi Jambi saat ini.</p>
    </div>

    <!-- Hidden Data for Chart/Preview -->
    <div id="trx-data" data-transactions='<?= json_encode($dummyData) ?>' class="hidden"></div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Uang Masuk</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1" id="summary-masuk"><?= formatRp($masuk) ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Uang Keluar</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1" id="summary-keluar"><?= formatRp($keluar) ?></h3>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 shadow-md shadow-blue-200 text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -left-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <div class="relative flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0 backdrop-blur-sm">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div>
                    <p class="text-blue-100 text-sm font-medium">Saldo Terkini</p>
                    <h3 class="text-2xl font-bold mt-1" id="summary-saldo"><?= formatRp($saldo) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Table Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
            <h3 class="text-base font-bold text-slate-900 mb-4">Grafik Arus Kas</h3>
            <div class="flex-1 w-full min-h-[300px] relative">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">5 Transaksi Terakhir</h3>
                <a href="/keuangan/bendahara/wilayah/transaksi" class="text-sm font-medium text-blue-600 hover:text-blue-800">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3 font-semibold">Tanggal</th>
                            <th class="px-6 py-3 font-semibold">Keterangan</th>
                            <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                            <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="table-body">
                        <!-- JS will populate top 5 here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
