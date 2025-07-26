<?php

namespace App\Http\Controllers\Auth;

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
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'login' => 'required|string',
        'password' => 'required|string',
    ]);

    // kolom di DB tetap email, baik isi email atau username
    $login_type = 'email';
    $credentials = [
        $login_type => $request->login,
        'password' => $request->password,
    ];

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

       $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('petugas') || $user->hasRole('wali')) {
            $request->session()->put('show_welcome', true);
        }
        // Redirect ke dashboard sesuai role
         $user = Auth::user(); 
        if ($user->hasRole('admin')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('petugas')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('wali')) {
            return redirect()->intended('/dashboard');
        } else {
            return redirect('/dashboard');
        }
    }

    // Jika gagal login
    return back()->withErrors([
        'login' => 'Login gagal! Email/username atau password salah.',
    ])->onlyInput('login');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
