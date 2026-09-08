@extends('layouts.admin')
@section('content')
@php
  $item = $item ?? null;
  $submissions = $submissions ?? [];
  $roleOptions = $item && is_array($item['role_options'] ?? null)
      ? $item['role_options']
      : array_map(static fn($role): array => ['name' => (string) $role, 'score' => 0], $item && is_array($item['roles'] ?? null) ? $item['roles'] : []);
  $roleScores = [];
  foreach ($roleOptions as $roleOption) {
      $roleName = (string) ($roleOption['name'] ?? '');
      if ($roleName !== '') $roleScores[$roleName] = (int) ($roleOption['score'] ?? 0);
  }
  $roleNames = array_values(array_keys($roleScores));
  $formatPresensiTime = static function (mixed $value): string {
      $raw = trim((string) $value);
      if ($raw === '') return '-';
      try { $date = new DateTimeImmutable($raw); } catch (Throwable) { return $raw; }
      $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
      $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
      return sprintf('%s, %s %s %s %s', $days[$date->format('l')] ?? $date->format('l'), $date->format('H:i'), $date->format('j'), $months[(int) $date->format('n')] ?? $date->format('m'), $date->format('Y'));
  };
  $members = $item && is_array($item['members'] ?? null) ? $item['members'] : [];
  $submissionsByTeamId = [];
  $memberIds = [];
  foreach ($members as $member) {
      $memberId = (int) ($member['id'] ?? 0);
      if ($memberId > 0) $memberIds[$memberId] = true;
  }
  foreach ($submissions as $submission) {
      $teamId = (int) ($submission['team_id'] ?? 0);
      if ($teamId > 0 && !isset($submissionsByTeamId[$teamId])) $submissionsByTeamId[$teamId] = $submission;
  }
