@extends('layouts.public')
@section('content')
<?php
$item = $item ?? null;
?>
@if(!$item)
<section class="bg-stone py-16 md:py-24">
  <div class="site-container text-center">
    <p class="eyebrow">404</p>
    <h1 class="page-title mt-5">Event tidak ditemukan.</h1>
    <p class="lead mt-7">Event yang Anda cari tidak tersedia atau sudah dihapus.</p>
    <a href="/event" class="btn btn-secondary mt-8">Kembali ke daftar event</a>
  </div>
</section>
@else
<section class="bg-stone py-16 md:py-24">
  <div class="article-container">
    <nav class="mb-6 text-sm text-neutral-500" aria-label="Breadcrumb">
      <a href="/" class="hover:text-blue-800">Beranda</a>
      <span class="mx-2">/</span>
      <a href="/event" class="hover:text-blue-800">Event</a>
      <span class="mx-2">/</span>
      <span class="text-neutral-950">{!! $e($item['title'] ?? '') !!}</span>
    </nav>
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <span class="event-card-badge {!! ($item['status'] ?? '') === 'Upcoming' ? 'upcoming' : '' !!}">{!! $e($item['status'] ?? 'Event') !!}</span>
    </div>
    <h1 class="serif text-4xl font-semibold leading-tight tracking-tight text-neutral-950 md:text-5xl">{!! $e($item['title'] ?? '') !!}</h1>
    <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-neutral-600">
      @if(!empty($item['start_date']))
        <span class="inline-flex items-center gap-2 font-semibold">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>
          {!! $e($item['start_date']) !!}@if(!empty($item['end_date']) && $item['end_date'] !== $item['start_date']) – {!! $e($item['end_date']) !!}@endif
        </span>
      @endif
      @if(!empty($item['location']))
        <span class="inline-flex items-center gap-2 font-semibold text-blue-800">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.14-7.5 11.25-7.5 11.25S4.5 17.64 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
          {!! $e($item['location']) !!}
        </span>
      @endif
    </div>
  </div>
</section>
<section class="bg-cream py-12 md:py-16">
  <div class="article-container">
    @if(!empty($item['banner'] ?? $item['image']))
      <div class="mb-8 overflow-hidden rounded-2xl">
        <img src="{!! $e($item['banner'] ?? $item['image']) !!}" alt="{!! $e($item['title'] ?? '') !!}" class="w-full object-cover" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'" />
      </div>
    @endif
    <div class="prose prose-neutral max-w-none text-base leading-8 text-neutral-700">
      {!! $item['content'] ?? $item['excerpt'] ?? '' !!}
    </div>
    @if(!empty($item['map']))
      <div class="mt-8 overflow-hidden rounded-2xl border border-neutral-900/10">
        <iframe src="{!! $e($item['map']) !!}" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    @endif
    <div class="mt-10 border-t border-neutral-900/10 pt-8">
      <a href="/event" class="btn btn-secondary">Kembali ke daftar event</a>
    </div>
  </div>
</section>
@endif

@endsection

