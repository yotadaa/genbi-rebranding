@extends('layouts.public')

@section('content')
@php
$programs = $programs ?? [];
@endphp

<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container fade-up">
    <p class="eyebrow">Program Utama</p>
    <h1 class="page-title mt-5">Program yang tumbuh bersama anggota dan masyarakat.</h1>
    <p class="lead mt-7">Rangkaian program GenBI Provinsi Jambi dalam edukasi, pengabdian, kepemimpinan, dan kolaborasi.</p>
  </div>
</section>

<section class="home-section-surface bg-cream py-16 md:py-24">
  <div class="site-container">
    @if ($programs !== [])
      <div class="feature-page-grid" id="feature-program-list" data-ssr="true" aria-label="Daftar Program Utama">
        @foreach ($programs as $index => $program)
          @php
          $images = array_values(array_filter($program['images'] ?? [], static fn ($image): bool => is_string($image) && $image !== ''));
          $firstImage = $images[0] ?? '/uploads/slider-1.png';
          @endphp
          <article
            class="editorial-slide-card program-slide-card feature-page-card"
            data-program-slides='{!! json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]' !!}'
            style="--program-bg-image: url('{{ $firstImage }}');"
          >
            <span class="slide-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="program-icon mx-auto" data-program-icon="{{ $program['icon_key'] ?? 'sparkles' }}"></span>
            <p class="slide-kicker">{{ $program['title'] ?? '' }}</p>
            <h2>{{ $program['name'] ?? '' }}</h2>
            @if (!empty($program['description']))
              <p>{{ $program['description'] }}</p>
            @endif
            @if (!empty($program['focus']))
              <span class="blue-badge mx-auto mt-5">{{ $program['focus'] }}</span>
            @endif
          </article>
        @endforeach
      </div>
    @else
      <div class="soft-card p-8 text-center text-sm leading-7 text-neutral-600">
        Program Utama belum dipublikasikan.
      </div>
    @endif
  </div>
</section>

@endsection
