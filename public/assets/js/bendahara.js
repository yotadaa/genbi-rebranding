document.addEventListener('DOMContentLoaded', () => {
    // Shared Data
    let transactions = [];
    let categories = [];
    try {
        const dataEl = document.getElementById('trx-data');
        if (dataEl) {
            transactions = JSON.parse(dataEl.dataset.transactions || '[]');
        }
        const catEl = document.getElementById('trx-categories');
        if (catEl) {
            categories = JSON.parse(catEl.dataset.categories || '[]');
        }
    } catch (e) {
        console.error('Error parsing data:', e);
    }

    // Formatting utility
    const formatRupiah = (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount).replace('Rp', 'Rp ');
    };

    const formatDate = (dateStr) => {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    // Mobile Sidebar Toggle
    const btnMobileMenu = document.getElementById('btn-mobile-menu');
    const sidebarMenu = document.getElementById('sidebar-menu');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    
    if (btnMobileMenu && sidebarMenu && sidebarBackdrop) {
        const closeSidebar = () => {
            sidebarMenu.classList.remove('translate-x-0');
            sidebarMenu.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('hidden');
        };

        btnMobileMenu.addEventListener('click', () => {
            sidebarMenu.classList.remove('-translate-x-full');
            sidebarMenu.classList.add('translate-x-0');
            sidebarBackdrop.classList.remove('hidden');
        });

        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // State
    let activeCategory = 'Semua';
    let searchQuery = '';
    let currentPage = 1;
    const itemsPerPage = 5;

    // Elements
    const tableBody = document.getElementById('table-body');
    const pageInfo = document.getElementById('page-info');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const filterContainer = document.getElementById('filter-container');
    const searchInput = document.getElementById('search-input');
    const btnAddTrx = document.getElementById('btn-add-trx');
    const summaryMasuk = document.getElementById('summary-masuk');
    const summaryKeluar = document.getElementById('summary-keluar');
    const summarySaldo = document.getElementById('summary-saldo');
    const chartCanvas = document.getElementById('cashflowChart');

    // Rendering Table
    const renderTable = () => {
        if (!tableBody) return;

        // Filter
        let filtered = transactions.filter(t => {
            const matchCat = activeCategory === 'Semua' || t.category === activeCategory;
            const matchSearch = t.desc.toLowerCase().includes(searchQuery.toLowerCase());
            return matchCat && matchSearch;
        });

        // Sort latest first
        filtered.sort((a, b) => new Date(b.date) - new Date(a.date));

        // Update Chart & Summary if on dashboard
        updateSummary(filtered);
        renderChart(filtered);

        // Pagination
        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * itemsPerPage;
        const currentData = filtered.slice(startIdx, startIdx + itemsPerPage);

        tableBody.innerHTML = '';

        if (currentData.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada data transaksi.</td>
                </tr>
            `;
        } else {
            currentData.forEach(t => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50 transition-colors';
                
                const typeColor = t.type === 'in' ? 'text-blue-600' : 'text-rose-600';
                const typeSign = t.type === 'in' ? '+' : '-';

                // Check if we are on Dashboard (no Edit/Delete) or Transaksi page
                const isDashboard = !document.getElementById('btn-add-trx');

                let actionHtml = `
                    <button type="button" class="btn-detail p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-id="${t.id}" title="Detail">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                `;

                if (!isDashboard) {
                    actionHtml += `
                        <a href="/keuangan/bendahara/wilayah/transaksi/edit/${t.id}" class="btn-edit-link p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors ml-1" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="/keuangan/bendahara/wilayah/transaksi/delete/${t.id}" method="POST" class="inline">
                            <button type="submit" class="btn-delete-link p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1" title="Hapus" onclick="event.preventDefault(); confirmDelete(this.closest('form'));">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    `;
                }

                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${formatDate(t.date)}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${t.desc}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            ${t.category}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${typeColor} text-right">
                        ${typeSign}${formatRupiah(t.amount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            ${actionHtml}
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        if (pageInfo) pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
        if (btnPrev) btnPrev.disabled = currentPage === 1;
        if (btnNext) btnNext.disabled = currentPage === totalPages;

        // Attach action listeners
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id, 10);
                showDetailModal(id);
            });
        });

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id, 10);
                showFormModal(id);
            });
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id, 10);
                deleteTransaction(id);
            });
        });
    };

    const updateSummary = (filtered) => {
        if (!summaryMasuk || !summaryKeluar || !summarySaldo) return;
        
        let masuk = 0;
        let keluar = 0;
        
        filtered.forEach(t => {
            if (t.type === 'in') masuk += t.amount;
            else keluar += t.amount;
        });
        
        summaryMasuk.textContent = formatRupiah(masuk);
        summaryKeluar.textContent = formatRupiah(keluar);
        summarySaldo.textContent = formatRupiah(masuk - keluar);
    };

    let chartInstance = null;
    const renderChart = (filtered) => {
        if (!chartCanvas) return;

        // Group by Date for simplicity in dummy chart
        const grouped = {};
        filtered.forEach(t => {
            if (!grouped[t.date]) grouped[t.date] = { in: 0, out: 0 };
            grouped[t.date][t.type] += t.amount;
        });

        const sortedDates = Object.keys(grouped).sort((a,b) => new Date(a) - new Date(b));
        const labels = sortedDates.map(d => formatDate(d));
        const dataIn = sortedDates.map(d => grouped[d].in);
        const dataOut = sortedDates.map(d => grouped[d].out);

        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = chartCanvas.getContext('2d');
        
        const gradientIn = ctx.createLinearGradient(0, 0, 0, 300);
        gradientIn.addColorStop(0, 'rgba(37, 99, 235, 1)');
        gradientIn.addColorStop(1, 'rgba(59, 130, 246, 0.1)');
        
        const gradientOut = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOut.addColorStop(0, 'rgba(225, 29, 72, 1)');
        gradientOut.addColorStop(1, 'rgba(244, 63, 94, 0.1)');

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: dataIn,
                        backgroundColor: gradientIn,
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 16
                    },
                    {
                        label: 'Pengeluaran',
                        data: dataOut,
                        backgroundColor: gradientOut,
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 16
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.8)',
                            borderDash: [4, 4],
                            drawBorder: false
                        },
                        border: { display: false },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            color: '#94a3b8',
                            padding: 10,
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000) + 'Jt';
                                }
                                if (value >= 1000) {
                                    return 'Rp ' + (value / 1000) + 'K';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: { 
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            boxHeight: 8,
                            font: { family: "'Inter', sans-serif", size: 12, weight: '500' },
                            color: '#475569',
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                        bodyFont: { family: "'Inter', sans-serif", size: 13 },
                        padding: 12,
                        cornerRadius: 12,
                        usePointStyle: true,
                        boxPadding: 6,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatRupiah(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    };

    // Events
    if (filterContainer) {
        filterContainer.addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON') {
                activeCategory = e.target.dataset.cat;
                // Update active state
                filterContainer.querySelectorAll('button').forEach(btn => {
                    if (btn.dataset.cat === activeCategory) {
                        btn.className = 'category-btn px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all bg-[#3b5998] text-white shadow-md';
                    } else {
                        btn.className = 'category-btn px-5 py-2.5 rounded-xl text-[13px] font-medium transition-all bg-slate-50 border border-slate-200/60 text-slate-600 hover:bg-slate-100';
                    }
                });
                currentPage = 1;
                renderTable();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            currentPage = 1;
            renderTable();
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            currentPage++;
            renderTable();
        });
    }

    if (btnAddTrx) {
        btnAddTrx.addEventListener('click', (e) => {
            if (btnAddTrx.tagName === 'BUTTON') {
                e.preventDefault();
                showFormModal();
            }
        });
    }

    // Modals
    const createModalWrapper = (id, contentHtml) => {
        const existing = document.getElementById(id);
        if (existing) existing.remove();
        
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm';
        modal.innerHTML = contentHtml;
        
        document.body.appendChild(modal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });

        return modal;
    };

    const showDetailModal = (id) => {
        const t = transactions.find(x => x.id === id);
        if (!t) return;
        
        const html = `
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
                    <button class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#modal-detail').remove()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tanggal</p>
                        <p class="text-base font-semibold text-slate-900">${formatDate(t.date)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Keterangan Kegiatan</p>
                        <p class="text-base font-semibold text-slate-900">${t.desc}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Divisi / Kategori</p>
                        <p class="text-base font-semibold text-slate-900">${t.category}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tipe Transaksi</p>
                        <p class="text-base font-semibold inline-flex px-2.5 py-0.5 rounded-full text-sm ${t.type === 'in' ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700'}">
                            ${t.type === 'in' ? 'Pemasukan' : 'Pengeluaran'}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Nominal</p>
                        <p class="text-2xl font-bold ${t.type === 'in' ? 'text-blue-600' : 'text-rose-600'}">
                            ${formatRupiah(t.amount)}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 text-right">
                    <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-detail').remove()">Tutup</button>
                </div>
            </div>
        `;
        createModalWrapper('modal-detail', html);
    };

    const showFormModal = (id = null) => {
        let isEdit = id !== null;
        let t = isEdit ? transactions.find(x => x.id === id) : { date: new Date().toISOString().split('T')[0], desc: '', category: categories[0] || '', type: 'out', amount: '' };
        
        const catOptions = categories.map(c => `<option value="${c}" ${t.category === c ? 'selected' : ''}>${c}</option>`).join('');

        const html = `
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
                <form id="trx-form">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">${isEdit ? 'Edit Transaksi' : 'Tambah Transaksi'}</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#modal-form').remove()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / Nama Kegiatan</label>
                            <input type="text" id="form-desc" required value="${t.desc}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Contoh: Pembelian domain">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                                <input type="date" id="form-date" required value="${t.date}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Divisi</label>
                                <select id="form-cat" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    ${catOptions}
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Transaksi</label>
                                <select id="form-type" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                                    <option value="in" ${t.type === 'in' ? 'selected' : ''}>Pemasukan</option>
                                    <option value="out" ${t.type === 'out' ? 'selected' : ''}>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" id="form-amount" required value="${t.amount}" min="0" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="500000">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 transition-colors" onclick="this.closest('#modal-form').remove()">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">Simpan</button>
                    </div>
                </form>
            </div>
        `;
        const modal = createModalWrapper('modal-form', html);
        
        // Handle Submit
        modal.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            const newData = {
                id: isEdit ? id : Date.now(),
                date: document.getElementById('form-date').value,
                desc: document.getElementById('form-desc').value,
                category: document.getElementById('form-cat').value,
                type: document.getElementById('form-type').value,
                amount: parseInt(document.getElementById('form-amount').value, 10)
            };

            if (isEdit) {
                const idx = transactions.findIndex(x => x.id === id);
                if (idx !== -1) transactions[idx] = newData;
            } else {
                transactions.push(newData);
            }

            modal.remove();
            renderTable();
        });
    };

    const deleteTransaction = (id) => {
        if(confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
            transactions = transactions.filter(t => t.id !== id);
            renderTable();
        }
    };

    // Initial render
    renderTable();
});
