<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login Keuangan | GenBI Provinsi Jambi</title>
    <!-- Font Inter dari Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS Tailwind & Kustom -->
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/assets/css/styles.css?v=<?= time() ?>">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="page-ready bg-gradient-to-br from-slate-50 via-white to-blue-50 min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 text-slate-800">
    
    <!-- Ambient Background Shapes (Optional for aesthetic) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-96 h-96 bg-indigo-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Login Card -->
    <div class="relative bg-white/80 backdrop-blur-xl p-8 sm:p-10 rounded-[2rem] shadow-2xl shadow-slate-200/50 w-full max-w-md border border-white/60">
        <div class="text-center mb-8">
            <img src="/assets/images/logo-genbi.png" alt="GenBI Logo" class="h-16 sm:h-20 mx-auto mb-5 object-contain hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Selamat Datang</h1>
            <p class="text-slate-500 mt-2 text-sm">Sistem Keuangan GenBI Provinsi Jambi</p>
        </div>
        
        <form action="/keuangan/akun/login" method="POST" class="space-y-5">
            
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input type="email" id="email" name="email" required placeholder="contoh@genbijambi.com" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm sm:text-base">
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    <a href="#" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline transition-colors">Lupa Password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm sm:text-base">
                </div>
            </div>

            <div class="flex items-center pt-1">
                <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500/30 border-slate-300 rounded cursor-pointer transition-colors">
                <label for="remember" class="ml-2.5 block text-sm font-medium text-slate-600 cursor-pointer select-none">
                    Ingat sesi saya
                </label>
            </div>

            <button type="submit" class="w-full mt-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 sm:py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transform hover:-translate-y-0.5 active:translate-y-0 text-sm sm:text-base tracking-wide">
                Masuk ke Dasbor
            </button>
        </form>

        <div class="mt-8 relative flex items-center justify-center">
            <hr class="w-full border-slate-200">
            <span class="absolute bg-white px-4 text-xs font-medium text-slate-400 uppercase tracking-wider">Atau</span>
        </div>

        <p class="mt-8 text-center text-sm text-slate-600">
            Belum terdaftar? <a href="/keuangan/akun/register" class="text-blue-600 hover:text-indigo-600 font-semibold transition-colors hover:underline decoration-2 underline-offset-4">Buat akun bendahara</a>
        </p>
    </div>
</body>
</html>