@endphp
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Detail Presensi</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">{{ $item ? ($item['event_name'] ?? '') : 'Event tidak ditemukan.' }}</p>
    </div>
    <div class="cms-actions">
      <a href="{{ route('admin.presensi') }}" class="btn btn-secondary">View All</a>
      @if ($item)
        <a href="{{ route('admin.presensi.edit', ['id' => (int) $item['id']]) }}" class="btn btn-primary">Edit Event</a>
      @endif
    </div>
  </header>

  @if (!$item)
    <section class="admin-card mt-6 p-6 text-sm text-neutral-600">Event presensi tidak ditemukan.</section>
  @else
    @php
      $publicUrl = (string) ($item['public_url'] ?? '');
      $absoluteUrl = $publicUrl !== '' ? $publicUrl : '#';
    @endphp
    <div class="mt-6 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
      <section class="admin-card p-5 md:p-6">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <p class="eyebrow">Event</p>
            <h2 class="mt-2 text-2xl font-bold text-neutral-950">{{ $item['event_name'] ?? '' }}</h2>
            <p class="mt-2 text-sm text-neutral-600">{{ $item['location'] ?? '' }}</p>
          </div>
          <div>
            <p class="eyebrow">Status</p>
            <div class="mt-2 flex flex-wrap gap-2">
              <span class="cms-pill cms-pill-green">{{ ucfirst((string) ($item['status'] ?? 'open')) }}</span>
              <span class="cms-pill">{{ (int) ($item['member_count'] ?? 0) }} anggota</span>
              <span class="cms-pill">{{ count($submissions) }} presensi</span>
            </div>
          </div>
        </div>
        <div class="mt-5">
          <p class="eyebrow">Role</p>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($roleOptions as $role)
              @php
                $roleName = (string) ($role['name'] ?? '');
                $roleScore = (int) ($role['score'] ?? 0);
              @endphp
              @if ($roleName !== '')
                <span class="cms-pill">{{ $roleName }}: {{ $roleScore }} poin</span>
              @endif
            @endforeach
          </div>
        </div>
      </section>

      <aside class="admin-card p-5 md:p-6">
        <p class="eyebrow">Public Link</p>
        <div class="mt-3 presensi-public-link">
          <input class="config-input" readonly value="{{ $publicUrl }}">
          <button class="btn btn-secondary" type="button" data-copy-link="{{ $publicUrl }}">Copy</button>
        </div>
        <div class="mt-4 presensi-qr-box" data-presensi-qr="{{ $absoluteUrl }}" aria-label="QR presensi {{ $item['event_name'] ?? '' }}"></div>
      </aside>
    </div>

    <section class="admin-card p-0 mt-5">
      <div class="presensi-table-header p-5 md:p-6">
        <div>
          <p class="eyebrow">List Presensi Anggota</p>
          <h2 class="mt-2 text-xl font-bold text-neutral-950">Daftar Kehadiran</h2>
        </div>
        <p class="text-sm font-semibold text-neutral-500">{{ count($members) }} anggota event</p>
      </div>
      <div class="admin-data-table-wrap">
        <table class="admin-table admin-data-table presensi-attendance-table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Role</th>
              <th>Skor</th>
              <th>Foto</th>
              <th>Waktu</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="presensi-submission-list" data-event-id="{{ (int) $item['id'] }}">
            @if (empty($members) && empty($submissions))
              <tr><td colspan="7" class="text-center text-sm text-neutral-500">Belum ada anggota event.</td></tr>
            @else
              @foreach ($members as $member)
                @php
                  $memberId = (int) ($member['id'] ?? 0);
                  $submission = $memberId > 0 ? ($submissionsByTeamId[$memberId] ?? null) : null;
                @endphp
                @if ($submission)
                  @php
                    $status = strtolower((string) ($submission['status'] ?? 'pending'));
                    $submissionRole = (string) ($submission['role'] ?? '');
                    $submissionScore = (int) ($roleScores[$submissionRole] ?? 0);
                    $submissionTime = $formatPresensiTime($submission['created_at'] ?? '');
                    $detailSubmission = array_merge($submission, ['role_score' => $submissionScore, 'created_at_label' => $submissionTime]);
                    $photoPayload = ['url' => (string) ($submission['photo_url'] ?? ''), 'name' => (string) ($submission['member_name'] ?? $member['name'] ?? '')];
                  @endphp
                  <tr>
                    <td class="admin-cell-title"><strong>{{ $submission['member_name'] ?? $member['name'] ?? '' }}</strong></td>
                    <td class="admin-cell-meta">{{ $submission['role'] ?? '' }}</td>
                    <td class="admin-cell-meta">{{ $submissionScore }} poin</td>
                    <td class="admin-cell-meta">
                      @if (!empty($submission['photo_url']))
                        <button class="btn btn-outline btn-sm presensi-photo-button" type="button" data-presensi-photo='@json($photoPayload)'>Lihat Foto</button>
                      @endif
                    </td>
                    <td class="admin-cell-meta">{{ $submissionTime }}</td>
                    <td class="admin-cell-status"><span class="cms-pill {{ $status === 'approved' ? 'cms-pill-green' : 'cms-pill-yellow' }}">{{ ucfirst($status) }}</span></td>
                    <td class="admin-cell-actions">
                      <div class="admin-table-actions">
                        <button class="btn btn-outline btn-sm" type="button" data-presensi-detail='@json($detailSubmission)'>Detail</button>
                        @if ($status !== 'approved')
                          <button class="btn btn-primary btn-sm" type="button" data-approve-presensi="{{ (int) ($submission['id'] ?? 0) }}">Approve</button>
                        @endif
                        <button class="btn btn-danger btn-sm" type="button" data-cancel-presensi="{{ (int) ($submission['id'] ?? 0) }}">Batalkan</button>
                      </div>
                    </td>
                  </tr>
                @else
                  @php
                    $manualApprovePayload = ['event_id' => (int) ($item['id'] ?? 0), 'team_id' => $memberId, 'member_name' => (string) ($member['name'] ?? ''), 'roles' => $roleNames];
                  @endphp
                  <tr class="presensi-row-missing">
                    <td class="admin-cell-title"><strong>{{ $member['name'] ?? '' }}</strong></td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-meta">0 poin</td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-meta">-</td>
                    <td class="admin-cell-status"><span class="cms-pill presensi-pill-missing">Belum Presensi</span></td>
                    <td class="admin-cell-actions">
                      @if ($memberId > 0 && $roleNames !== [])
                        <button class="btn btn-primary btn-sm" type="button" data-presensi-manual-approve='@json($manualApprovePayload)'>Approve</button>
                      @else
                        -
                      @endif
                    </td>
                  </tr>
                @endif
              @endforeach
              @foreach ($submissions as $submission)
                @php
                  $teamId = (int) ($submission['team_id'] ?? 0);
                  if ($teamId > 0 && isset($memberIds[$teamId])) continue;
                  $status = strtolower((string) ($submission['status'] ?? 'pending'));
                  $submissionRole = (string) ($submission['role'] ?? '');
                  $submissionScore = (int) ($roleScores[$submissionRole] ?? 0);
                  $submissionTime = $formatPresensiTime($submission['created_at'] ?? '');
                  $detailSubmission = array_merge($submission, ['role_score' => $submissionScore, 'created_at_label' => $submissionTime]);
                  $photoPayload = ['url' => (string) ($submission['photo_url'] ?? ''), 'name' => (string) ($submission['member_name'] ?? '')];
                @endphp
                <tr>
                  <td class="admin-cell-title"><strong>{{ $submission['member_name'] ?? '' }}</strong></td>
                  <td class="admin-cell-meta">{{ $submission['role'] ?? '-' }}</td>
                  <td class="admin-cell-meta">{{ $submissionScore }} poin</td>
                  <td class="admin-cell-meta">
                    @if (!empty($submission['photo_url']))
                      <button class="btn btn-outline btn-sm presensi-photo-button" type="button" data-presensi-photo='@json($photoPayload)'>Lihat Foto</button>
                    @endif
                  </td>
                  <td class="admin-cell-meta">{{ $submissionTime }}</td>
                  <td class="admin-cell-status"><span class="cms-pill {{ $status === 'approved' ? 'cms-pill-green' : 'cms-pill-yellow' }}">{{ ucfirst($status) }}</span></td>
                  <td class="admin-cell-actions">
                    <div class="admin-table-actions">
                      <button class="btn btn-outline btn-sm" type="button" data-presensi-detail='@json($detailSubmission)'>Detail</button>
                      @if ($status !== 'approved')
                        <button class="btn btn-primary btn-sm" type="button" data-approve-presensi="{{ (int) ($submission['id'] ?? 0) }}">Approve</button>
                      @endif
                      <button class="btn btn-danger btn-sm" type="button" data-cancel-presensi="{{ (int) ($submission['id'] ?? 0) }}">Batalkan</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </section>
  @endif
</section>
@endsection
