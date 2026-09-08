@extends('layouts.admin')
@section('content')
@php
  $page = $page ?? 1;
  $perPage = $perPage ?? 25;
  $total = $total ?? 0;
  $totalPages = $totalPages ?? 1;
  $items = $items ?? [];
  $activities = $activities ?? [];
  $filters = $filters ?? [];
  $query = (string) ($filters['q'] ?? '');
  $startItem = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
  $endItem = min($page * $perPage, $total);
  $formatActivityDate = static function (mixed $value): string {
      $raw = trim((string) $value);
      if ($raw === '') return '-';
      try { return (new DateTimeImmutable($raw))->format('d M Y'); } catch (Throwable) { return $raw; }
  };
  $paginationWindow = static function (int $current, int $total): array {
      if ($total <= 7) return range(1, $total);
      $pages = [1, 2, $total - 1, $total, $current - 1, $current, $current + 1];
      if ($current <= 3) $pages[] = 3;
      if ($current >= $total - 2) $pages[] = $total - 2;
      $pages = array_values(array_unique(array_filter($pages, static fn($value): bool => $value >= 1 && $value <= $total)));
      sort($pages);
      $window = [];
      $previous = null;
      foreach ($pages as $value) {
          if ($previous !== null && $value - $previous > 1) $window[] = 'ellipsis-' . $previous . '-' . $value;
          $window[] = $value;
          $previous = $value;
      }
      return $window;
  };
  $eyeIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z"/></svg>';
@endphp
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">GenBI Poin</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Rekap poin anggota dari presensi yang sudah approved dan aktivitas manual.</p>
    </div>
    <div class="cms-actions">
      <a href="{{ route('admin.genbiPoin.add') }}" class="btn btn-primary">Tambah Aktivitas</a>
    </div>
  </header>

  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form class="cms-toolbar cms-toolbar-admin" method="get" action="/admin/genbi-poin">
        <div class="admin-toolbar-row">
          <label class="admin-toolbar-inline-label text-sm text-neutral-600">Show
            <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">
              @foreach ([10, 25, 50, 100] as $opt)
                <option value="{{ $opt }}" @selected($opt === $perPage)>{{ $opt }}</option>
              @endforeach
            </select>
            entries
          </label>
          <div class="cms-search">
            <input name="q" placeholder="Cari nama anggota..." value="{{ $query }}" />
            <noscript><button type="submit" class="btn btn-secondary btn-sm">Cari</button></noscript>
          </div>
        </div>
        @if ($total > 0)
          <div class="admin-toolbar-summary text-sm text-neutral-600">
            Menampilkan {{ $startItem }}-{{ $endItem }} dari {{ $total }} anggota.
          </div>
        @endif
      </form>
    </section>

    <section class="admin-card p-0 mt-5">
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table genbi-point-table">
          <colgroup>
            <col class="genbi-point-col-no">
            <col class="genbi-point-col-member">
            <col class="genbi-point-col-detail">
            <col class="genbi-point-col-meta">
            <col class="genbi-point-col-meta">
            <col class="genbi-point-col-score">
            <col class="genbi-point-col-score">
            <col class="genbi-point-col-score">
          </colgroup>
          <thead>
            <tr>
              <th>No.</th>
              <th>Anggota</th>
              <th>Detail Aktivitas</th>
              <th>Divisi</th>
              <th>Kampus</th>
              <th>Poin Presensi</th>
              <th>Poin Manual</th>
              <th>Total Poin</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($items as $index => $item)
              <tr>
                <td class="admin-cell-index">{{ $startItem + $index }}</td>
                <td class="admin-cell-title">
                  <strong>{{ $item['name'] ?? '' }}</strong>
                  <p>{{ $item['role'] ?? '-' }}</p>
                </td>
                <td class="admin-cell-actions">
                  <a class="btn btn-outline btn-sm genbi-point-detail-link" href="{{ route('admin.genbiPoin.show', ['id' => (int) ($item['id'] ?? 0)]) }}">
                    {!! $eyeIcon !!}
                    <span>Lihat Detail Aktivitas</span>
                  </a>
                </td>
                <td class="admin-cell-meta">{{ $item['division'] ?? '-' }}</td>
                <td class="admin-cell-meta">{{ $item['campus'] ?? '-' }}</td>
                <td class="admin-cell-meta">{{ (int) ($item['presensi_points'] ?? 0) }} poin</td>
                <td class="admin-cell-meta">{{ (int) ($item['manual_points'] ?? 0) }} poin</td>
                <td class="admin-cell-title"><strong>{{ (int) ($item['total_points'] ?? 0) }} poin</strong></td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-sm text-neutral-500">Belum ada anggota yang cocok.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    @if ($totalPages > 1)
      <nav class="admin-pagination mt-5" aria-label="Pagination GenBI Poin">
        @if ($page > 1)
          <a class="pager-button pager-button-icon" href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'per_page' => $perPage, 'q' => $query]) }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
        @else
          <span class="pager-button pager-button-icon" aria-disabled="true">&lsaquo;</span>
        @endif
        @foreach ($paginationWindow((int) $page, (int) $totalPages) as $entry)
          @if (is_string($entry))
            <span class="pager-ellipsis" aria-hidden="true">...</span>
          @elseif ($entry === $page)
            <span class="pager-button is-active" aria-current="page">{{ $entry }}</span>
          @else
            <a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $entry, 'per_page' => $perPage, 'q' => $query]) }}">{{ $entry }}</a>
          @endif
        @endforeach
        @if ($page < $totalPages)
          <a class="pager-button pager-button-icon" href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'per_page' => $perPage, 'q' => $query]) }}" aria-label="Halaman berikutnya">&rsaquo;</a>
        @else
          <span class="pager-button pager-button-icon" aria-disabled="true">&rsaquo;</span>
        @endif
      </nav>
    @endif

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">Aktivitas Manual</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Riwayat Terbaru</h2>
        </div>
        <a href="{{ route('admin.genbiPoin.add') }}" class="btn btn-secondary btn-sm">Tambah Aktivitas</a>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table">
          <thead>
            <tr>
              <th>Anggota</th>
              <th>Kegiatan</th>
              <th>Poin</th>
              <th>Tanggal</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($activities as $activity)
              <tr>
                <td class="admin-cell-title"><strong>{{ $activity['member_name'] ?? '' }}</strong></td>
                <td class="admin-cell-meta">{{ $activity['activity_name'] ?? '' }}</td>
                <td class="admin-cell-meta">{{ (int) ($activity['points'] ?? 0) }} poin</td>
                <td class="admin-cell-meta">{{ $formatActivityDate($activity['activity_date'] ?? '') }}</td>
                <td class="admin-cell-actions"><a class="btn btn-secondary btn-sm" href="{{ route('admin.genbiPoin.edit', ['id' => (int) ($activity['id'] ?? 0)]) }}">Edit</a></td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-sm text-neutral-500">Belum ada aktivitas manual.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
</section>
@endsection
