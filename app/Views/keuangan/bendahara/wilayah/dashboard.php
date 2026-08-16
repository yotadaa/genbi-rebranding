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

function formatRp($num)
{
    return 'Rp ' . number_format($num, 0, ',', '.');
}
?>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-10 md:mb-14">
        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">BENDAHARA OVERVIEW</div>
        <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight">Dashboard keuangan GenBI.</h1>
        <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Ringkasan arus kas keuangan GenBI Provinsi Jambi saat ini. Pantau seluruh transaksi masuk dan keluar dari satu tempat tanpa hambatan.</p>
    </div>

    <!-- Hidden Data for Chart/Preview -->
    <div id="trx-data" data-transactions='<?= json_encode($dummyData) ?>' class="hidden"></div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all flex flex-col">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">TOTAL PEMASUKAN</div>
            <h3 class="text-3xl lg:text-[2rem] font-serif-title font-medium text-slate-800 mb-6 truncate" id="summary-masuk"><?= formatRp($masuk) ?></h3>
            <a href="/keuangan/bendahara/wilayah/transaksi?type=in" class="mt-auto text-[13px] font-semibold text-blue-700 hover:text-blue-900 inline-flex items-center group">Buka Transaksi Masuk <span class="ml-1.5 group-hover:translate-x-1 transition-transform">&rarr;</span></a>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all flex flex-col">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">TOTAL PENGELUARAN</div>
            <h3 class="text-3xl lg:text-[2rem] font-serif-title font-medium text-slate-800 mb-6 truncate" id="summary-keluar"><?= formatRp($keluar) ?></h3>
            <a href="/keuangan/bendahara/wilayah/transaksi?type=out" class="mt-auto text-[13px] font-semibold text-blue-700 hover:text-blue-900 inline-flex items-center group">Buka Transaksi Keluar <span class="ml-1.5 group-hover:translate-x-1 transition-transform">&rarr;</span></a>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all flex flex-col">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">SALDO TERKINI</div>
            <h3 class="text-3xl lg:text-[2rem] font-serif-title font-medium text-slate-800 mb-6 truncate" id="summary-saldo"><?= formatRp($saldo) ?></h3>
            <span class="mt-auto text-[13px] font-semibold text-slate-400 inline-flex items-center">Total Keseluruhan Kas</span>
        </div>
    </div>

    <!-- Chart & Table Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- Chart -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-8 flex flex-col">
            <h3 class="text-[1.35rem] font-serif-title font-medium text-slate-800 mb-6">Grafik Arus Kas</h3>
            <div class="flex-1 w-full min-h-[300px] relative">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 flex flex-col overflow-hidden p-8">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-[1.35rem] font-serif-title font-medium text-slate-800">5 Transaksi Terakhir</h3>
                <a href="/keuangan/bendahara/wilayah/transaksi" class="text-[13px] font-semibold text-blue-700 hover:text-blue-900 group">Lihat Semua <span class="inline-block ml-1 group-hover:translate-x-1 transition-transform">&rarr;</span></a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100/80">
                            <th class="pb-4 pr-6 font-bold">Tanggal</th>
                            <th class="pb-4 pr-6 font-bold">Keterangan</th>
                            <th class="pb-4 pr-6 font-bold">Alokasi Dana</th>
                            <th class="pb-4 pr-6 font-bold text-right">Nominal</th>
                            <th class="pb-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-[13px]" id="table-body">
                        <!-- JS will populate top 5 here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>