<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected array $allowedRoles = ['superadmin', 'admin', 'editor', 'moderator'];

    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting: max 5 attempts per minute per IP+email combo
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                ], 429);
            }
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $credentials = [
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Verify the user has an allowed role
            $role = strtolower(trim($user->role ?? ''));
            if (!in_array($role, $this->allowedRoles)) {
                Auth::logout();
                RateLimiter::hit($throttleKey);

                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Akun tidak memiliki akses admin.'], 403);
                }
                return back()->withErrors(['email' => 'Akun ini tidak memiliki akses admin.'])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Login berhasil.',
                    'redirect' => route('admin.dashboard'),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey);

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah.'], 401);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
