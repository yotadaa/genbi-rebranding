@extends('layouts.public')
@section('content')
<?php
$contact = $contact ?? [];
$placeName = $contact['place_name'] ?? 'Bank Indonesia Jambi';
$address = $contact['address'] ?? '';
$email = $contact['email'] ?? '';
$phone = $contact['phone'] ?? '';
$coordinatesLabel = $contact['coordinates_label'] ?? '';
$mapsUrl = $contact['maps_url'] ?? '';
$mapEmbedUrl = $contact['map_embed_url'] ?? '';
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="site-container fade-up">
    <p class="eyebrow">Contact</p>
    <h1 class="page-title mt-5 max-w-4xl">Hubungi GenBI Provinsi Jambi.</h1>
    <p class="lead mt-7 max-w-3xl">Kontak utama, alamat, dan titik lokasi kami ditampilkan langsung agar komunikasi lebih cepat dan jelas.</p>
  </div>
</section>
<section class="bg-cream py-12 md:py-20">
  <div class="site-container grid gap-8 lg:grid-cols-[0.88fr_1.12fr]">
    <div class="fade-up grid gap-4">
      <div class="soft-card p-6">
        <p class="eyebrow">Alamat</p>
        <p class="mt-4 text-base leading-7 text-neutral-700">{!! $e($address) !!}</p>
      </div>
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
        <a href="tel:{!! $e($phone) !!}" class="soft-card block p-6 hover:bg-blue-50/60">
          <p class="eyebrow">Telepon</p>
          <p class="mt-4 font-semibold text-neutral-950">{!! $e($phone) !!}</p>
        </a>
        <a href="mailto:{!! $e($email) !!}" class="soft-card block p-6 hover:bg-blue-50/60">
          <p class="eyebrow">Email</p>
          <p class="mt-4 break-all font-semibold text-neutral-950">{!! $e($email) !!}</p>
        </a>
      </div>

      <article class="contact-map-meta-card">
        <span class="blue-badge">Map preview</span>
        <h2 class="serif mt-4 text-[2rem] font-semibold tracking-tight text-blue-950">{!! $e($placeName) !!}</h2>
        @if($coordinatesLabel !== '')
          <p class="mt-3 text-sm font-semibold uppercase tracking-[0.1em] text-blue-800">{!! $e($coordinatesLabel) !!}</p>
        @endif
        <p class="mt-3 text-sm leading-7 text-neutral-600">{!! $e($address) !!}</p>
        @if($mapsUrl !== '')
          <a href="{!! $e($mapsUrl) !!}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-6 w-fit">Open in Google Maps</a>
        @endif
      </article>
    </div>

    <div class="fade-up grid gap-5">
      <div class="contact-map-shell">
        @if($mapEmbedUrl !== '')
          <iframe src="{!! $e($mapEmbedUrl) !!}" title="{!! $e('Peta ' . $placeName) !!}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        @else
          <div class="contact-map-empty">
            <strong>Preview peta belum tersedia.</strong>
            <p>Lengkapi koordinat di admin contact settings untuk menampilkan peta.</p>
          </div>
        @endif
      </div>

      <form id="contact-form" class="soft-card p-5 md:p-7">
        <div class="grid gap-4 md:grid-cols-2">
          <input name="name" class="input-soft" placeholder="Nama" />
          <input name="phone" class="input-soft" placeholder="Nomor telepon" />
          <input name="email" class="input-soft" placeholder="Email" />
          <input name="subject" class="input-soft" placeholder="Subjek" />
        </div>
        <textarea name="message" class="input-soft mt-4 min-h-44 resize-none" placeholder="Pesan"></textarea>
        <button class="btn btn-primary mt-5" type="submit">Kirim Pesan</button>
        <p id="form-message" class="mt-4 hidden rounded-2xl bg-blue-50 p-4 text-sm font-medium text-blue-950"></p>
      </form>
    </div>
  </div>
</section>


@endsection

