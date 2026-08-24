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
                document.getElementById('m-alokasi').textContent = this.dataset.alokasi || '-';
                document.getElementById('m-tipe').textContent = this.dataset.tipe.toUpperCase();
                document.getElementById('m-nominal').textContent = this.dataset.nominal;
                document.getElementById('m-tanggal').textContent = this.dataset.tanggal;
                document.getElementById('m-sumber-dana').textContent = this.dataset.sumberdana || '-';
                document.getElementById('m-keterangan').textContent = this.dataset.keterangan || '-';
                document.getElementById('m-dicatat').textContent = this.dataset.dicatat || '-';
                document.getElementById('m-periode').textContent = this.dataset.periode || '-';

                // Bukti Transaksi
                const linkBukti = document.getElementById('m-bukti-link');
                const noBukti = document.getElementById('m-no-bukti');
                if (this.dataset.bukti && this.dataset.bukti !== '-') {
                    const isLink = this.dataset.bukti.startsWith('http://') || this.dataset.bukti.startsWith('https://');
                    linkBukti.href = isLink ? this.dataset.bukti : '/uploads/keuangan/' + this.dataset.bukti;
                    linkBukti.classList.remove('hidden'); linkBukti.classList.add('inline-flex');
                    noBukti.classList.add('hidden');
                } else {
                    linkBukti.href = '#';
                    linkBukti.classList.add('hidden'); linkBukti.classList.remove('inline-flex');
                    noBukti.classList.remove('hidden');
                }

                // Show modal
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });
    });
</script>

