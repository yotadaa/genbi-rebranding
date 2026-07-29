@extends('layouts.admin')

@section('content')
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div><p class="eyebrow">Admin CMS</p><h1 class="section-title mt-3">Prestasi Token</h1><p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Generate dan kelola link form prestasi sekali pakai untuk dibagikan ke anggota di luar admin.</p></div>
    <div class="cms-actions"><button type="button" id="generate-token-btn" class="btn btn-primary">Buat Link Form Prestasi</button></div>
  </header>
  <div class="mt-6"><section class="admin-card p-0"><div class="admin-data-table-wrap"><table class="admin-table admin-data-table"><thead><tr><th>No.</th><th>Label</th><th>Untuk</th><th>Status</th><th>Dibuat</th><th>Kedaluwarsa</th><th>Digunakan</th><th>Action</th></tr></thead><tbody id="admin-prestasi-token-list" data-ssr="true">
    @forelse($items ?? [] as $index => $item)
      @php($status = $item['status'] ?? 'active')
      <tr><td class="admin-cell-index">{{ $index + 1 }}</td><td class="admin-cell-title"><strong>{{ $item['label'] ?? '' }}</strong></td><td class="admin-cell-meta">{{ $item['intended_for'] ?? '-' }}</td><td class="admin-cell-status"><span class="cms-pill {{ $status === 'active' ? 'cms-pill-green' : ($status === 'used' ? 'cms-pill-yellow' : '') }}">{{ ucfirst($status) }}</span></td><td class="admin-cell-meta">{{ $item['created_at'] ?? '-' }}</td><td class="admin-cell-meta">{{ $item['expires_at'] ?? 'Tidak ada' }}</td><td class="admin-cell-meta">{{ $item['used_at'] ?? '-' }}</td><td class="admin-cell-actions"><div class="admin-table-actions"><button type="button" class="btn btn-secondary btn-sm" data-copy-token-url data-token-id="{{ (int) $item['id'] }}" data-token-url="">Copy URL</button>@if($status === 'active')<button type="button" class="btn btn-danger btn-sm" data-revoke data-token-id="{{ (int) $item['id'] }}">Revoke</button>@endif</div></td></tr>
    @empty<tr><td colspan="8" class="text-center text-sm text-neutral-500">Belum ada token.</td></tr>@endforelse
  </tbody></table></div></section>
  <div id="generated-token-display" class="mt-6 hidden"><section class="admin-card p-6 border-2 border-green-200 bg-green-50"><h3 class="text-lg font-bold text-green-900">Token Berhasil Dibuat</h3><p class="mt-2 text-sm text-green-800">Salin link di bawah. Token hanya ditampilkan sekali dan tidak dapat dilihat kembali.</p><div class="mt-4 flex items-center gap-3"><input id="generated-token-url" class="config-input flex-1 font-mono text-sm" readonly><button id="copy-token-url" class="btn btn-primary">Copy</button></div></section></div>
  </div>
</section>
@endsection
