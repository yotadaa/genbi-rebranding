<?php
/**
 * @var string $title
 * @var string $komsatName
 * @var array $divisions
 * @var string $selectedDivisi
 * @var array $transaksiList
 * @var float $totalPemasukan
 * @var float $totalPengeluaran
 * @var float $saldo
 * @var string $chartLabels
 * @var string $chartPemasukan
 * @var string $chartPengeluaran
 */

$formatRupiah = function (float $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
};

// Pastikan library Chart.js ada di layout atau dimuat di sini
$scripts = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financeChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {$chartLabels},
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: {$chartPemasukan},
                        backgroundColor: '#10b981', // emerald-500
                        borderRadius: 4
                    },
                    {
                        label: 'Pengeluaran',
                        data: {$chartPengeluaran},
                        backgroundColor: '#f43f5e', // rose-500
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Filter onChange submit
    const filterSelect = document.getElementById('divisiFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>
HTML;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight"><?= htmlspecialchars($komsatName) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Pemantauan transaksi dan aliran dana dari <?= htmlspecialchars($komsatName) ?>.</p>
        </div>
        <form action="" method="GET" class="flex items-center gap-2">
            <label for="divisiFilter" class="text-sm font-medium text-slate-700">Filter Divisi:</label>
            <select name="divisi" id="divisiFilter" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all shadow-sm cursor-pointer min-w-[150px]">
                <?php foreach ($divisions as $div): ?>
                    <option value="<?= htmlspecialchars($div) ?>" <?= $selectedDivisi === $div ? 'selected' : '' ?>>
                        <?= htmlspecialchars($div) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-emerald-50 to-transparent pointer-events-none"></div>
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 mb-1">Total Pemasukan</div>
                <div class="text-2xl font-bold text-slate-900"><?= $formatRupiah($totalPemasukan) ?></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-rose-50 to-transparent pointer-events-none"></div>
            <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 mb-1">Total Pengeluaran</div>
                <div class="text-2xl font-bold text-slate-900"><?= $formatRupiah($totalPengeluaran) ?></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 shadow-md text-white flex items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center shrink-0 backdrop-blur-sm border border-white/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-sm font-medium text-blue-100 mb-1">Total Saldo (Berdasarkan Filter)</div>
                <div class="text-2xl font-bold"><?= $formatRupiah($saldo) ?></div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <h2 class="text-base font-bold text-slate-800 mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            Grafik Pemasukan & Pengeluaran
        </h2>
        
        <?php if (empty(json_decode($chartLabels, true))): ?>
            <div class="h-64 flex items-center justify-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                Belum ada data transaksi yang sesuai dengan filter ini untuk divisualisasikan.
            </div>
        <?php else: ?>
            <div class="relative h-72 w-full">
                <canvas id="financeChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-800">Riwayat Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Kegiatan & Divisi</th>
                        <th class="px-6 py-4 font-semibold">Tipe</th>
                        <th class="px-6 py-4 font-semibold text-right">Nominal</th>
                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                        <th class="px-6 py-4 font-semibold">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($transaksiList)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <p class="text-slate-500 font-medium">Tidak ada transaksi ditemukan.</p>
                                <p class="text-slate-400 text-sm mt-1">Coba ubah filter divisi untuk melihat data lain.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksiList as $t): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-600">
                                    <?= date('d M Y', strtotime($t['tanggal_transaksi'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-900 font-medium"><?= htmlspecialchars($t['nama_kegiatan'] ?? '-') ?></div>
                                    <div class="text-slate-500 text-xs mt-0.5"><?= htmlspecialchars($t['divisi'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($t['tipe_transaksi'] === 'pemasukan'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Pemasukan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Pengeluaran
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right font-medium <?= $t['tipe_transaksi'] === 'pemasukan' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= $t['tipe_transaksi'] === 'pemasukan' ? '+' : '-' ?><?= $formatRupiah((float) $t['nominal']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($t['keterangan_transaksi'] ?? '') ?>">
                                    <?= htmlspecialchars($t['keterangan_transaksi'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">
                                    <?= htmlspecialchars($t['dicatat_oleh'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
