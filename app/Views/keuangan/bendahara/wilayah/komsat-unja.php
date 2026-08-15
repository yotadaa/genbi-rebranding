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
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financeChart');
        if (ctx) {
            new window.Chart(ctx, {
                type: 'bar',
                data: {
                    // PERBAIKAN DI SINI
                    labels: <?= $chartLabels ?>,
                    datasets: [{
                            label: 'Pemasukan',
                            // PERBAIKAN DI SINI
                            data: <?= $chartPemasukan ?>,
                            backgroundColor: '#10b981', // emerald-500
                            borderRadius: 4
                        },
                        {
                            label: 'Pengeluaran',
                            // PERBAIKAN DI SINI
                            data: <?= $chartPengeluaran ?>,
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
                                    return 'Rp ' + new window.Intl.NumberFormat('id-ID').format(value);
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
                                        label += 'Rp ' + new window.Intl.NumberFormat('id-ID').format(context.parsed.y);
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

    // Modal Logic
    const modal = document.getElementById('detailModal');
    const btnClose = document.getElementById('closeModal');
    
    // Close modal function
    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    btnClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            // Populate modal data
            document.getElementById('m-nama-kegiatan').textContent = this.dataset.kegiatan || '-';
            document.getElementById('m-divisi').textContent = this.dataset.divisi || '-';
            document.getElementById('m-tipe').textContent = this.dataset.tipe.toUpperCase();
            document.getElementById('m-nominal').textContent = this.dataset.nominal;
            document.getElementById('m-tanggal').textContent = this.dataset.tanggal;
            document.getElementById('m-alokasi').textContent = this.dataset.alokasi || '-';
            document.getElementById('m-sumber-dana').textContent = this.dataset.sumberdana || '-';
            document.getElementById('m-sumber-penerima').textContent = this.dataset.sumberpenerima || '-';
            document.getElementById('m-keterangan').textContent = this.dataset.keterangan || '-';
            document.getElementById('m-dicatat').textContent = this.dataset.dicatat || '-';
            document.getElementById('m-periode').textContent = this.dataset.periode || '-';
            
            // Bukti Transaksi
            const imgBukti = document.getElementById('m-bukti-img');
            const noBukti = document.getElementById('m-no-bukti');
            if (this.dataset.bukti && this.dataset.bukti !== '-') {
                imgBukti.src = '/uploads/keuangan/' + this.dataset.bukti;
                imgBukti.classList.remove('hidden');
                noBukti.classList.add('hidden');
            } else {
                imgBukti.src = '';
                imgBukti.classList.add('hidden');
                noBukti.classList.remove('hidden');
            }
            
            // Show modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    });
});
</script>

<div class="max-w-7xl mx-auto relative">
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
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 mb-1">Total Pemasukan</div>
                <div class="text-2xl font-bold text-slate-900"><?= $formatRupiah($totalPemasukan) ?></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-rose-50 to-transparent pointer-events-none"></div>
            <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </div>
            <div>
                <div class="text-sm font-medium text-slate-500 mb-1">Total Pengeluaran</div>
                <div class="text-2xl font-bold text-slate-900"><?= $formatRupiah($totalPengeluaran) ?></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 shadow-md text-white flex items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center shrink-0 backdrop-blur-sm border border-white/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
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
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
            </svg>
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
                        <th class="px-6 py-4 font-semibold">Nama Kegiatan</th>
                        <th class="px-6 py-4 font-semibold">Tipe</th>
                        <th class="px-6 py-4 font-semibold">Alokasi Dana</th>
                        <th class="px-6 py-4 font-semibold">Sumber Penerima</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($transaksiList)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                                <p class="text-slate-500 font-medium">Tidak ada transaksi ditemukan.</p>
                                <p class="text-slate-400 text-sm mt-1">Coba ubah filter divisi untuk melihat data lain.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksiList as $t): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
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
                                <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($t['alokasi_dana'] ?? '') ?>">
                                    <?= htmlspecialchars($t['alokasi_dana'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($t['sumber_penerima_dana'] ?? '') ?>">
                                    <?= htmlspecialchars($t['sumber_penerima_dana'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" 
                                        class="btn-detail inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition-colors"
                                        data-kegiatan="<?= htmlspecialchars($t['nama_kegiatan'] ?? '') ?>"
                                        data-divisi="<?= htmlspecialchars($t['divisi'] ?? '') ?>"
                                        data-tipe="<?= htmlspecialchars($t['tipe_transaksi']) ?>"
                                        data-nominal="<?= $formatRupiah((float) $t['nominal']) ?>"
                                        data-tanggal="<?= date('d M Y', strtotime($t['tanggal_transaksi'])) ?>"
                                        data-alokasi="<?= htmlspecialchars($t['alokasi_dana'] ?? '') ?>"
                                        data-sumberdana="<?= htmlspecialchars($t['sumber_dana'] ?? '') ?>"
                                        data-sumberpenerima="<?= htmlspecialchars($t['sumber_penerima_dana'] ?? '') ?>"
                                        data-keterangan="<?= htmlspecialchars($t['keterangan_transaksi'] ?? '') ?>"
                                        data-dicatat="<?= htmlspecialchars($t['dicatat_oleh'] ?? '') ?>"
                                        data-periode="<?= htmlspecialchars($t['periode_kepengurusan'] ?? '') ?>"
                                        data-bukti="<?= htmlspecialchars($t['bukti_transaksi'] ?? '') ?>"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="fixed inset-0 z-[100] hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
            <h3 class="text-lg font-bold text-slate-800">Detail Transaksi</h3>
            <button type="button" id="closeModal" class="text-slate-400 hover:text-slate-600 bg-white hover:bg-slate-100 rounded-full p-1.5 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama Kegiatan</div>
                    <div id="m-nama-kegiatan" class="text-sm font-medium text-slate-900"></div>
                </div>
                
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Divisi</div>
                    <div id="m-divisi" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Transaksi</div>
                    <div id="m-tanggal" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Transaksi</div>
                    <div id="m-tipe" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nominal</div>
                    <div id="m-nominal" class="text-lg font-bold text-blue-600"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Sumber Dana</div>
                    <div id="m-sumber-dana" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Alokasi Dana</div>
                    <div id="m-alokasi" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Sumber Penerima Dana</div>
                    <div id="m-sumber-penerima" class="text-sm font-medium text-slate-900"></div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Keterangan Transaksi</div>
                    <div id="m-keterangan" class="text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100"></div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Bukti Transaksi</div>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 flex items-center justify-center min-h-[150px]">
                        <img id="m-bukti-img" src="" alt="Bukti Transaksi" class="max-h-[300px] object-contain rounded-lg hidden shadow-sm">
                        <div id="m-no-bukti" class="text-slate-400 text-sm flex flex-col items-center gap-2">
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Tidak ada foto bukti
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <div>Dicatat oleh: <span id="m-dicatat" class="font-medium text-slate-600"></span></div>
                <div>Periode: <span id="m-periode" class="font-medium text-slate-600"></span></div>
            </div>
        </div>
    </div>
</div>