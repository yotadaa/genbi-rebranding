@extends('layouts.admin')

@section('content')
<section class="mx-auto max-w-7xl">
  <header class="cms-header slide-in">
    <div>
      <p class="eyebrow">Admin CMS</p>
      <h1 class="section-title mt-3">Dashboard</h1>
      <p class="mt-4 max-w-2xl text-base leading-7 text-neutral-600">Selamat datang di Dashboard Admin GenBI Jambi.</p>
    </div>
  </header>

  <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="admin-card p-6 flex flex-col justify-center text-center">
      <h3 class="text-neutral-500 text-sm font-medium">Total Berita</h3>
      <p class="text-4xl font-bold text-neutral-900 mt-2">{{ $stats['news'] ?? 0 }}</p>
    </div>
    <div class="admin-card p-6 flex flex-col justify-center text-center">
      <h3 class="text-neutral-500 text-sm font-medium">Total Prestasi</h3>
      <p class="text-4xl font-bold text-neutral-900 mt-2">{{ $stats['prestasi'] ?? 0 }}</p>
    </div>
    <div class="admin-card p-6 flex flex-col justify-center text-center">
      <h3 class="text-neutral-500 text-sm font-medium">Komentar Pending</h3>
      <p class="text-4xl font-bold text-neutral-900 mt-2">{{ $stats['comments'] ?? 0 }}</p>
    </div>
  </div>
</section>
@endsection
