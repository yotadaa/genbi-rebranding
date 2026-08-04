<?php

use App\Core\Paginator;

/** @var callable $e */
$items = $items ?? [];
$filters = $filters ?? [];
$total = $total ?? 0;
$page = (int) ($page ?? 1);
$perPage = (int) ($perPage ?? 10);
$totalPages = (int) ($totalPages ?? 1);

// Perhitungan nomor urut item
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);

// Membangun parameter filter untuk dipertahankan di URL pagination
$filterParams = array_filter([
    'q' => $filters['q'] ?? '',
    'status' => $filters['status'] ?? '',
    'per_page' => $perPage !== 10 ? $perPage : null,
], static fn($v) => $v !== '' && $v !== null);
?>
<section class="mx-auto max-w-7xl">
    <!-- Header Bagian Atas -->
    <header class="cms-header slide-in flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="eyebrow">Admin CMS</p>
            <h1 class="section-title mt-3">Katalog Buku GenBI</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-600">
                Kelola koleksi literasi, modul, panduan, dan publikasi resmi GenBI Jambi.
            </p>
        </div>
        <div>
            <a href="/admin/buku-add" class="btn btn-primary inline-flex items-center gap-2 px-5 py-2.5 shadow-md hover:shadow-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Buku Baru
            </a>
        </div>
    </header>

    <!-- Bilah Pencarian, Filter & Jumlah Baris (Toolbar) -->
    <section class="admin-card mt-6 p-4 md:p-6 shadow-sm">
        <form method="GET" action="/admin/buku" id="buku-filter-form" class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
            <!-- Smooth Search Box & Status Filter -->
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="smooth-search-input" name="q" value="<?= $e($filters['q'] ?? '') ?>" placeholder="Ketik judul, penulis, atau kategori..." autocomplete="off" class="form-input w-full pl-10 pr-10 py-2 rounded-xl text-sm border-neutral-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all shadow-inner" />
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>

                    <!-- Indikator Loading saat memuat pencarian -->
                    <div id="search-spinner" class="absolute right-3 top-2.5 hidden">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Filter Status -->
                <select name="status" onchange="this.form.submit()" class="form-input rounded-xl text-sm border-neutral-300 py-2 px-3 focus:border-blue-600 font-medium text-neutral-700 w-auto">
                    <option value="">Semua Status</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>

                <?php if (!empty($filters['q']) || !empty($filters['status'])): ?>
                    <a href="/admin/buku" class="inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-800 self-center px-2 py-1 bg-red-50 rounded-lg transition-colors">Reset Filter</a>
                <?php endif; ?>
            </div>

            <!-- Opsi Jumlah Baris & Summary -->
            <div class="flex items-center gap-3 text-sm text-neutral-600 font-medium w-full md:w-auto justify-end border-t md:border-t-0 pt-3 md:pt-0 border-neutral-100">
                <span>Show</span>
                <select name="per_page" onchange="this.form.submit()" class="form-input rounded-lg text-xs border-neutral-300 py-1 px-2.5 font-bold text-blue-900 focus:border-blue-600">
                    <?php foreach ([10, 25, 50, 100] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <span>entries</span>
            </div>
        </form>

        <!-- Informasi Menampilkan Jumlah Data -->
        <?php if ($total > 0): ?>
            <div class="mt-3 text-xs font-medium text-neutral-500 bg-neutral-50 px-3 py-1.5 rounded-lg inline-block border border-neutral-150">
                Menampilkan <strong class="text-neutral-800"><?= $startItem ?> - <?= $endItem ?></strong> dari total <strong class="text-blue-900"><?= number_format($total) ?></strong> buku
            </div>
        <?php endif; ?>

        <!-- Tabel Responsive -->
        <div class="mt-4 overflow-x-auto">
            <?php if ($items === []): ?>
                <div class="p-12 text-center border-2 border-dashed border-neutral-200 rounded-2xl">
                    <svg class="mx-auto h-12 w-12 text-neutral-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-sm font-semibold text-neutral-600">Buku Tidak Ditemukan</p>
                    <p class="text-xs text-neutral-400 mt-1">Belum ada data atau kata kunci pencarian Anda tidak mencocokkan hasil.</p>
                </div>
            <?php else: ?>
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50/75 text-neutral-600 uppercase text-xs tracking-wider font-semibold">
                            <th class="p-3.5 rounded-tl-xl w-14 text-center">SL</th>
                            <th class="p-3.5 w-20">Cover</th>
                            <th class="p-3.5">Detail Buku</th>
                            <th class="p-3.5 hidden md:table-cell">Kategori & Penulis</th>
                            <th class="p-3.5 hidden lg:table-cell text-center">Statistik</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 rounded-tr-xl text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-150">
                        <?php foreach ($items as $index => $item): ?>
                            <tr class="hover:bg-blue-50/25 transition-colors">
                                <!-- Nomor Urut (SL) -->
                                <td class="p-3.5 text-center text-xs font-bold text-neutral-400 align-top">
                                    <?= $startItem + $index ?>
                                </td>
                                <!-- Cover -->
                                <td class="p-3.5 align-top">
                                    <div class="w-14 h-20 bg-neutral-100 rounded-lg overflow-hidden shadow-sm flex items-center justify-center border border-neutral-200 relative">
                                        <?php if (!empty($item['cover'])): ?>
                                            <img src="<?= $e($item['cover']) ?>" alt="<?= $e($item['judul']) ?>" class="w-full h-full object-cover" loading="lazy" />
                                        <?php else: ?>
                                            <span class="text-[9px] text-neutral-400 font-bold uppercase">No Cover</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <!-- Detail Utama -->
                                <td class="p-3.5 align-top">
                                    <p class="font-bold text-neutral-900 leading-snug hover:text-blue-600 transition-colors">
                                        <?= $e($item['judul']) ?>
                                    </p>
                                    <p class="text-xs text-neutral-500 mt-1">
                                        Tahun: <strong class="text-neutral-700"><?= $e($item['tahun']) ?></strong> &bull;
                                        <?= $e($item['halaman']) ?> Halaman
                                    </p>
                                    <!-- Mobile Info -->
                                    <div class="md:hidden mt-2 pt-2 border-t border-neutral-100 text-xs text-neutral-500">
                                        Kategori: <span class="font-semibold text-blue-900"><?= $e($item['kategori']) ?></span> &bull;
                                        Oleh: <?= $e($item['penulis']) ?>
                                    </div>
                                </td>
                                <!-- Kategori & Penulis (Desktop) -->
                                <td class="p-3.5 align-top hidden md:table-cell">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-100">
                                        <?= $e($item['kategori']) ?>
                                    </span>
                                    <p class="text-xs font-medium text-neutral-700 mt-1.5">Oleh: <?= $e($item['penulis']) ?></p>
                                    <p class="text-[11px] text-neutral-400">Penerbit: <?= $e($item['penerbit']) ?></p>
                                </td>
                                <!-- Statistik -->
                                <td class="p-3.5 align-top text-center hidden lg:table-cell">
                                    <div class="text-xs text-neutral-600 font-medium">
                                        <div>👁️ <?= number_format((int) ($item['view_count'] ?? 0)) ?>x diliang</div>
                                        <div class="mt-1 text-[11px] text-neutral-400">📦 <?= $e($item['file_size_formatted'] ?? '0 KB') ?></div>
                                    </div>
                                </td>
                                <!-- Status -->
                                <td class="p-3.5 align-top text-center">
                                    <?php if (($item['status'] ?? '') === 'published'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Published
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <!-- Aksi -->
                                <td class="p-3.5 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <?php if (!empty($item['file_path'])): ?>
                                            <a href="<?= $e($item['file_path']) ?>" target="_blank" class="px-2.5 py-1.5 text-xs font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors" title="Buka File PDF/Ebook">
                                                Preview
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/buku-edit?id=<?= (int) $item['id'] ?>" class="px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                            Edit
                                        </a>
                                        <button type="button" onclick="deleteBukuItem(<?= (int) $item['id'] ?>, '<?= addslashes((string) $item['judul']) ?>')" class="px-2.5 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- PAGINATION SECTION -->
        <?php if ($totalPages > 1): ?>
            <nav class="admin-pagination mt-6 pt-4 border-t border-neutral-200 flex items-center justify-between" aria-label="Pagination buku" data-ssr="true">
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                        <a class="pager-button px-3.5 py-1.5 rounded-lg border border-neutral-300 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 transition-colors" href="/admin/buku?<?= $e(Paginator::buildQuery($page - 1, $filterParams)) ?>">Sebelumnya</a>
                    <?php else: ?>
                        <span class="pager-button px-3.5 py-1.5 rounded-lg border border-neutral-200 text-xs font-semibold text-neutral-300 cursor-not-allowed" aria-disabled="true">Sebelumnya</span>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-1.5">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="pager-button is-active px-3.5 py-1.5 rounded-lg bg-blue-700 text-white text-xs font-bold shadow-sm" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a class="pager-button px-3.5 py-1.5 rounded-lg border border-neutral-300 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 transition-colors" href="/admin/buku?<?= $e(Paginator::buildQuery($i, $filterParams)) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <div class="flex items-center gap-1">
                    <?php if ($page < $totalPages): ?>
                        <a class="pager-button px-3.5 py-1.5 rounded-lg border border-neutral-300 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 transition-colors" href="/admin/buku?<?= $e(Paginator::buildQuery($page + 1, $filterParams)) ?>">Berikutnya</a>
                    <?php else: ?>
                        <span class="pager-button px-3.5 py-1.5 rounded-lg border border-neutral-200 text-xs font-semibold text-neutral-300 cursor-not-allowed" aria-disabled="true">Berikutnya</span>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </section>
</section>

<!-- Skrip Smooth Search (Live Debounce) & Delete Modal -->
<script>
    // 1. Fitur Smooth Search (Otomatis cari tanpa tekan Enter)
    (function() {
        var searchInput = document.getElementById('smooth-search-input');
        var searchForm = document.getElementById('buku-filter-form');
        var spinner = document.getElementById('search-spinner');
        var debounceTimer;

        if (searchInput && searchForm) {
            // Arahkan kursor ke ujung teks jika sedang mencari
            if (searchInput.value.length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                spinner.classList.remove('hidden'); // Tampilkan putaran loading

                // Tunggu 450 milidetik setelah user selesai mengetik, lalu submit
                debounceTimer = setTimeout(function() {
                    searchForm.submit();
                }, 450);
            });
        }
    })();

    // 2. Fitur Hapus Buku dengan Toast Admin
    function deleteBukuItem(id, judul) {
        if (!confirm('Apakah Anda yakin ingin menghapus buku "' + judul + '"?\nData yang dihapus tidak akan ditampilkan lagi.')) {
            return;
        }

        var token = document.querySelector('meta[name=csrf-token]')?.content || '';

        fetch('/admin/buku/' + id + '/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({})
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(res) {
                if (res.data && res.data.deleted) {
                    var toast = document.getElementById('admin-toast');
                    if (toast) {
                        toast.textContent = 'Buku berhasil dihapus!';
                        toast.classList.add('is-show');
                        setTimeout(function() {
                            toast.classList.remove('is-show');
                        }, 3000);
                    }
                    window.location.reload();
                } else {
                    alert('Gagal menghapus: ' + (res.error || 'Terjadi kesalahan'));
                }
            })
            .catch(function() {
                alert('Terjadi kesalahan jaringan saat menghapus data.');
            });
    }
</script>