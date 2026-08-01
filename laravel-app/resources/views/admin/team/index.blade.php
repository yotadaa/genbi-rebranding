@extends('layouts.admin')

@section('content')
@php
    $items = $items ?? [];
    $filters = $filters ?? [];
    $options = $filterOptions ?? ['divisions' => [], 'campuses' => [], 'years' => []];
    $page = $page ?? 1;
    $perPage = $perPage ?? 24;
    $total = $total ?? 0;
    $totalPages = $totalPages ?? 1;
    $layout = $layout ?? 'grid';
    $startItem = $total ? (($page - 1) * $perPage) + 1 : 0;
    $endItem = min($page * $perPage, $total);
    $filterParams = array_filter([
        'q' => $filters['q'] ?? '',
        'division' => $filters['division'] ?? '',
        'campus' => $filters['campus'] ?? '',
        'year' => $filters['year'] ?? '',
        'per_page' => $perPage,
    ], static fn ($value) => $value !== '');
@endphp

<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">View Team Members</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Direktori anggota GenBI. Listing beranda memakai periode terbaru dan dapat dioverride lewat aksi BPI Beranda.</p>
    </div>
  </header>

  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form method="get" action="{{ route('admin.team') }}" class="cms-toolbar team-control-toolbar">
        <input type="hidden" name="layout" value="{{ $layout }}">
        <div class="team-control-group team-control-primary">
          <div class="team-control-filters">
            <label class="team-filter-field text-sm text-neutral-600">
              Show
              <select name="per_page" class="config-input w-auto js-admin-custom-select" onchange="this.form.submit()">
                @foreach ([12, 24, 48, 100] as $option)
                  <option value="{{ $option }}" @selected($option === (int) $perPage)>{{ $option }}</option>
                @endforeach
              </select>
            </label>
            <label class="team-filter-field text-sm text-neutral-600">
              Divisi
              <select name="division" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Divisi</option>
                @foreach ($options['divisions'] as $division)
                  <option value="{{ $division }}" @selected(($filters['division'] ?? '') === (string) $division)>{{ $division }}</option>
                @endforeach
              </select>
            </label>
            <label class="team-filter-field text-sm text-neutral-600">
              Komisariat
              <select name="campus" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Komisariat</option>
                @foreach ($options['campuses'] as $campus)
                  <option value="{{ $campus }}" @selected(($filters['campus'] ?? '') === (string) $campus)>{{ $campus }}</option>
                @endforeach
              </select>
            </label>
            <label class="team-filter-field text-sm text-neutral-600">
              Tahun
              <select name="year" class="config-input js-admin-custom-select" onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                @foreach ($options['years'] as $year)
                  <option value="{{ $year }}" @selected(($filters['year'] ?? '') === (string) $year)>{{ $year }}</option>
                @endforeach
              </select>
            </label>
          </div>
          <div class="team-control-actions">
            <label class="team-filter-field text-sm text-neutral-600">Cari</label>
            <label class="cms-search">
              <input name="q" placeholder="Cari nama, jabatan, komisariat, divisi..." value="{{ $filters['q'] ?? '' }}">
            </label>
          </div>
        </div>
        <div class="team-control-group team-control-secondary">
          <div class="view-toggle" role="group" aria-label="Layout mode">
            <a href="?{{ http_build_query(array_merge($filterParams, ['layout' => 'grid'])) }}" class="view-toggle-btn {{ $layout === 'grid' ? 'is-active' : '' }}">Grid</a>
            <a href="?{{ http_build_query(array_merge($filterParams, ['layout' => 'list'])) }}" class="view-toggle-btn {{ $layout === 'list' ? 'is-active' : '' }}">List</a>
          </div>
          <button type="button" class="cms-action edit" id="team-batch-toggle">Batch Operation</button>
        </div>
      </form>

      <div class="team-batch-bar mt-3 hidden" id="team-batch-bar">
        <strong><span id="team-selection-count">0</span> dipilih</strong>
        <button type="button" class="cms-action delete" data-team-bulk="delete">Delete</button>
        <button type="button" class="cms-action" data-team-bulk="alumni">Jadikan Alumni</button>
        <button type="button" class="cms-action" id="team-selection-clear">Clear</button>
      </div>

      @if ($total > 0)
        <div class="mt-4 text-sm text-neutral-600">Menampilkan {{ $startItem }}-{{ $endItem }} dari {{ $total }} anggota.</div>
      @endif
    </section>

    <div class="{{ $layout === 'grid' ? 'team-card-grid' : 'team-card-list' }} mt-5" id="admin-team-list" data-ssr="true">
      @forelse ($items as $item)
        <article class="team-admin-card {{ !empty($item['show_on_home']) ? 'is-home' : '' }}" data-team-id="{{ (int) $item['id'] }}">
          <label class="team-select-check hidden"><input type="checkbox" data-team-select="{{ (int) $item['id'] }}"> Select</label>
          <div class="team-admin-photo">
            @if (!empty($item['photo']))
              <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}">
            @else
              {{ mb_substr($item['name'] ?? '', 0, 2) }}
            @endif
          </div>
          <div class="team-admin-content">
            <h2>{{ $item['name'] ?? '' }}</h2>
            <p>{{ $item['role'] ?? '' }}</p>
            <div class="team-tags">
              @foreach (['campus', 'division', 'year'] as $key)
                @if (!empty($item[$key]))<span>{{ $item[$key] }}</span>@endif
              @endforeach
            </div>
          </div>
          <div class="team-card-actions">
            <button type="button" class="cms-action" data-team-home="{{ (int) $item['id'] }}">{{ !empty($item['show_on_home']) ? 'Hapus BPI' : 'BPI Beranda' }}</button>
            <button type="button" class="cms-action" data-team-alumni="{{ (int) $item['id'] }}">Jadikan Alumni</button>
            <a href="{{ url('/admin/team-member-edit?id=' . (int) $item['id']) }}" class="cms-action edit">Edit</a>
            <button type="button" class="cms-action delete" data-delete-team="{{ (int) $item['id'] }}">Delete</button>
          </div>
        </article>
      @empty
        <div class="admin-card p-8 text-center text-sm text-neutral-500">Belum ada anggota.</div>
      @endforelse
    </div>

    @if ($totalPages > 1)
      <nav class="admin-pagination mt-5" aria-label="Pagination anggota" data-ssr="true">
        @for ($number = 1; $number <= $totalPages; $number++)
          @if ($number === $page)
            <span class="pager-button is-active">{{ $number }}</span>
          @else
            <a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $number]) }}">{{ $number }}</a>
          @endif
        @endfor
      </nav>
    @endif
  </div>
</section>
@endsection
