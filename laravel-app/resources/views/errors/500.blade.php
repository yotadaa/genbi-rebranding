@extends('layouts.public')

@section('content')
<section class="py-24 text-center">
  <div class="site-container">
    <h1 class="text-6xl font-bold text-neutral-900">500</h1>
    <h2 class="text-2xl mt-4 font-semibold text-neutral-800">Terjadi Kesalahan Server</h2>
    <p class="mt-4 text-neutral-600 max-w-md mx-auto">Maaf, kami sedang mengalami masalah teknis. Silakan coba lagi nanti.</p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-8">Kembali ke Beranda</a>
  </div>
</section>
@endsection
