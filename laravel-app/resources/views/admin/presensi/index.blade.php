@extends('layouts.admin')
@section('content')
@php
  $page = $page ?? 1;
  $perPage = $perPage ?? 25;
  $total = $total ?? 0;
  $totalPages = $totalPages ?? 1;
  $items = $items ?? [];
  $filters = $filters ?? [];
  $activeQ = $filters['q'] ?? '';
  $activeStatus = $filters['status'] ?? '';
  $startItem = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
  $endItem = min($page * $perPage, $total);
@endphp
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Presensi</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Kelola event, link publik, QR, dan approval kehadiran anggota.</p>
    </div>
    <div class="cms-actions">
      <a href="{{ route('admin.presensi.add') }}" class="btn btn-primary">Add Event</a>
    </div>
  </header>

  <div class="mt-6">
    <section class="admin-card p-4 md:p-6">
      <form class="cms-toolbar cms-toolbar-admin" method="get" action="/admin/presensi" id="presensi-filter-form">
        <div class="admin-toolbar-row">
          <label class="admin-toolbar-inline-label text-sm text-neutral-600">Show
            <select name="per_page" class="admin-inline-select js-admin-custom-select" onchange="this.form.submit()">
              @foreach ([10, 25, 50, 100] as $opt)
                <option value="{{ $opt }}" @selected($opt === $perPage)>{{ $opt }}</option>
              @endforeach
            </select>
            entries
          </label>
          <select name="status" class="admin-toolbar-select js-admin-custom-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="open" @selected($activeStatus === 'open')>Open</option>
            <option value="draft" @selected($activeStatus === 'draft')>Draft</option>
            <option value="closed" @selected($activeStatus === 'closed')>Closed</option>
            <option value="archived" @selected($activeStatus === 'archived')>Archived</option>
          </select>
          <div class="cms-search">
            <input name="q" id="presensi-search" placeholder="Search presensi..." value="{{ $activeQ }}" />
            <noscript><button type="submit" class="btn btn-secondary btn-sm">Cari</button></noscript>
          </div>
        </div>

        @if ($total > 0)
          <div class="admin-toolbar-summary text-sm text-neutral-600">
            Menampilkan {{ $startItem }}-{{ $endItem }} dari {{ $total }} event.
          </div>
        @endif
      </form>
    </section>

    <section class="admin-card p-0 mt-5">
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>Event</th>
              <th>Role</th>
              <th>Anggota</th>
              <th>Kehadiran</th>
              <th>Link</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="admin-presensi-list" data-ssr="true">
            @forelse ($items as $index => $item)
              @php
                $id = (int) ($item['id'] ?? 0);
                $status = strtolower((string) ($item['status'] ?? 'open'));
                $publicUrl = (string) ($item['public_url'] ?? '');
                $absoluteUrl = $publicUrl !== '' ? $publicUrl : '#';
              @endphp
              <tr>
                <td class="admin-cell-index">{{ $startItem + $index }}</td>
                <td class="admin-cell-title">
                  <strong>{{ $item['event_name'] ?? '' }}</strong>
                  <p>{{ $item['location'] ?? '' }}</p>
                </td>
                <td class="admin-cell-meta">
                  <div class="flex flex-wrap gap-1">
                    @foreach (($item['roles'] ?? []) as $role)
                      <span class="cms-pill">{{ $role }}</span>
                    @endforeach
                  </div>
                </td>
                <td class="admin-cell-meta">{{ (int) ($item['member_count'] ?? 0) }} anggota</td>
                <td class="admin-cell-meta">
                  <strong>{{ (int) ($item['submission_count'] ?? 0) }}</strong>
                  <p class="text-xs text-neutral-500">{{ (int) ($item['pending_count'] ?? 0) }} pending, {{ (int) ($item['approved_count'] ?? 0) }} approved</p>
                </td>
                <td class="admin-cell-meta">
                  <button class="cms-action" type="button" data-copy-link="{{ $publicUrl }}">Copy</button>
                  <button class="cms-action" type="button" data-show-qr="{{ $absoluteUrl }}" data-qr-title="{{ $item['event_name'] ?? '' }}">QR</button>
                </td>
                <td class="admin-cell-status">
                  <span class="cms-pill {{ $status === 'open' ? 'cms-pill-green' : ($status === 'draft' ? 'cms-pill-yellow' : '') }}">{{ ucfirst($status) }}</span>
                </td>
                <td class="admin-cell-actions">
                  <div class="admin-table-actions">
                    <a class="btn btn-outline btn-sm" href="{{ route('admin.presensi.show', ['id' => $id]) }}">Detail</a>
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.presensi.edit', ['id' => $id]) }}">Edit</a>
                    <button class="btn btn-danger btn-sm" type="button" data-delete-presensi="{{ $id }}">Delete</button>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-sm text-neutral-500">Belum ada event presensi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    @if ($totalPages > 1)
      <nav class="admin-pagination mt-5" aria-label="Pagination presensi" data-ssr="true">
        @if ($page > 1)
          <a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'per_page' => $perPage, 'q' => $activeQ, 'status' => $activeStatus]) }}">Sebelumnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Sebelumnya</span>
        @endif
        @for ($i = 1; $i <= $totalPages; $i++)
          @if ($i === $page)
            <span class="pager-button is-active" aria-current="page">{{ $i }}</span>
          @else
            <a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $i, 'per_page' => $perPage, 'q' => $activeQ, 'status' => $activeStatus]) }}">{{ $i }}</a>
          @endif
        @endfor
        @if ($page < $totalPages)
          <a class="pager-button" href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'per_page' => $perPage, 'q' => $activeQ, 'status' => $activeStatus]) }}">Berikutnya</a>
        @else
          <span class="pager-button" aria-disabled="true">Berikutnya</span>
        @endif
      </nav>
    @endif
  </div>
</section>
@endsection
