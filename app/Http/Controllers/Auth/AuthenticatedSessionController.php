<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // 1. Regenerate session HARUS dilakukan paling awal setelah auth berhasil
        $request->session()->regenerate();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('superadmin')) {
            ActivityLogger::login($user->id, $user->email);
        }

        // 2. Gunakan redirect()->route() agar user DIPAKSA ke dashboard role-nya
        // Jangan gunakan intended() jika Anda ingin strict redirection berdasarkan role
        if ($user) {
            if ($user->hasRole('superadmin')) {
                return redirect()->route('admin.dashboard');
            } 
            
            if ($user->hasRole('adminPosyandu')) {
                return redirect()->route('adminPosyandu.dashboard');
            } 
            
            if ($user->hasRole('orangtua')) {
                return redirect()->route('orangtua.dashboard');
            }
        }

        // Default jika tidak punya role atau role tidak dikenali
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->hasRole('superadmin')) {
            ActivityLogger::logout($user->id);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
