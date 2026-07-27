@extends('layouts.auth')

@section('content')
<div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 border border-neutral-100">
  <div class="text-center mb-8">
    <h1 class="text-2xl font-bold text-neutral-900">Admin GenBI</h1>
    <p class="text-neutral-500 text-sm mt-2">Masuk untuk mengelola konten website</p>
  </div>
  
  <form method="POST" action="{{ route('admin.login.submit') ?? '/admin/login' }}" class="space-y-5">
    @csrf
    @if ($errors->any())
      <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm border border-red-100">
        {{ $errors->first() }}
      </div>
    @endif
    
    <div>
      <label class="block text-sm font-medium text-neutral-700 mb-1" for="email">Email</label>
      <input type="email" name="email" id="email" class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="admin@genbijambi.com" required autofocus>
    </div>
    
    <div>
      <label class="block text-sm font-medium text-neutral-700 mb-1" for="password">Password</label>
      <input type="password" name="password" id="password" class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="••••••••" required>
    </div>
    
    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition-colors mt-2 shadow-md shadow-blue-500/20">
      Masuk ke Dashboard
    </button>
  </form>
</div>
@endsection
