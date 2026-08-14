document.addEventListener('DOMContentLoaded', () => {
    const initDashboard = () => {
        const container = document.querySelector('[data-dashboard="true"]');
        if (!container) return;

        let activeCategory = 'Semua';
        let searchQuery = '';
        let currentPage = 1;
        const itemsPerPage = 5;
        
        let transactions = [];
        try {
            transactions = JSON.parse(container.dataset.transactions || '[]');
        } catch(e) {
            console.error("Failed to parse transactions", e);
        }
        
        let chartInstance = null;
        
        // DOM Elements
        const searchInput = document.getElementById('search-input');
        const categoryButtons = document.querySelectorAll('.category-btn');
        const tableBody = document.getElementById('table-body');
        const totalInEl = document.getElementById('total-in');
        const totalOutEl = document.getElementById('total-out');
        const balanceEl = document.getElementById('total-balance');
        const prevPageBtn = document.getElementById('prev-page');
        const nextPageBtn = document.getElementById('next-page');
        const pageIndicator = document.getElementById('page-indicator');
        
        const formatRupiah = (amount) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        };

        const getFilteredTransactions = () => {
            let result = transactions;
            if (activeCategory !== 'Semua') {
                result = result.filter(t => t.category === activeCategory);
            }
            if (searchQuery.trim() !== '') {
                const q = searchQuery.toLowerCase();
                result = result.filter(t => t.desc.toLowerCase().includes(q) || t.category.toLowerCase().includes(q));
            }
            return result;
        };

        const renderSummary = (filtered) => {
            const totalIn = filtered.filter(t => t.type === 'in').reduce((sum, t) => sum + t.amount, 0);
            const totalOut = filtered.filter(t => t.type === 'out').reduce((sum, t) => sum + t.amount, 0);
            const balance = totalIn - totalOut;

            if(totalInEl) totalInEl.textContent = formatRupiah(totalIn);
            if(totalOutEl) totalOutEl.textContent = formatRupiah(totalOut);
            if(balanceEl) balanceEl.textContent = formatRupiah(balance);
        };

        const renderTable = (filtered) => {
            if(!tableBody) return;
            
            const totalPages = Math.max(1, Math.ceil(filtered.length / itemsPerPage));
            if (currentPage > totalPages) currentPage = Math.max(1, totalPages);
            
            const start = (currentPage - 1) * itemsPerPage;
            const paginated = filtered.slice(start, start + itemsPerPage);
            
            tableBody.innerHTML = '';
            if (paginated.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Tidak ada transaksi ditemukan</td></tr>';
            } else {
                paginated.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${t.date}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">${t.desc}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${t.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${t.type === 'in' ? 'text-emerald-600' : 'text-red-600'} text-right">
                            ${t.type === 'in' ? '+' : '-'}${formatRupiah(t.amount)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button type="button" class="btn-detail text-blue-600 hover:text-blue-800" data-id="${t.id}">Detail</button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            }
            
            if (pageIndicator) pageIndicator.textContent = `${currentPage} dari ${totalPages}`;
            if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
            if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;
            
            // Add event listeners for Detail buttons
            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.target.dataset.id, 10);
                    showDetailModal(id);
                });
            });
        };

        const showDetailModal = (id) => {
            const t = transactions.find(x => x.id === id);
            if (!t) return;
            
            // Remove existing modal if any
            const existing = document.getElementById('trx-modal');
            if (existing) existing.remove();
            
            const modal = document.createElement('div');
            modal.id = 'trx-modal';
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm';
            
            modal.innerHTML = `
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Detail Transaksi</h3>
                        <button class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('#trx-modal').remove()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Tanggal</p>
                            <p class="text-base font-semibold text-slate-900">${t.date}</p>
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
                            <p class="text-base font-semibold ${t.type === 'in' ? 'text-emerald-600' : 'text-red-600'}">
                                ${t.type === 'in' ? 'Pemasukan' : 'Pengeluaran'}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Nominal</p>
                            <p class="text-xl font-bold ${t.type === 'in' ? 'text-emerald-600' : 'text-red-600'}">
                                ${formatRupiah(t.amount)}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 text-right">
                        <button class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300" onclick="this.closest('#trx-modal').remove()">Tutup</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close on click outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        };

        const renderChart = (filtered) => {
            const canvas = document.getElementById('financeChart');
            if (!canvas) return;
            
            const dataMap = {};
            filtered.forEach(t => {
                if (!dataMap[t.date]) {
                    dataMap[t.date] = { in: 0, out: 0 };
                }
                dataMap[t.date][t.type] += t.amount;
            });

            const dates = Object.keys(dataMap).sort();
            const ins = dates.map(d => dataMap[d].in);
            const outs = dates.map(d => dataMap[d].out);

            const chartData = {
                labels: dates,
                datasets: [
                    { label: 'Pemasukan', data: ins, backgroundColor: 'rgba(16, 185, 129, 0.8)', borderRadius: 4 },
                    { label: 'Pengeluaran', data: outs, backgroundColor: 'rgba(239, 68, 68, 0.8)', borderRadius: 4 }
                ]
            };

            if (chartInstance) {
                chartInstance.data = chartData;
                chartInstance.update();
            } else {
                const ctx = canvas.getContext('2d');
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        };

        const updateUI = () => {
            const filtered = getFilteredTransactions();
            renderSummary(filtered);
            renderTable(filtered);
            renderChart(filtered);
            
            // Update category buttons visual state
            categoryButtons.forEach(btn => {
                if (btn.dataset.cat === activeCategory) {
                    btn.className = 'category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-md shadow-blue-200';
                } else {
                    btn.className = 'category-btn px-5 py-2 rounded-lg text-sm font-medium transition-all bg-blue-50 text-blue-700 hover:bg-blue-100';
                }
            });
        };

        // Event Listeners
        if (searchInput) {
            // Remove old listener if re-initializing
            const newSearch = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearch, searchInput);
            
            newSearch.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                currentPage = 1;
                updateUI();
            });
        }

        categoryButtons.forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', () => {
                activeCategory = newBtn.dataset.cat;
                currentPage = 1;
                updateUI();
            });
        });

        if (prevPageBtn) {
            const newPrev = prevPageBtn.cloneNode(true);
            prevPageBtn.parentNode.replaceChild(newPrev, prevPageBtn);
            newPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateUI();
                }
            });
        }

        if (nextPageBtn) {
            const newNext = nextPageBtn.cloneNode(true);
            nextPageBtn.parentNode.replaceChild(newNext, nextPageBtn);
            newNext.addEventListener('click', () => {
                const filtered = getFilteredTransactions();
                const totalPages = Math.max(1, Math.ceil(filtered.length / itemsPerPage));
                if (currentPage < totalPages) {
                    currentPage++;
                    updateUI();
                }
            });
        }

        // Initial render
        updateUI();
    };

    // Run on first load
    initDashboard();

    // Since this site uses an SPA transition (which triggers full page reload but maybe we want to be safe)
    // Actually the router in app.js does window.location.href = url.href, so DOMContentLoaded fires on every page.
});
