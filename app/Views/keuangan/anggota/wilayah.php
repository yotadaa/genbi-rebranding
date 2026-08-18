<?php
// Pastikan semua string ter-encode UTF-8 agar json_encode tidak gagal
$safeTransactions = array_map(function($t) {
    return array_map(function($v) {
        return is_string($v) ? mb_convert_encoding($v, 'UTF-8', 'auto') : $v;
    }, $t);
}, $transactions ?? []);

$transactionsJson = json_encode($safeTransactions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
?>
<script id="tx-data" type="application/json">
<?= $transactionsJson ?>
</script>

<div class="site-container py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Keuangan Wilayah</h1>
        <p class="text-slate-500 mt-2">Dashboard pantauan arus kas dan riwayat transaksi Keuangan Wilayah GenBI Jambi.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1">
            <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Pemasukan</p>
                <h3 class="text-2xl font-bold text-slate-900" id="summary-masuk">Rp 0</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1">
            <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Pengeluaran</p>
                <h3 class="text-2xl font-bold text-slate-900" id="summary-keluar">Rp 0</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 transition-transform hover:-translate-y-1 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 pointer-events-none"></div>
            <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 relative z-10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <p class="text-sm font-semibold text-slate-500 mb-1">Saldo Kas Saat Ini</p>
                <h3 class="text-2xl font-bold text-slate-900" id="summary-saldo">Rp 0</h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Chart Section -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">Grafik Arus Kas</h3>
                <div class="p-2 bg-slate-50 rounded-lg text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="relative w-full h-[300px]">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="lg:col-span-1 flex flex-col gap-6">
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-6">
                <h3 class="text-base font-bold text-slate-900 mb-4">Pencarian</h3>
                <div class="relative">
                    <input type="text" id="search-input" placeholder="Cari transaksi..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border-transparent focus:bg-white focus:border-blue-500 rounded-xl text-sm transition-all focus:ring-4 focus:ring-blue-500/10 placeholder-slate-400">
                    <svg class="w-5 h-5 text-slate-400 absolute left-4 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-6 flex-grow">
                <h3 class="text-base font-bold text-slate-900 mb-4">Filter Divisi</h3>
                <div class="flex flex-col gap-2" id="filter-container">
                    <button class="category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-blue-600 text-white shadow-md shadow-blue-600/20" data-cat="Semua Kategori">Semua Kategori</button>
                    <button class="category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-slate-50 text-slate-600 hover:bg-slate-100" data-cat="BPI Wilayah">BPI Wilayah</button>
                    <button class="category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-slate-50 text-slate-600 hover:bg-slate-100" data-cat="Tim IT dan Website">Tim IT dan Website</button>
                    <button class="category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-slate-50 text-slate-600 hover:bg-slate-100" data-cat="Tim Media Wilayah">Tim Media Wilayah</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Riwayat Transaksi</h3>
            <span class="text-sm text-slate-500 font-medium" id="page-info">Halaman 1 dari 1</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-900 font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="table-body">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <button id="btn-prev" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-blue-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                Sebelumnya
            </button>
            <button id="btn-next" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-blue-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                Selanjutnya
            </button>
        </div>
    </div>

</div>

<!-- Modal Detail Transaksi -->
<div id="detail-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
                <button type="button" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-xl transition-colors focus:outline-none" onclick="closeDetailModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4" id="detail-modal-body">
                <!-- Content here -->
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button type="button" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-100 transition-all" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let transactions = [];
        const txDataEl = document.getElementById('tx-data');
        if (txDataEl) {
            try {
                transactions = JSON.parse(txDataEl.textContent || '[]');
            } catch(e) {
                console.error('Failed to parse transactions from JSON block', e, txDataEl.textContent);
            }
        }

        // Formatters
        const formatRupiah = (amount) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount).replace('Rp', 'Rp ');
        };
        const formatDate = (dateStr) => {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        };

        // State
        let activeCategory = 'Semua Kategori';
        let searchQuery = '';
        let currentPage = 1;
        const itemsPerPage = 8;
        let chartInstance = null;

        // Elements
        const tableBody = document.getElementById('table-body');
        const pageInfo = document.getElementById('page-info');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const searchInput = document.getElementById('search-input');
        const filterContainer = document.getElementById('filter-container');
        const summaryMasuk = document.getElementById('summary-masuk');
        const summaryKeluar = document.getElementById('summary-keluar');
        const summarySaldo = document.getElementById('summary-saldo');
        const chartCanvas = document.getElementById('financeChart');
        const detailModal = document.getElementById('detail-modal');

        const render = () => {
            let filtered = transactions.filter(t => {
                const matchCat = activeCategory === 'Semua Kategori' || t.category === activeCategory;
                const matchSearch = t.desc.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    (t.event && t.event.toLowerCase().includes(searchQuery.toLowerCase()));
                return matchCat && matchSearch;
            });

            updateSummary(filtered);
            renderChart(filtered);

            const totalPages = Math.ceil(filtered.length / itemsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * itemsPerPage;
            const currentData = filtered.slice(startIdx, startIdx + itemsPerPage);

            tableBody.innerHTML = '';

            if (currentData.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm font-medium">Tidak ada transaksi ditemukan.</td></tr>`;
            } else {
                currentData.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50/50 transition-colors group';

                    const typeColor = t.type === 'in' ? 'text-emerald-600' : 'text-rose-600';
                    const typeSign = t.type === 'in' ? '+' : '-';

                    tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-[13px] font-medium text-slate-600">${formatDate(t.date)}</td>
                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 group-hover:text-blue-600 transition-colors">${t.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100/50">
                            ${t.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold ${typeColor} text-right">
                        ${typeSign}${formatRupiah(t.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button type="button" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" onclick="showDetail(${t.id})" title="Detail">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </td>
                `;
                    tableBody.appendChild(tr);
                });
            }

            pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            btnPrev.disabled = currentPage === 1;
            btnNext.disabled = currentPage === totalPages;
        };

        const updateSummary = (data) => {
            let masuk = 0,
                keluar = 0;
            data.forEach(t => {
                if (t.type === 'in') masuk += t.amount;
                else keluar += t.amount;
            });
            summaryMasuk.textContent = formatRupiah(masuk);
            summaryKeluar.textContent = formatRupiah(keluar);
            summarySaldo.textContent = formatRupiah(masuk - keluar);
        };

        const renderChart = (data) => {
            if (!chartCanvas) return;
            const grouped = {};
            data.forEach(t => {
                const month = t.date.substring(0, 7); // YYYY-MM
                if (!grouped[month]) grouped[month] = {
                    in: 0,
                    out: 0
                };
                grouped[month][t.type] += t.amount;
            });

            const sortedMonths = Object.keys(grouped).sort();
            const labels = sortedMonths.map(m => {
                const d = new Date(m + '-01');
                return d.toLocaleDateString('id-ID', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const dataIn = sortedMonths.map(m => grouped[m].in);
            const dataOut = sortedMonths.map(m => grouped[m].out);

            if (chartInstance) chartInstance.destroy();

            const ctx = chartCanvas.getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Pemasukan',
                            data: dataIn,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderRadius: 4
                        },
                        {
                            label: 'Pengeluaran',
                            data: dataOut,
                            backgroundColor: 'rgba(244, 63, 94, 0.8)',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: {
                                family: "'Inter', sans-serif",
                                size: 13
                            },
                            bodyFont: {
                                family: "'Inter', sans-serif",
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) label += formatRupiah(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [4, 4],
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                },
                                color: '#64748b',
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'M';
                                    if (value >= 1000) return 'Rp ' + (value / 1000) + 'K';
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        };

        window.showDetail = (id) => {
            const t = transactions.find(x => x.id === id);
            if (!t) return;

            const typeBadge = t.type === 'in' ?
                '<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-100">Pemasukan</span>' :
                '<span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold border border-rose-100">Pengeluaran</span>';

            let proofHtml = '<p class="text-sm text-slate-500 italic mt-1">Tidak ada bukti terlampir.</p>';
            if (t.proof) {
                const isLink = t.proof.startsWith('http://') || t.proof.startsWith('https://');
                const url = isLink ? t.proof : '/uploads/keuangan/' + t.proof;
                proofHtml = `
                <a href="${url}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 mt-2 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 rounded-xl text-sm font-semibold transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Lihat Bukti Transaksi
                </a>
            `;
            }

            document.getElementById('detail-modal-body').innerHTML = `
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nominal</p>
                    <p class="text-2xl font-bold text-slate-900">${formatRupiah(t.amount)}</p>
                </div>
                ${typeBadge}
            </div>
            
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal</p>
                    <p class="text-sm font-semibold text-slate-800">${formatDate(t.date)}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Kategori / Divisi</p>
                    <p class="text-sm font-semibold text-slate-800">${t.category}</p>
                </div>
                ${t.event ? `
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Terkait Kegiatan</p>
                    <p class="text-sm font-semibold text-slate-800">${t.event}</p>
                </div>
                ` : ''}
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Keterangan</p>
                    <p class="text-sm font-medium text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">${t.desc}</p>
                </div>
                <div class="col-span-2 mt-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Lampiran</p>
                    ${proofHtml}
                </div>
            </div>
        `;
            detailModal.classList.remove('hidden');
        };

        window.closeDetailModal = () => {
            detailModal.classList.add('hidden');
        };

        // Events
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                currentPage = 1;
                render();
            });
        }

        if (filterContainer) {
            filterContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.category-btn');
                if (btn) {
                    activeCategory = btn.dataset.cat;
                    currentPage = 1;
                    // Update UI
                    filterContainer.querySelectorAll('.category-btn').forEach(b => {
                        if (b.dataset.cat === activeCategory) {
                            b.className = 'category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-blue-600 text-white shadow-md shadow-blue-600/20';
                        } else {
                            b.className = 'category-btn w-full text-left px-5 py-3 rounded-xl text-[13px] font-semibold transition-all bg-slate-50 text-slate-600 hover:bg-slate-100';
                        }
                    });
                    render();
                }
            });
        }

        if (btnPrev) btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                render();
            }
        });
        if (btnNext) btnNext.addEventListener('click', () => {
            currentPage++;
            render();
        });

        // Initial render
        render();
    });
</script>