<div class="max-w-7xl mx-auto relative pb-12">
    <!-- Header & Filter -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-3">PEMANTAUAN KEUANGAN KANCAH</div>
            <h1 class="text-4xl md:text-[2.75rem] font-serif-title font-medium text-slate-900 tracking-tight leading-tight"><?= htmlspecialchars($komsatName) ?>.</h1>
            <p class="text-[15px] text-slate-500 mt-4 max-w-2xl leading-relaxed">Pemantauan transaksi dan aliran dana dari <?= htmlspecialchars($komsatName) ?>.</p>
        </div>
        <form action="" method="GET" class="flex items-center gap-3">
            <label for="divisiFilter" class="text-[11px] font-bold text-slate-500 uppercase tracking-widest hidden sm:block">Filter Divisi</label>
            <select name="divisi" id="divisiFilter" class="px-5 py-3.5 bg-white border border-slate-200/60 rounded-xl text-[13px] font-medium outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900 transition-all shadow-sm cursor-pointer min-w-[200px]">
                <?php foreach ($divisions as $div): ?>
                    <option value="<?= htmlspecialchars($div) ?>" <?= $selectedDivisi === $div ? 'selected' : '' ?>>
                        <?= htmlspecialchars($div) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 flex items-center gap-5 relative overflow-hidden group">
            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-emerald-50/50 to-transparent pointer-events-none transition-transform group-hover:scale-110"></div>
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0 border border-emerald-100/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Total Pemasukan</div>
                <div class="text-2xl md:text-3xl font-serif-title font-medium text-slate-900"><?= $formatRupiah($totalPemasukan) ?></div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 flex items-center gap-5 relative overflow-hidden group">
            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-rose-50/50 to-transparent pointer-events-none transition-transform group-hover:scale-110"></div>
            <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center shrink-0 border border-rose-100/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4"></path>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Total Pengeluaran</div>
                <div class="text-2xl md:text-3xl font-serif-title font-medium text-slate-900"><?= $formatRupiah($totalPengeluaran) ?></div>
            </div>
        </div>

        <div class="bg-[#3b5998] rounded-3xl p-8 shadow-xl shadow-[#3b5998]/20 text-white flex items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center shrink-0 backdrop-blur-sm border border-white/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-1.5">Total Saldo (Filter)</div>
                <div class="text-2xl md:text-3xl font-serif-title font-medium text-white"><?= $formatRupiah($saldo) ?></div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-8 mb-10">
        <h2 class="text-lg font-bold text-slate-900 mb-8 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-[#3b5998]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
            </div>
            Grafik Pemasukan & Pengeluaran
        </h2>

        <?php if (empty(json_decode($chartLabels, true))): ?>
            <div class="h-64 flex flex-col items-center justify-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                <svg class="w-10 h-10 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-[13px] font-medium">Belum ada data transaksi untuk filter ini.</span>
            </div>
        <?php else: ?>
            <div class="relative h-[300px] w-full">
                <canvas id="financeChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100/80 bg-white">
            <h2 class="text-lg font-bold text-slate-900">Riwayat Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Nama Kegiatan</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Tipe</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Keterangan Transaksi</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Sumber Dana</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    <?php if (empty($transaksiList)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-[13px] font-medium text-slate-500 mb-1">Tidak ada transaksi ditemukan.</p>
                                    <p class="text-[12px] text-slate-400">Coba ubah filter divisi untuk melihat data lain.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksiList as $t): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="text-[14px] font-bold text-slate-900 group-hover:text-[#3b5998] transition-colors"><?= htmlspecialchars($t['nama_kegiatan'] ?? '-') ?></div>
                                    <div class="text-[12px] text-slate-500 mt-1"><?= htmlspecialchars($t['divisi'] ?? '-') ?></div>
                                </td>
                                <td class="px-8 py-5">
                                    <?php if ($t['tipe_transaksi'] === 'pemasukan'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Pemasukan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-100/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Pengeluaran
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-5 text-[13px] font-medium text-slate-600 max-w-[200px] truncate" title="<?= htmlspecialchars($t['keterangan_transaksi'] ?? '') ?>">
                                    <?= htmlspecialchars($t['keterangan_transaksi'] ?? '-') ?>
                                </td>
                                <td class="px-8 py-5 text-[13px] font-medium text-slate-600 max-w-[150px] truncate" title="<?= htmlspecialchars($t['sumber_dana'] ?? '') ?>">
                                    <?= htmlspecialchars($t['sumber_dana'] ?? '-') ?>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button type="button"
                                        class="btn-detail inline-flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-600 hover:bg-[#3b5998] hover:text-white rounded-xl text-[12px] font-semibold transition-all"
                                        data-kegiatan="<?= htmlspecialchars($t['nama_kegiatan'] ?? '') ?>"
                                        data-divisi="<?= htmlspecialchars($t['divisi'] ?? '') ?>"
                                        data-alokasi="<?= htmlspecialchars($t['alokasi_dana'] ?? '') ?>"
                                        data-tipe="<?= htmlspecialchars($t['tipe_transaksi']) ?>"
                                        data-nominal="<?= $formatRupiah((float) $t['nominal']) ?>"
                                        data-tanggal="<?= date('d M Y', strtotime($t['tanggal_transaksi'])) ?>"
                                        data-sumberdana="<?= htmlspecialchars($t['sumber_dana'] ?? '') ?>"
                                        data-keterangan="<?= htmlspecialchars($t['keterangan_transaksi'] ?? '') ?>"
                                        data-dicatat="<?= htmlspecialchars($t['dicatat_oleh'] ?? '') ?>"
                                        data-periode="<?= htmlspecialchars($t['periode_kepengurusan'] ?? '') ?>"
                                        data-bukti="<?= htmlspecialchars($t['bukti_transaksi'] ?? '') ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
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
<div id="detailModal" class="fixed inset-0 z-[100] hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] scale-95 opacity-0 transition-all duration-200" id="detailModalContent">

        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
            <h3 class="text-xl font-serif-title font-medium text-slate-900">Detail Transaksi.</h3>
            <button type="button" id="closeModal" class="text-slate-400 hover:text-rose-500 bg-slate-50 hover:bg-rose-50 rounded-full p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-8 overflow-y-auto flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-8">

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Kegiatan</div>
                    <div id="m-nama-kegiatan" class="text-[14px] font-bold text-slate-900"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Divisi</div>
                    <div id="m-divisi" class="text-[14px] font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alokasi Dana</div>
                    <div id="m-alokasi" class="text-[14px] font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Transaksi</div>
                    <div id="m-tanggal" class="text-[14px] font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe Transaksi</div>
                    <div id="m-tipe" class="text-[14px] font-medium text-slate-900"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nominal</div>
                    <div id="m-nominal" class="text-2xl font-serif-title font-medium text-[#3b5998]"></div>
                </div>

                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Sumber Dana</div>
                    <div id="m-sumber-dana" class="text-[14px] font-medium text-slate-900"></div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Keterangan Transaksi</div>
                    <div id="m-keterangan" class="text-[13px] text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100/80 leading-relaxed"></div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Bukti Transaksi</div>
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center justify-center min-h-[200px]">
                        <a id="m-bukti-link" href="#" target="_blank" class="hidden items-center gap-2 px-6 py-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors font-medium text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Lihat Bukti Transaksi
                        </a>
                        <div id="m-no-bukti" class="text-slate-400 text-[13px] flex flex-col items-center gap-3">
                            <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Tidak ada foto bukti yang diunggah
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-[12px] text-slate-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Dicatat oleh: <span id="m-dicatat" class="font-bold text-slate-700"></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Periode: <span id="m-periode" class="font-bold text-slate-700"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animasi Modal */
    #detailModal:not(.hidden) #detailModalContent {
        transform: scale(1);
        opacity: 1;
    }
</style>