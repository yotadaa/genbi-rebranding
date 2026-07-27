@extends('layouts.public')

@section('title', 'Tim GenBI Jambi | GenBI Provinsi Jambi')
@section('meta_description', 'Direktori anggota GenBI Provinsi Jambi. Temukan anggota berdasarkan divisi, komisariat, atau tahun.')

@section('content')
@php
$page = $page ?? 1;
$perPage = $perPage ?? 12;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$members = $members ?? [];
$filterOptions = $filterOptions ?? ['divisions' => [], 'campuses' => [], 'years' => []];
$activeDivision = $activeDivision ?? '';
$activeCampus   = $activeCampus ?? '';
$activeYear     = $activeYear ?? '';
$activeQ        = request('q', '');
$startItem = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
$endItem   = min($page * $perPage, $total);

// Helper to build paginator URL
$buildPageUrl = function(int $p) use ($activeDivision, $activeCampus, $activeYear, $activeQ) {
    $params = array_filter([
        'q'        => $activeQ,
        'division' => $activeDivision,
        'campus'   => $activeCampus,
        'year'     => $activeYear,
        'page'     => $p > 1 ? $p : null,
    ], fn($v) => $v !== '' && $v !== null);
    return '/team' . ($params ? '?' . http_build_query($params) : '');
};
@endphp

<section class="public-inner-hero py-16 md:py-24">
  <div class="site-container fade-up">
    <p class="eyebrow">Team</p>
    <h1 class="page-title mt-5">Tim GenBI Jambi.</h1>
    <p class="lead mt-7">Direktori anggota GenBI Provinsi Jambi. Gunakan filter untuk menemukan anggota berdasarkan divisi, komisariat, atau tahun.</p>
  </div>
</section>

<section class="bg-cream py-12 md:py-16">
  <div class="site-container">
    <form class="fade-up mb-8 relative" style="z-index: 20;" method="get" action="/team" id="team-filter-form">
      <div class="grid gap-3 md:grid-cols-[1fr_180px_180px_120px]">
        <input id="team-search" name="q" class="input-soft" placeholder="Cari nama, jabatan, divisi..."
               value="{{ $activeQ }}" />

        <select name="division" id="team-division" class="input-soft js-custom-select" onchange="this.form.submit()">
          <option value="">Semua Divisi</option>
          @foreach($filterOptions['divisions'] ?? [] as $div)
            <option value="{{ $div }}" {{ $div === $activeDivision ? 'selected' : '' }}>{{ $div }}</option>
          @endforeach
        </select>

        <select name="campus" id="team-campus" class="input-soft js-custom-select" onchange="this.form.submit()">
          <option value="">Semua Kampus</option>
          @foreach($filterOptions['campuses'] ?? [] as $campus)
            <option value="{{ $campus }}" {{ $campus === $activeCampus ? 'selected' : '' }}>{{ $campus }}</option>
          @endforeach
        </select>

        <select name="year" id="team-year" class="input-soft js-custom-select" onchange="this.form.submit()">
          <option value="">Semua Tahun</option>
          @foreach($filterOptions['years'] ?? [] as $year)
            <option value="{{ $year }}" {{ (string) $year === (string) $activeYear ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <noscript><button type="submit" class="btn btn-primary mt-3">Filter</button></noscript>
    </form>

    <div class="fade-up mb-4 flex flex-wrap items-center justify-between gap-3">
      <span class="text-sm text-neutral-600" id="team-count">
        @if($total > 0)
          Menampilkan {{ $startItem }}–{{ $endItem }} dari {{ $total }} anggota.
        @else
          Belum ada data anggota yang tersedia.
        @endif
      </span>
      <div class="flex gap-1">
        <button id="team-layout-grid" class="chip is-active" type="button">Grid</button>
        <button id="team-layout-list" class="chip" type="button">List</button>
      </div>
    </div>

    <div class="fade-up team-public-grid" id="team-list" data-ssr="true">
      @if(!empty($members))
        @foreach($members as $member)
          <article class="team-public-card">
            <div class="team-public-photo">
              @if(!empty($member['photo']))
                <img src="{{ $member['photo'] }}" alt="{{ $member['name'] }}" loading="lazy" />
              @else
                <span>{{ mb_substr($member['name'] ?? '', 0, 2) }}</span>
              @endif
            </div>
            <div>
              <p class="eyebrow">{{ $member['year'] ?? '' }}</p>
              <h3 class="serif mt-2 text-2xl font-semibold tracking-tight text-neutral-950">{{ $member['name'] ?? '' }}</h3>
              <p class="mt-2 text-sm font-semibold text-blue-800">{{ $member['role'] ?? '' }}</p>
              @if(!empty($member['division']))
                <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $member['division'] }}</p>
              @endif
              @if(!empty($member['campus']))
                <p class="text-sm leading-6 text-neutral-500">{{ $member['campus'] }}</p>
              @endif
            </div>
          </article>
        @endforeach
      @else
        <div class="rounded-2xl border border-neutral-900/10 bg-white p-8 text-center text-sm text-neutral-600 col-span-full">
          Belum ada data anggota yang cocok dengan filter.
        </div>
      @endif
    </div>

    @if($totalPages > 1)
      <nav class="public-pagination mt-8" id="team-pagination" aria-label="Pagination anggota" data-ssr="true">
        @if($page > 1)
          <a class="pager-button" href="{{ $buildPageUrl($page - 1) }}">Sebelumnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        @endif

        @for($i = 1; $i <= $totalPages; $i++)
          @if($i === $page)
            <span class="pager-button is-active" aria-current="page">{{ $i }}</span>
          @else
            <a class="pager-button" href="{{ $buildPageUrl($i) }}">{{ $i }}</a>
          @endif
        @endfor

        @if($page < $totalPages)
          <a class="pager-button" href="{{ $buildPageUrl($page + 1) }}">Berikutnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        @endif
      </nav>
    @else
      <div class="public-pagination mt-8" id="team-pagination" aria-label="Pagination anggota"></div>
    @endif
  </div>
</section>

<div id="member-modal" class="public-fixed-modal hidden"></div>
@endsection
