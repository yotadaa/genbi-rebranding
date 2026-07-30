@extends('layouts.admin')

@section('content')
@php
  $items = $items ?? []; $filters = $filters ?? []; $page = $page ?? 1; $perPage = $perPage ?? 25;
  $total = $total ?? 0; $totalPages = $totalPages ?? 1; $startItem = $total ? (($page - 1) * $perPage) + 1 : 0; $endItem = min($page * $perPage, $total);
  $prestasiCategories = ['QRIS', 'KTI', 'Essay', 'Inovasi Desa', 'Kreativitas', 'Ekonomi Syariah'];
@endphp
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div><p class="eyebrow">Admin CMS</p><h1 class="section-title mt-3">View Prestasi</h1><p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Daftar prestasi anggota GenBI. Aksi hapus memakai custom confirmation modal.</p></div>
    <div class="cms-actions"><a href="{{ route('admin.prestasi.token') }}" class="btn btn-secondary">Buat Link Form Prestasi</a><a href="{{ route('admin.prestasi.add') }}" class="btn btn-primary">Add Prestasi</a></div>
  </header>
  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form class="cms-toolbar cms-toolbar-admin" method="get" action="{{ route('admin.prestasi') }}" id="prestasi-filter-form">
        <div class="admin-toolbar-row">
          <label class="admin-toolbar-inline-label text-sm text-neutral-600">Show <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">@foreach ([10,25,50,100] as $option)<option value="{{ $option }}" @selected($option === (int) $perPage)>{{ $option }}</option>@endforeach</select> entries</label>
          <select name="category" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()"><option value="">Semua Kategori</option>@foreach ($prestasiCategories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select>
          <select name="status" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
          <div class="cms-search"><input name="q" id="prestasi-search" placeholder="Search prestasi..." value="{{ $filters['q'] ?? '' }}"><noscript><button type="submit" class="btn btn-secondary btn-sm">Cari</button></noscript></div>
        </div>
        @if ($total > 0)<div class="admin-toolbar-summary text-sm text-neutral-600">Menampilkan {{ $startItem }}-{{ $endItem }} dari {{ $total }} prestasi.</div>@endif
      </form>
    </section>
    <section class="admin-card p-0 mt-5"><div class="admin-data-table-wrap"><table class="admin-table admin-data-table admin-data-table-prestasi"><colgroup><col class="prestasi-col-number"><col class="prestasi-col-title"><col class="prestasi-col-member"><col class="prestasi-col-category"><col class="prestasi-col-year"><col class="prestasi-col-institution"><col class="prestasi-col-status"><col class="prestasi-col-actions"></colgroup><thead><tr><th>No.</th><th>Judul</th><th>Nama Anggota</th><th>Kategori</th><th>Tahun</th><th>Penyelenggara</th><th>Status</th><th>Action</th></tr></thead><tbody id="admin-prestasi-list" data-ssr="true">
      @forelse ($items as $index => $item)
        @php($status = strtolower($item['status'] ?? 'draft'))
        <tr><td class="admin-cell-index">{{ $startItem + $index }}</td><td class="admin-cell-title"><div class="admin-table-media">@if(!empty($item['image']))<img src="{{ $item['image'] }}" class="table-thumb rounded" alt="{{ $item['title'] }}" loading="lazy">@else<span class="admin-thumb-placeholder">No image</span>@endif<div class="admin-table-title"><strong>{{ $item['title'] ?? '' }}</strong><p>{{ \Illuminate\Support\Str::limit($item['description'] ?? '', 120) }}</p></div></div></td><td class="admin-cell-meta">{{ $item['name'] ?? '' }}</td><td class="admin-cell-meta"><span class="cms-pill">{{ $item['category'] ?? '' }}</span></td><td class="admin-cell-year">{{ $item['year'] ?? '' }}</td><td class="admin-cell-meta">{{ $item['institution'] ?? '' }}</td><td class="admin-cell-status"><span class="cms-pill {{ $status === 'published' ? 'cms-pill-green' : ($status === 'draft' ? 'cms-pill-yellow' : '') }}">{{ ucfirst($status) }}</span></td><td class="admin-cell-actions"><div class="admin-table-actions"><button type="button" class="btn btn-outline btn-sm" data-detail-prestasi="{{ (int) $item['id'] }}">Detail</button>@if($status !== 'published')<button type="button" class="btn btn-primary btn-sm" data-approve-prestasi="{{ (int) $item['id'] }}">Approve</button>@endif<a class="btn btn-secondary btn-sm" href="{{ route('admin.prestasi.edit', ['id' => (int) $item['id']]) }}">Edit</a><button type="button" class="btn btn-danger btn-sm" data-delete-prestasi="{{ (int) $item['id'] }}">Delete</button></div></td></tr>
      @empty<tr><td colspan="8" class="text-center text-sm text-neutral-500">Belum ada data prestasi.</td></tr>@endforelse
    </tbody></table></div></section>
    @if($totalPages > 1)
      <nav class="admin-pagination mt-5" aria-label="Pagination prestasi">
        @for($number = 1; $number <= $totalPages; $number++)
          @if($number === $page)<span class="pager-button is-active">{{ $number }}</span>@else<a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $number]) }}">{{ $number }}</a>@endif
        @endfor
      </nav>
    @endif
  </div>
</section>
@endsection
