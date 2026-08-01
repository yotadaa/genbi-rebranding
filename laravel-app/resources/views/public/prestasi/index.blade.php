@extends('layouts.public')
@section('content')
<?php
use App\Core\Paginator;

$page = $page ?? 1;
$perPage = $perPage ?? 12;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$startItem = ($page - 1) * $perPage + 1;
$endItem = min($page * $perPage, $total);
$layout = $layout ?? 'grid';
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container fade-up">
    <p class="eyebrow">Prestasi</p>
    <h1 class="page-title mt-5">Prestasi GenBI Jambi.</h1>
    <p class="lead mt-7">Pencapaian anggota GenBI Provinsi Jambi di berbagai bidang kompetisi dan pengabdian.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="article-container">
    <div class="prestasi-layout-switch mb-8" aria-label="Pilihan layout prestasi">
      <a href="?layout=list" class="prestasi-layout-toggle {{ $layout === 'list' ? 'is-active' : '' }}" aria-label="Tampilkan sebagai list">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h12M3 9h12M3 14h12"/></svg>
      </a>
      <a href="?layout=grid" class="prestasi-layout-toggle {{ $layout === 'grid' ? 'is-active' : '' }}" aria-label="Tampilkan sebagai grid">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="5" height="5" rx="1"/><rect x="11" y="2" width="5" height="5" rx="1"/><rect x="2" y="11" width="5" height="5" rx="1"/><rect x="11" y="11" width="5" height="5" rx="1"/></svg>
      </a>
    </div>
    <div class="fade-up mb-4 text-sm text-neutral-600">
      @if ($total > 0)
        Menampilkan {!! $startItem !!}–{!! $endItem !!} dari {!! $total !!} prestasi.
      @else
        Belum ada data prestasi yang tersedia.
      @endif
    </div>

    @if ($layout === 'list')
    <div class="soft-card overflow-hidden prestasi-list" id="prestasi-list" data-ssr="true">
      @if (!empty($items))
        @foreach ($items as $index => $item)
          <?php $image = (string) ($item['image'] ?? $item['photo'] ?? ''); ?>
          <a href="/prestasi/{{ rawurlencode($item['slug'] ?? $item['id']) }}" class="prestasi-row soft-row fade-up in-view" data-index="{{ $index }}">
            <span class="serif prestasi-number">{{ str_pad($startItem + $index, 2, '0', STR_PAD_LEFT) }}</span>
            <div class="prestasi-row-copy">
              <p class="eyebrow">{!! $e($item['year'] ?? '') !!}</p>
              <h3 class="serif text-xl font-semibold text-neutral-900">{!! $e($item['title'] ?? '') !!}</h3>
            </div>
            <div class="prestasi-row-meta">
              <div class="prestasi-meta-badge inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-900">
                {!! $e($item['category'] ?? '') !!}
              </div>
              <div class="prestasi-meta-name mt-2 text-sm text-neutral-600">
                <span class="font-semibold text-neutral-900">{!! $e($item['name'] ?? '') !!}</span><br />
                {!! $e($item['campus'] ?? $item['institution'] ?? '') !!}
              </div>
            </div>
          </a>
        @endforeach
      @else
        <div class="p-8 text-center text-sm text-neutral-600">Belum ada data prestasi yang tersedia.</div>
      @endif
    </div>
    @else
    <div class="prestasi-grid" id="prestasi-list" data-ssr="true">
      @if (!empty($items))
        @foreach ($items as $index => $item)
          <?php $image = (string) ($item['image'] ?? $item['photo'] ?? ''); ?>
          <a href="/prestasi/{{ rawurlencode($item['slug'] ?? $item['id']) }}" class="prestasi-card soft-card fade-up in-view" data-index="{{ $index }}">
            <div class="prestasi-grid-image bg-blue-50">
              @if($image !== '')
                <img src="{!! $e($image) !!}" alt="{!! $e($item['title'] ?? '') !!}" class="h-full w-full object-cover" loading="lazy" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'">
              @endif
            </div>
            <div class="p-5 text-left">
              <p class="eyebrow">{!! $e($item['year'] ?? '') !!}</p>
              <h3 class="serif mt-2 line-clamp-2 text-lg font-semibold tracking-tight text-neutral-900">{!! $e($item['title'] ?? '') !!}</h3>
              <div class="mt-4 border-t border-neutral-900/5 pt-4">
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-[0.65rem] font-semibold tracking-wider text-blue-900 uppercase">
                  {!! $e($item['category'] ?? '') !!}
                </span>
                <div class="mt-3 text-sm text-neutral-600">
                  <span class="font-semibold text-neutral-900">{!! $e($item['name'] ?? '') !!}</span><br />
                  {!! $e($item['campus'] ?? $item['institution'] ?? '') !!}
                </div>
              </div>
            </div>
          </a>
        @endforeach
      @else
        <div class="p-8 text-center text-sm text-neutral-600">Belum ada data prestasi yang tersedia.</div>
      @endif
    </div>
    @endif

    @if ($totalPages > 1)
      <nav class="public-pagination mt-8" id="prestasi-pagination" aria-label="Pagination prestasi" data-ssr="true">
        @if ($page > 1)
          <a class="pager-button" href="/prestasi?{!! $e(Paginator::buildQuery($page - 1)) !!}">Sebelumnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        @endif
        @for ($i = 1; $i <= $totalPages; $i++)
          @if ($i === $page)
            <span class="pager-button is-active" aria-current="page">{!! $i !!}</span>
          @else
            <a class="pager-button" href="/prestasi?{!! $e(Paginator::buildQuery($i)) !!}">{!! $i !!}</a>
          @endif
        @endfor
        @if ($page < $totalPages)
          <a class="pager-button" href="/prestasi?{!! $e(Paginator::buildQuery($page + 1)) !!}">Berikutnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        @endif
      </nav>
    @else
      <div class="public-pagination mt-8" id="prestasi-pagination" aria-label="Pagination prestasi"></div>
    @endif
  </div>
</section>
<div id="prestasi-modal" class="public-fixed-modal hidden"></div>

@endsection
