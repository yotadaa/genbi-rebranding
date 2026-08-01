@extends('layouts.public')
@section('content')
<?php
/** @var callable $e */
/** @var callable $url */
$aboutBlocks = [
    ['title' => 'Tentang GenBI', 'text' => 'Generasi Baru Indonesia adalah komunitas mahasiswa penerima beasiswa Bank Indonesia. GenBI Jambi menjadi ruang pembinaan, edukasi publik, literasi kebanksentralan, dan pengembangan kepemimpinan bagi mahasiswa Universitas Jambi dan UIN STS Jambi.'],
    ['title' => 'Visi', 'text' => 'Menjadikan kaum muda Indonesia sebagai generasi yang kompeten dalam berbagai bidang keilmuan, mampu membawa perubahan positif, dan menjadi inspirasi bagi bangsa dan negara.'],
    ['title' => 'Misi', 'text' => 'Menggagas kegiatan pemberdayaan, melakukan aksi nyata, peduli terhadap masyarakat, serta berbagi inspirasi dan motivasi sebagai energi bagi negeri.'],
];
$roles = [
    ['title' => 'Frontliners Bank Indonesia', 'text' => 'Mengkomunikasikan kelembagaan dan berbagai kebijakan Bank Indonesia kepada mahasiswa dan masyarakat umum.'],
    ['title' => 'Change Agents', 'text' => 'Menjadi agen perubahan dan role model di kalangan pelajar, mahasiswa, dan masyarakat.'],
    ['title' => 'Future Leaders', 'text' => 'Mempersiapkan anggota sebagai pemimpin masa depan di berbagai bidang dan tingkatan.'],
];
?>
<section class="public-inner-hero py-16 md:py-24">
  <div class="article-container fade-up">
    <p class="eyebrow">About</p>
    <h1 class="page-title mt-5">Tentang GenBI Provinsi Jambi.</h1>
    <p class="lead mt-7">Halaman ini fokus pada identitas organisasi, visi, misi, tujuan, dan peran GenBI sebagai frontliners Bank Indonesia, change agents, serta future leaders.</p>
  </div>
</section>
<section class="bg-cream py-16 md:py-24">
  <div class="article-container prose-soft fade-up" id="about-content" data-ssr="true">
    @foreach($aboutBlocks as $block)
      <section class="border-t border-neutral-900/10 py-9 first:border-t-0 first:pt-0">
        <h2 class="serif text-3xl font-semibold tracking-tight text-neutral-950">{!! $e($block['title']) !!}</h2>
        <p>{!! $e($block['text']) !!}</p>
      </section>
    @endforeach
  </div>
</section>
<section class="bg-[var(--surface-soft)] py-16 md:py-24">
  <div class="site-container grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
    <div class="fade-up">
      <p class="eyebrow">Role organisasi</p>
      <h2 class="section-title mt-4">Tiga peran utama yang perlu cepat terbaca.</h2>
    </div>
    <div class="fade-up soft-card overflow-hidden" id="role-list" data-ssr="true">
      @foreach($roles as $index => $role)
        <article class="soft-row grid gap-4 p-6 md:grid-cols-[80px_1fr]">
          <span class="serif text-4xl font-semibold text-blue-800">0{!! $index + 1 !!}</span>
          <div>
            <h3 class="text-lg font-bold text-neutral-950">{!! $e($role['title']) !!}</h3>
            <p class="mt-2 text-sm leading-7 text-neutral-600">{!! $e($role['text']) !!}</p>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>

@endsection

